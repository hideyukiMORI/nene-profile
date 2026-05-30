# 命名規則

NeNe Profile のコード・API コントラクト・データベース・テスト・ドキュメントに対する権威ある命名規則。

> **絶対遵守 — non-negotiable.** これらのルールは MUST であり、推奨ではない。
> ここで違反した名前、または登録済み用語のタイポ・スペルバリエーションは不具合であり
> **マージをブロックする**。「ほぼ合っている」は存在しない。疑わしい場合はレジストリを確認すること。
>
> 全用語・識別子の正確なスペリングの唯一の正解は
> **[`../explanation/terminology.md`](../explanation/terminology.md)** にある。
> このドキュメントは *パターン* を定義し、レジストリは *正確な文字列* を定義する。
> 識別子の追加・リネームは必ず同 PR でレジストリを更新すること。

**用語レジストリ（正規スペリング）:** [`../explanation/terminology.md`](../explanation/terminology.md)
**用語集（用語の意味）:** [`../explanation/glossary.md`](../explanation/glossary.md)
**フレームワーク基準:** NENE2 [`domain-layer.md`](https://github.com/hideyukiMORI/NENE2/blob/main/docs/development/domain-layer.md)

---

## 1. PHP

### ファイルとネームスペース

| 項目 | ルール | 例 |
|---|---|---|
| ネームスペースルート | `NeneProfile\` | `NeneProfile\Preset\CreateMappingPresetHandler` |
| ドメインフォルダ | PascalCase 単数ドメイン名 | `src/Preset/`, `src/ImportJob/` |
| ファイル名 | 主クラスと一致 | `CreateMappingPresetHandler.php` |
| 1 ファイル 1 公開クラス | 必須 | — |

### クラスとインターフェース

| 役割 | パターン | 例 |
|---|---|---|
| HTTP ハンドラ | `{Verb}{Noun}Handler` | `CreateMappingPresetHandler`, `ListImportJobsHandler` |
| UseCase インターフェース | `{Verb}{Noun}UseCaseInterface` | `CreateMappingPresetUseCaseInterface` |
| UseCase 実装 | `{Verb}{Noun}UseCase` | `CreateMappingPresetUseCase` |
| UseCase メソッド | 常に `execute` | `execute(CreateMappingPresetInput $input): CreateMappingPresetOutput` |
| Input DTO | `{Verb}{Noun}Input` | `CreateMappingPresetInput` |
| Output DTO | `{Verb}{Noun}Output` | `CreateMappingPresetOutput` |
| ドメインエンティティ | 単数名詞、サフィックスなし | `MappingPreset`, `ImportJob`, `Organization` |
| リポジトリインターフェース | `{Entity}RepositoryInterface` | `MappingPresetRepositoryInterface` |
| PDO リポジトリ | `Pdo{Entity}Repository` | `PdoMappingPresetRepository` |
| ドメイン例外 | `{Entity}{Reason}Exception` | `MappingPresetNotFoundException`, `ImportJobAlreadyRunningException` |
| サービスプロバイダ | `{Purpose}ServiceProvider` | `PresetServiceProvider`, `RuntimeServiceProvider` |
| トランスフォーマー | `{Name}Transformer` (Transformer/) | `DateYmdSlashTransformer`, `DebitCreditTransformer` |

全アプリクラス: `final` かつ適用可能な場合は `readonly`。全 PHP ファイル: `declare(strict_types=1);`。

### モジュール (`src/`)

使用できるトップレベルフォルダ: ドメイングループのみ。
`Handlers/`, `Repositories/`, `UseCases/` のようなレイヤーフォルダは禁止。

Phase 1 ドメイン: `Organization/`, `Auth/`, `User/`, `OrgSettings/`, `Preset/`,
`ImportJob/`, `Transformer/`, `Export/`, `Audit/`, `Http/`.

### メソッドとプロパティ

| 項目 | ルール | 例 |
|---|---|---|
| メソッド | camelCase | `findById`, `markAsCompleted`, `computeLineHash` |
| プロパティ | camelCase | `$presetVersionId`, `$importJobRepository` |
| 定数 | UPPER_SNAKE_CASE | `MAX_FILE_SIZE_BYTES`, `SCHEMA_VERSION` |

リポジトリメソッドは **ドメイン動詞** を使用: `findById`, `save`, `delete` — `selectById`, `insertRow` は禁止。

---

## 2. HTTP ルートと OpenAPI

### URL パス

| 項目 | ルール | 例 |
|---|---|---|
| パスセグメント | lowercase **kebab-case** | `/admin/mapping-presets`, `/admin/import-jobs` |
| コレクションパス | 複数形名詞 | `/admin/organizations`, `/admin/users` |
| 単一リソース | `{id}` パスパラメータ | `/admin/mapping-presets/{id}` |
| エクスポート | リソース + アクション | `/admin/import-jobs/{id}/export.json` |
| パスパラメータ名 | lowercase camelCase | `id`, `presetId` |

Admin 変更操作ルートは `/admin/…` 以下。

### operationId

| 項目 | ルール | 例 |
|---|---|---|
| ケース | camelCase | `getHealth`, `createMappingPreset` |
| 形式 | `{verb}{Resource}` or `{verb}{Resource}ById` | `listImportJobs`, `getMappingPresetById` |
| 安定性 | リリース後はリネーム禁止; deprecated 代替のみ | — |

`docs/openapi/openapi.yaml`、ルート登録、`docs/mcp/tools.json` の `operationId` は一致が必須。

### OpenAPI スキーマ名

| 項目 | ルール | 例 |
|---|---|---|
| レスポンススキーマ | `{Resource}Response` | `MappingPresetResponse`, `ImportJobResponse` |
| リストレスポンス | `{Resource}ListResponse` | `ImportJobListResponse` |
| 作成リクエスト | `Create{Resource}Request` | `CreateMappingPresetRequest` |
| タグ名 | PascalCase 単数グループ | `System`, `Admin`, `Preset`, `ImportJob` |

公開 OpenAPI のサマリー・説明・例: **English のみ**。

---

## 3. JSON（リクエスト・レスポンスボディ）

| 項目 | ルール | 例 |
|---|---|---|
| プロパティ名 | **snake_case** | `preset_version_id`, `original_file_hash`, `row_count` |
| 金額 | integer **cents** | `amount_cents`, `balance_cents` |
| ブール値 | `is_` / `has_` プレフィックス | `is_deleted`, `is_completed` |
| タイムスタンプ | `_at` サフィックス、ISO 8601 文字列 | `started_at`, `completed_at`, `created_at` |
| 外部キー | `{entity}_id` | `preset_version_id`, `import_job_id` |
| リストエンベロープ | `items`, `limit`, `offset` | NENE2 list パターンと同一 |

公開 JSON で camelCase を混在させない。金額にフロートを使わない。

---

## 4. Problem Details とバリデーションエラー

| 項目 | ルール | 例 |
|---|---|---|
| Base URL | `https://nene-profile.dev/problems/` | — |
| Type スラグ | kebab-case | `validation-failed`, `mapping-preset-not-found`, `import-job-already-running` |
| バリデーション `errors[].field` | snake_case パス | `body.preset_version_id`, `body.columns.transaction_date` |
| バリデーション `errors[].code` | snake_case | `required`, `invalid_encoding`, `unsupported_transformer` |

Problem Details の `title` と `detail`: English。

---

## 5. データベース

| 項目 | ルール | 例 |
|---|---|---|
| テーブル名 | snake_case、**複数形** | `mapping_presets`, `import_jobs`, `import_job_errors` |
| カラム名 | snake_case | `preset_version_id`, `original_file_hash`, `amount_cents` |
| 金額カラム | `*_cents` サフィックス、integer | `amount_cents`, `balance_cents` |
| 主キー | `id` | BIGINT auto-increment |
| 外部キーカラム | `{singular_entity}_id` | `import_job_id`, `preset_version_id` |
| インデックス名 | `idx_{table}_{columns}` | `idx_import_jobs_organization_id` |
| ユニーク制約 | `uniq_{table}_{columns}` | `uniq_mapping_presets_org_name` |

SQL は `Pdo*Repository` クラス内にのみ記述。

### マイグレーション

| 項目 | ルール | 例 |
|---|---|---|
| ファイル名 | `YYYYMMDDHHMMSS_snake_description.php` | `20260601120000_create_mapping_presets_table.php` |
| スナップショット | `database/schema/{table}.sql` | `database/schema/mapping_presets.sql` |

---

## 6. 環境変数

| 項目 | ルール | 例 |
|---|---|---|
| 名前 | UPPER_SNAKE_CASE | `DB_HOST`, `NENE_PROFILE_PORT` |
| プレフィックス | プロダクト固有 | `NENE_PROFILE_` |
| シークレット | コミット禁止; `.env.example` にのみ記載 | — |

---

## 7. テスト

| 項目 | ルール | 例 |
|---|---|---|
| テストクラス | `{ClassUnderTest}Test` | `CreateMappingPresetUseCaseTest`, `DebitCreditTransformerTest` |
| テストメソッド | `test_{behavior}_when_{condition}` | `test_flags_error_when_both_debit_and_credit_nonzero` |
| テストネームスペース | `src/` を `tests/` 以下で鏡写し | `tests/Preset/CreateMappingPresetUseCaseTest.php` |

---

## 8. MCP ツール

| 項目 | ルール | 例 |
|---|---|---|
| ツール `name` | OpenAPI `operationId` と同一 | `listMappingPresets`, `runProfileImport` |
| ツール `title` | 短い English Title Case | `List Mapping Presets`, `Run Profile Import` |
| `safety` | `read` または `write` | 認証レビュー通過前は `read` を優先 |

カタログ: `docs/mcp/tools.json`。`composer mcp` で検証。

---

## 9. フロントエンド (Phase 2+)

| 項目 | ルール |
|---|---|
| コンポーネント | PascalCase ファイル名・エクスポート |
| フック | camelCase + `use` プレフィックス |
| API クライアント | snake_case JSON をマップ; 転送中にフィールドをリネームしない |
| Admin SPA | React + TypeScript strict mode |

全フロントエンド標準: **`docs/development/frontend-standards.md`** (Phase 2)。

---

## 10. ドキュメントとコミット

| サーフェス | 言語 | 命名 |
|---|---|---|
| 公開ドキュメント、OpenAPI、API エラー | English 推奨（日本語も可 — ADR 0011） | 用語集の正規用語を使用 |
| Issues、PR、コミット本文 | 日本語・English 両方可 (ADR 0011) | 初出は用語集の English 用語を使う |
| コミット subject | Conventional Commits + `(#issue)` | [`commit-conventions.md`](./commit-conventions.md) 参照 |
| ADR ファイル名 | `NNNN-kebab-title.md` | `0003-transform-fidelity.md` |

識別子の追加・リネームは [`../explanation/terminology.md`](../explanation/terminology.md) を同 PR で更新すること。
プロダクト概念ならば [`../explanation/glossary.md`](../explanation/glossary.md) も更新する。

---

## 11. 禁止パターン

- **`terminology.md` に登録された用語のタイポ・スペルバリエーション** — マージブロック
- **未登録識別子** — 同 PR でレジストリに追加しないまま使用することは禁止
- レイヤーファースト フォルダ (`src/Handlers/`, `src/Repositories/`)
- `Pdo*Repository` 外の SQL
- 公開 JSON プロパティ名の camelCase
- DB/JSON/テストでの金額フロート・DECIMAL
- リリース済み `operationId` のリネーム
- 兄弟製品 (nene-clear 等) へのドメインロジック埋め込み
- サイレントな行ドロップ（全エラーは `import_job_errors` に記録）

---

## 検証

```bash
composer check
composer openapi
composer mcp
```

レビューチェックリスト: [`../review/`](../review/).
