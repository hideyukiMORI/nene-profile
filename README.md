# NeNe Profile

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](./LICENSE)
[![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)](https://www.php.net/)
[![Private](https://img.shields.io/badge/status-private-red)]()

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

## Documentation (read first)

| Topic | Document |
| --- | --- |
| **Scope contract (GOAL / DO / DON'T)** | [`docs/explanation/scope-contract.md`](./docs/explanation/scope-contract.md) |
| **CSV normalization spec (binding)** | [`docs/explanation/csv-normalization-spec.md`](./docs/explanation/csv-normalization-spec.md) |
| **Standard output schema** | [`docs/explanation/output-schema.md`](./docs/explanation/output-schema.md) |
| **Domain boundary** | [`docs/explanation/scope-boundary.md`](./docs/explanation/scope-boundary.md) |
| **Clear downstream contract** | [`docs/integrations/clear-downstream-contract.md`](./docs/integrations/clear-downstream-contract.md) |
| **Agents** | [`AGENTS.md`](./AGENTS.md) |

## Status

**Phase 0** — governance and product design. Runtime scaffold follows Issues #4+.

## Pipeline position

```
[Bank CSV] → NeNe Profile (map + normalize) → StandardTransaction JSON/CSV
                                                      ↓
                                            NeNe Clear (match + dunning)
```

## License

MIT — see [LICENSE](./LICENSE).
