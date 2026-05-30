# Coding Standards

NeNe Profile coding standards split by surface. **Full policies live in the
dedicated documents below** — this file is the index.

| Surface | Source of truth |
|---|---|
| **PHP / API / database** | [`backend-standards.md`](./backend-standards.md) |
| **命名規則（コード・API・DB・テスト）** | [`naming-conventions.md`](./naming-conventions.md) |
| **正規識別子スペリング** | [`../explanation/terminology.md`](../explanation/terminology.md) |
| **用語の意味（用語集）** | [`../explanation/glossary.md`](../explanation/glossary.md) |
| **React / TypeScript admin** | [`frontend-standards.md`](./frontend-standards.md) (Phase 2+) |
| **Conventional Commits** | [`commit-conventions.md`](./commit-conventions.md) |
| **ADR ポリシー** | [`adr.md`](./adr.md) |
| **NENE2 継承マップ** | [`../inheritance-from-nene2.md`](../inheritance-from-nene2.md) |

**フレームワーク基準:** [NENE2 coding standards](https://github.com/hideyukiMORI/NENE2/blob/main/docs/development/coding-standards.md) — NeNe Profile はローカルのドキュメントまたは ADR が明示した箇所でのみ逸脱する。

---

## 全サーフェス共通ルール

- **命名規則は絶対 (non-negotiable).** 違反・タイポはマージブロック — [`naming-conventions.md`](./naming-conventions.md) 参照。
- **識別子の唯一の正解は [`../explanation/terminology.md`](../explanation/terminology.md).** 追加・リネームは同 PR でレジストリを更新すること。
- **識別子のタイポ・スペルバリエーションはゼロ許容.** 登録済み用語の別スペルは不具合として扱う。
- GitHub Issue 駆動; フォーカスされた PR; `main` への直接コミット禁止
- **Strict typing** — PHP: `final readonly` DTO; TypeScript: strict mode (フロントエンド)
- **OpenAPI** が公開 API コントラクト; MCP は同一 HTTP 操作にマップ
- Problem Details `type`: `https://nene-profile.dev/problems/{problem-name}`
- **金額: integer cents** — DB・JSON でフロートは禁止
- **配置違反はマージブロック** — backend-standards 参照
- **transform fidelity とプロベナンス変更は必ずコンプライアンスレビューを通す** — [`../review/compliance.md`](../review/compliance.md)

---

## バックエンド（要約）

全文: **`docs/development/backend-standards.md`**。命名: **`docs/development/naming-conventions.md`**。

- NENE2 コンシューマ — フレームワークは `vendor/`、プロダクトコードは `src/`
- **ドメイングループ** モジュール — レイヤーフォルダは禁止
- Handler → UseCase → RepositoryInterface → PdoRepository
- PDO/SQL は `Pdo*Repository` 外に置かない; Handler にビジネスロジックを置かない
- Phinx マイグレーション + `database/schema/` スナップショット
- PHPUnit: in-memory UseCase テスト、SQLite リポジトリテスト、OpenAPI コントラクトテスト
- `composer check` マージ前必須

---

## 検証コマンド

```bash
composer check          # PHPStan + PHP-CS-Fixer + PHPUnit
composer openapi        # OpenAPI 整合性検証
composer mcp            # MCP カタログ検証
```

Phase 2 以降（`frontend/` 追加後）:

```bash
npm run check --prefix frontend
```
