# NENE2 継承マップ

NeNe Profile が NENE2 から何を継承し、何をローカルで上書きしているかを記録する。

NENE2 framework: [`hideyukiMORI/NENE2`](https://github.com/hideyukiMORI/NENE2)

---

## 継承ポリシー

- NENE2 の `docs/development/` はすべてのルールのデフォルト基準
- ローカルドキュメントまたは ADR で明示した箇所のみ逸脱する
- フレームワーク HTTP・ミドルウェア・DI の動作は `vendor/hideyukimori/nene2/docs/` が正本

---

## 継承マップ

| 項目 | NENE2 ベース | Profile ローカル上書き |
|---|---|---|
| PHP バージョン | `>=8.4.1 <9.0` | 同じ |
| PSR-7 / 15 / 17 | 使用 | 同じ |
| DI コンテナ | PSR-11 (`ContainerBuilder`) | 同じ |
| ロギング | PSR-3 (Monolog) | 同じ |
| HTTP ランタイム | Slim + NENE2 `RuntimeApplicationFactory` | 同じ |
| エラーレスポンス | RFC 9457 Problem Details | base URL: `https://nene-profile.dev/problems/` |
| リクエストバリデーション | 階層バリデーション + readonly DTO | 同じ |
| マイグレーション | Phinx (`database/migrations/`) | 同じ |
| テスト | PHPUnit; UseCase in-memory; Repository SQLite | 同じ + Transformer テスト追加 |
| 静的解析 | PHPStan level 8 | 同じ |
| フォーマット | PHP-CS-Fixer | 同じ |
| コミット規約 | Conventional Commits | `type`/`scope` は English; description は JP or EN (ADR 0011) |
| ドキュメント言語 | English (NENE2 ベース) | JP or EN 両方可 (ADR 0011) |
| ワークフロー | Issue 駆動 + PR | 同じ |
| ADR | `docs/adr/NNNN-*.md` | 同じ |
| モジュールレイアウト | ドメイングループ | Profile ドメイン定義 (`backend-standards.md` §2) |
| フロントエンド | React + TypeScript + Vite | Profile 固有ルール (`frontend-standards.md`) |
| 認証 | JWT Bearer (`BearerTokenMiddleware`) | 同じ |
| マルチテナント | `organization_id` + `OrgResolverMiddleware` パターン | ADR 0006 (NeNe Records 実装を参照) |
| MCP | `LocalMcpServer` + `LocalMcpToolCatalog` | `runProfileImport`, `listMappingPresets` (Phase 3+) |

---

## NENE2 で定義・Profile は使用するのみ

- `DatabaseQueryExecutorInterface` / `PdoDatabaseQueryExecutor`
- `DatabaseTransactionManagerInterface` / `PdoDatabaseTransactionManager`
- `JsonResponseFactory`
- `PaginationQuery` / `PaginationResponse`
- `JsonRequestBodyParser`
- `ErrorHandlerMiddleware` / `ProblemDetailsResponseFactory`
- `BearerTokenMiddleware` / `CompositeAuthMiddleware`
- `RequestScopedHolder`
- `HealthCheckInterface` / `HealthStatus`

---

## Profile 固有（NENE2 には存在しない）

- `Transformer/` — トランスフォーマーパイプライン (declarative, Phase 1 built-in)
- `ImportJob/` — CSV アップロード・行処理・エラーロギング・ジョブライフサイクル
- `Preset/` — mapping_preset + mapping_preset_version CRUD
- `Export/` — StandardTransaction JSON/CSV 出力
- `Audit/AuditRecorder` — UseCase レベルの mutation 記録
- ファイルストレージ (不変 CSV 原本) — `storage/uploads/` (Phase 1 ローカルFS)
- コンプライアンス binding ルール (`accounting-compliance.md`)

---

## 参照実装

- NeNe Records: `src/Organization/`, `src/Auth/` (Role, Capability, CapabilityResolver, CapabilityMiddleware), `src/Organization/Resolution/`
- NeNe Invoice: `docs/development/` 全文 (governance model の参照)

Last updated: 2026-05-30
