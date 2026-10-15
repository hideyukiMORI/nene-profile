# Backend Standards

NeNe Profile バックエンド (PHP API) の開発方針。
[NENE2](https://github.com/hideyukiMORI/NENE2/blob/main/docs/development/) をベースとし、
ローカル ADR で明示した箇所のみ逸脱する。

**命名と用語:** [`naming-conventions.md`](./naming-conventions.md),
[`../explanation/glossary.md`](../explanation/glossary.md).

---

## 1. プロジェクト構成

NeNe Profile は **NENE2 コンシューマプロジェクト**:

```
vendor/hideyukimori/nene2/   ← フレームワーク (編集不可)
src/                         ← プロダクトコード (NeneProfile\)
tests/                       ← src/ を鏡写し
docs/openapi/openapi.yaml    ← 公開コントラクト
public_html/index.php        ← フロントコントローラ
database/migrations/         ← Phinx マイグレーション
database/schema/             ← スキーマスナップショット
```

Namespace: `NeneProfile\`

---

## 2. モジュールレイアウト（ドメイングループ）

**技術レイヤーではなくドメイン** で分類する:

```
src/
  ApplicationServiceProvider.php
  Http/              # ルーティング、ブートストラップ
  Organization/      # テナント + per-request 解決 (Organization/Resolution/)
  Auth/              # JWT 認証、Role/Capability、CapabilityMiddleware
  User/              # オペレーターアカウント (組織内)
  OrgSettings/       # 組織設定 CRUD (organization_settings)
  Preset/            # mapping_preset + mapping_preset_version CRUD
  ImportJob/         # インポートジョブライフサイクル、行処理、エラーロギング
  Transformer/       # 組み込みトランスフォーマー実装 (Phase 1)
  Export/            # StandardTransaction JSON/CSV 出力
  Audit/             # audit_log 記録 (AuditRecorder)
  Upstream/          # オプション HTTP クライアント (Clear bearer token 等 Phase 3+)
```

**配置ゼロ許容:** Handler は必ずドメインフォルダに置く (`Preset/CreateMappingPresetHandler.php`)。
`src/Handlers/`、`src/Repositories/`、`src/UseCases/` のようなレイヤーフォルダは禁止。

---

## 3. レイヤーリング規則

```
Handler → UseCase → RepositoryInterface → PdoRepository
```

| レイヤー | してよいこと | してはいけないこと |
|---|---|---|
| **Handler** | HTTP パース、DTO 構築、UseCase 呼び出し、JSON レスポンス整形 | SQL、ビジネスルール、ファイル I/O 直接アクセス |
| **UseCase** | ビジネスルール、トランスフォーム検証、オーケストレーション | `$_SERVER`、PDO、raw HTTP |
| **Repository** | SQL / 永続化 | HTTP、トランスフォームロジック、ファイル処理 |
| **Transformer** | 宣言的な列変換 (Phase 1) | DB アクセス、HTTP |
| **Export** | StandardTransaction の JSON/CSV 整形 | ビジネスルール、SQL |

すべての PHP ファイル: `declare(strict_types=1);`。
アプリケーションクラス: `final` かつ適用可能な場合は `readonly`。

---

## 4. UseCase 規則

```php
// インターフェース
interface CreateMappingPresetUseCaseInterface
{
    public function execute(CreateMappingPresetInput $input): CreateMappingPresetOutput;
}
```

- UseCase インターフェースのメソッドは常に `execute` のみ
- Input/Output は `final readonly` DTO — raw 配列・PSR-7 オブジェクトは渡さない
- ビジネス不変条件 (uniqueness、状態チェック等) は UseCase 内で検証
- フォーマット検証（必須項目、型）は Handler 側で行い、UseCase に渡す前に完了させる
- UseCase は HTTP・セッション・テンプレート・キューを知らない
- UseCase は PSR-11 コンテナを直接呼び出さない

---

## 5. Repository 規則

```php
interface MappingPresetRepositoryInterface
{
    public function findById(string $id): ?MappingPreset;
    public function save(MappingPreset $preset): void;
    public function existsByName(string $orgId, string $name): bool;
}
```

- メソッド名はドメイン動詞: `findById`, `save`, `delete` — SQL 動詞 (`selectById`, `insertRow`) は禁止
- 戻り値型はドメインオブジェクトまたはプリミティブ — PDO 結果行・raw 配列は返さない
- SQL は `Pdo*Repository` クラスの内部にのみ記述する
- `DatabaseQueryExecutorInterface` (NENE2) を使用; raw PDO は直接使わない
- テナントスコープ: 全クエリで `organization_id` によるフィルタリング必須 (ADR 0006)

---

## 6. HTTP & OpenAPI

- 全公開ルートは `docs/openapi/openapi.yaml` に `operationId` 付きで記述する
- 成功レスポンスと Problem Details エラーシェイプを両方ドキュメント化する
- エラー: RFC 9457 Problem Details; base URL `https://nene-profile.dev/problems/`
- Admin ルートは JWT Bearer auth 必須 (Phase 1+)
- `GET /health` は認証不要

---

## 7. ファイルアップロード (CSV)

Profile 固有の考慮事項:

- 受け取ったファイルは **処理開始前** に永続化し SHA-256 ハッシュを記録 (ADR 0004)
- アップロードされたファイルは不変 — 処理後も削除・上書きしない
- ファイルサイズ上限: デフォルト 10 MB (組織設定で変更可)
- エンコーディング検出: UTF-8 / Shift_JIS (ADR 0003 §4)
- ファイルは `public_html/` には置かない; オペレーターの設定パス配下に保存

---

## 8. トランスフォーマー (Phase 1)

- 宣言的 (declarative) — プリセット JSON で指定; コード実行は許可しない
- 組み込みトランスフォーマー一覧: `docs/explanation/glossary.md` 参照
- カスタムトランスフォーマーは ADR + プラグインインターフェース確立後 (Phase 3+)
- トランスフォーマーは UseCase 内から呼び出す; Handler 内でトランスフォームしない

---

## 9. トランスフォーム整合性 & コンプライアンス

> **コンプライアンスは non-negotiable (binding).** トランスフォーマー、インポートジョブ、
> プロベナンスフィールド、プリセットバージョン、エクスポート整形に触れる変更は
> [`../explanation/accounting-compliance.md`](../explanation/accounting-compliance.md) と
> [`../review/compliance.md`](../review/compliance.md) のレビューが必須。
> コンプライアンスが利便性と衝突する場合、**コンプライアンスが勝つ**。

- 金額: **integer cents** (`amount_cents`). フロート・DECIMAL は DB・JSON・テスト全面禁止
- 金額符号: 入金/inflow → positive; 出金/outflow → negative (ADR 0003 §1)

> `cents` は**その通貨の最小単位**であって、表示額の 1/100 ではない。
> **JPY は小数点以下 0 桁（ISO 4217）なので、`*_cents` には円をそのまま格納する——×100 しない。**
> 例: ¥1,500 は `1500` として格納する。`116480` は ¥116,480 であって ¥1,164.80 ではない。
- 日付出力: ISO 8601 `YYYY-MM-DD` のみ (ADR 0003 §2–3)
- 行エラー: `import_job_errors` に必ずロギング; サイレントドロップ禁止
- プロベナンスフィールド 5 点: 全出力行に必須 (ADR 0004)
- プリセットバージョン: 完了ジョブから参照されているバージョンは不変 (ADR 0004)

---

## 10. データベース

- Phinx マイグレーション: `database/migrations/`
- スキーマスナップショット: `database/schema/`
- ソフトデリート: `is_deleted`, `deleted_at` (ADR で別途指定がない限り)
- テナントスコープ: `organization_id` を全テナントスコープテーブルに付与 (ADR 0006)
- 不変テーブル: `mapping_preset_versions`, `import_jobs` (完了後), `import_job_errors`, `normalized_transactions` — UPDATE を発行しない

---

## 11. テスト

- **UseCase テスト**: DB 不使用 — リポジトリフェイク (InMemory 実装) を注入
- **Repository テスト**: SQLite in-memory PDO
- **HTTP テスト**: OpenAPI シェイプに対するコントラクトテスト
- **Transformer テスト**: 純粋関数; DB 不使用; ADR 0003 の全ケースを網羅
- `composer check` = PHPStan + PHP-CS-Fixer + PHPUnit 全通過がマージ要件

---

## 12. 検証

```bash
composer check      # PHPStan + PHP-CS-Fixer + PHPUnit
composer openapi    # OpenAPI 整合性
composer mcp        # MCP カタログ
```

自己レビューチェックリスト:
[`../review/backend-api.md`](../review/backend-api.md),
[`../review/database.md`](../review/database.md),
[`../review/compliance.md`](../review/compliance.md).
