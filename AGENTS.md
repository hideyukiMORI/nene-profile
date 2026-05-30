# Agent / AI Guide

Entry point for AI agents working on **NeNe Profile** (public repo `nene-profile`).

## Domain (read first)

| Product | Repository | Domain |
| --- | --- | --- |
| **NeNe Profile** | `nene-profile` (this) | Bank CSV normalization |
| **NeNe Clear** | `nene-clear` | Reconciliation & dunning |
| **NeNe Vault** | `nene-vault` | Received-document archive |

See [ADR 0009](docs/adr/0009-separate-from-nene-clear.md).

## Read First

- **Scope contract (binding):** `docs/explanation/scope-contract.md`
- **Compliance rules (binding):** `docs/explanation/accounting-compliance.md`
- **Terminology registry (binding):** `docs/explanation/terminology.md` ← **識別子を書く前に必ず確認**
- **Coding standards:** `docs/development/coding-standards.md`
- **CSV spec (binding):** `docs/explanation/csv-normalization-spec.md`
- **Output schema:** `docs/explanation/output-schema.md`
- **Clear downstream:** `docs/integrations/clear-downstream-contract.md`
- **Current work:** `docs/todo/current.md`

## Operating Rules

- Issue-driven; no direct commits to `main`
- **識別子はすべて `docs/explanation/terminology.md` を確認してから書く** — タイポ・スペルバリエーションはマージブロック
- Do **not** add invoice matching, payment write-back, or dunning — **`nene-clear`**
- Do **not** add document/PDF storage — **`nene-vault`**
- Do **not** duplicate Clear's reconciliation entities in Profile DB
- Namespace: `NeneProfile\`; amounts: integer cents in output schema
- **Repository docs and commits: Japanese or English only** — no other languages (ADR 0011)

## Framework

[NENE2](https://github.com/hideyukiMORI/NENE2) via Composer when runtime lands.
