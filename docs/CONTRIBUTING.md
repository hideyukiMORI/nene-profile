# Contributing

NeNe Profile is built through small, Issue-driven changes. This document is the shared entry point for humans and AI agents.

## Required Reading

| Topic | Document |
| --- | --- |
| NENE2 継承マップ | [`docs/inheritance-from-nene2.md`](inheritance-from-nene2.md) |
| コーディング標準（インデックス）| [`docs/development/coding-standards.md`](development/coding-standards.md) |
| バックエンド標準 (PHP/API) | [`docs/development/backend-standards.md`](development/backend-standards.md) |
| 命名規則 | [`docs/development/naming-conventions.md`](development/naming-conventions.md) |
| 正規識別子レジストリ | [`docs/explanation/terminology.md`](explanation/terminology.md) |
| 用語集 | [`docs/explanation/glossary.md`](explanation/glossary.md) |
| コンプライアンス規則 (binding) | [`docs/explanation/accounting-compliance.md`](explanation/accounting-compliance.md) |
| コミットメッセージ規約 | [`docs/development/commit-conventions.md`](development/commit-conventions.md) |
| ワークフロー | [`docs/workflow.md`](workflow.md) |
| AI エージェント入口 | [`AGENTS.md`](../AGENTS.md) |
| ロードマップ | [`docs/roadmap.md`](roadmap.md) |
| 現在のタスク（運用ログ） | private `nene-origin/internal-docs/profile/todo/current.md`（移設済み） |

## Collaboration Policy

Follow [`docs/workflow.md`](workflow.md) — inherited from [NENE2](https://github.com/hideyukiMORI/NENE2/blob/main/docs/workflow.md):

1. Create or reuse a GitHub Issue **before** editing.
2. Branch from `main` as `type/issue-number-summary`.
3. Implement, verify (`composer check` when applicable), commit with `(#issue)`.
4. Push, open PR with `Closes #number`, merge after checks — **do not push directly to `main`**.

- Use one branch and one PR per focused work unit.
- Keep `docs/milestones/` and `docs/roadmap.md` updated when direction changes（運用ログ `current.md` は private `nene-origin/internal-docs/profile/` 側で更新）.
- Explain intent, impact, verification, and remaining risk in PRs.
- Prefer documentation that helps the next developer or AI agent decide what to do without rereading chat history.

## Secrets

Do not commit passwords, tokens, private URLs, production credentials, or local `.env` files. Commit only non-secret examples such as `.env.example` when needed.

Sensitive keys for this product include:

- Admin JWT secrets
- Clear bearer token for downstream handoff (Phase 3, optional)
- Webhook secrets (Phase 3, optional)

## Engineering Theme

NeNe Profile should stay readable, secure, and self-hostable:

- strict, typed, explicit boundaries (inherited from NENE2)
- decoupled use cases and infrastructure
- OpenAPI contracts before client assumptions
- CSV normalization only — no reconciliation, dunning, document storage, or invoice logic (ADR 0009)
- MCP access only through documented HTTP boundaries
- **never** merge into or embed inside sibling NeNe products (ADR 0002)

## Upstream Framework

Runtime HTTP, middleware, and DI patterns come from [NENE2](https://github.com/hideyukiMORI/NENE2). When framework behavior is unclear, read NENE2 docs under `vendor/hideyukimori/nene2/docs/` after `composer install`.
