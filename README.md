# NeNe Profile

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](./LICENSE)
[![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)](https://www.php.net/)
[![Backend CI](https://github.com/hideyukiMORI/nene-profile/actions/workflows/backend-ci.yml/badge.svg)](https://github.com/hideyukiMORI/nene-profile/actions/workflows/backend-ci.yml)
[![Frontend CI](https://github.com/hideyukiMORI/nene-profile/actions/workflows/frontend-ci.yml/badge.svg)](https://github.com/hideyukiMORI/nene-profile/actions/workflows/frontend-ci.yml)

**Bank CSV normalization — self-hosted for Japan SMB.**

**NeNe Profile** maps **any Japanese bank CSV** to a **standard transaction format**
via column presets and operator-defined mappings — without reconciliation,
matching, or dunning. Built on [NENE2](https://github.com/hideyukiMORI/NENE2).

> **Separate product.** Profile does **not** match deposits to invoices
> ([`nene-clear`](https://github.com/hideyukiMORI/nene-clear)),
> store received PDFs ([`nene-vault`](https://github.com/hideyukiMORI/nene-vault)),
> or issue invoices ([`nene-invoice`](https://github.com/hideyukiMORI/nene-invoice)).
> See [ADR 0009](./docs/adr/0009-separate-from-nene-clear.md).

## Domain (binding)

| Product | Repository | What it does |
| --- | --- | --- |
| **NeNe Profile** | `nene-profile` (this) | Bank CSV column mapping & normalization |
| **NeNe Clear** | `nene-clear` | Reconciliation & dunning (consumes Profile output) |
| **NeNe Vault** | `nene-vault` | Received-document archive |
| **NeNe Invoice** | `nene-invoice` | Billing documents |

## Goals

- **User-defined column mapping** — not hardcoded per bank forever
- **Presets** — MUFG, SMBC, PayPay bank, etc. as community/official templates
- **Transformers** — date formats, debit/credit columns, amount sign rules
- **Standard output schema** — JSON/CSV for Clear, Excel, or custom tools
- **Self-hosted OSS** — MIT; Tier A or Docker
- **No per-import SaaS fee** — unlimited banks via mapping UI

## Non-goals

- Not matching bank lines to invoices — [`nene-clear`](https://github.com/hideyukiMORI/nene-clear)
- Not writing payments back to the Invoice API — [`nene-clear`](https://github.com/hideyukiMORI/nene-clear)
- Not sending dunning notices — [`nene-clear`](https://github.com/hideyukiMORI/nene-clear)
- Not storing received PDFs / 電帳法 document archive — [`nene-vault`](https://github.com/hideyukiMORI/nene-vault)
- Not issuing quotes or invoices — [`nene-invoice`](https://github.com/hideyukiMORI/nene-invoice)
- Not auto-importing from bank API (CSV upload first; MVP scope)

Full list: [`docs/explanation/scope-contract.md`](./docs/explanation/scope-contract.md)

## Documentation (read first)

| Topic | Document |
| --- | --- |
| **Scope contract (GOAL / DO / DON'T)** | [`docs/explanation/scope-contract.md`](./docs/explanation/scope-contract.md) |
| **Compliance & integrity rules (binding)** | [`docs/explanation/accounting-compliance.md`](./docs/explanation/accounting-compliance.md) |
| **CSV normalization spec (binding)** | [`docs/explanation/csv-normalization-spec.md`](./docs/explanation/csv-normalization-spec.md) |
| **Standard output schema** | [`docs/explanation/output-schema.md`](./docs/explanation/output-schema.md) |
| **Domain model** | [`docs/explanation/domain-model.md`](./docs/explanation/domain-model.md) |
| **Glossary** | [`docs/explanation/glossary.md`](./docs/explanation/glossary.md) |
| **Domain boundary** | [`docs/explanation/scope-boundary.md`](./docs/explanation/scope-boundary.md) |
| **Clear downstream contract** | [`docs/integrations/clear-downstream-contract.md`](./docs/integrations/clear-downstream-contract.md) |
| **Agents** | [`AGENTS.md`](./AGENTS.md) |
| **Language policy (JP/EN)** | [`docs/adr/0011-bilingual-jp-en-documentation.md`](./docs/adr/0011-bilingual-jp-en-documentation.md) |

## Getting started (Docker)

The Compose stack ships sensible MySQL defaults; copy the example env to customize.

```bash
cp .env.example .env            # uncomment the Docker / MySQL block for local Docker
docker compose up -d --build    # MySQL → migrate → seed → Apache

# Admin SPA:  http://localhost:8490/admin/
# API health: http://localhost:8490/health
# phpMyAdmin: http://localhost:8491
```

Build the admin SPA into `public_html/admin/` (same-origin under `/admin/`):

```bash
npm --prefix frontend ci
npm --prefix frontend run build
```

Default seed superadmin: `admin@nene-profile.local` / `changeme`
(override via `SUPERADMIN_EMAIL` / `SUPERADMIN_PASSWORD`).

## Local ports

NeNe Profile owns the **`84**` port lane**; sibling products use their own lanes so several apps can run locally side by side. Override via `NENE_PROFILE_*` in `.env`.

| Service | Port |
| --- | --- |
| Admin SPA + API (Docker app) | 8490 |
| MySQL (Docker) | 3409 |
| phpMyAdmin (Docker) | 8491 |

## Status

| Phase | Scope | State |
| --- | --- | --- |
| **Phase 0** | Governance, ADRs, binding CSV/output contracts | ✅ Complete |
| **Phase 1** | Normalization API — preset CRUD, import jobs, transformers, export | ✅ Complete |
| **Phase 2** | Admin SPA — organizations, users, presets, import jobs, settings, audit logs, dashboard | ✅ Mostly complete (visual column mapper pending) |
| **Phase 3** | Official preset library + Clear handoff | Planned |

See [`docs/roadmap.md`](./docs/roadmap.md) and [`docs/todo/current.md`](./docs/todo/current.md).

Key shipped features:

- Multi-tenant auth (JWT + capability-based RBAC)
- 9 transformers — date formats incl. Japanese era `date_era`, debit/credit sign, yen→cents, regex extract ([ADR 0003](./docs/adr/0003-transform-fidelity.md))
- Immutable original-file storage with SHA-256 provenance ([ADR 0004](./docs/adr/0004-original-file-immutability.md))
- Full before/after audit trail, RFC 9457 problem responses, OpenAPI 3.1 contract
- React 19 admin SPA
- CI quality gates (PHPStan L8 · CS-Fixer · PHPUnit · Vitest · Playwright E2E)

## Pipeline position

```
[Bank CSV] → NeNe Profile (map + normalize) → StandardTransaction JSON/CSV
                                                      ↓
                                            NeNe Clear (match + dunning)
```

## License

MIT — see [LICENSE](./LICENSE).
