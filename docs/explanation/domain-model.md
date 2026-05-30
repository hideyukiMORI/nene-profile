# Domain Model

NeNe Profile domain overview — entities, relationships, and state machines.
Implementation follows Handler → UseCase → Repository layering.

See also: [`requirements.md`](./requirements.md), [`glossary.md`](./glossary.md),
[`accounting-compliance.md`](./accounting-compliance.md).

---

## Entity relationships (Phase 1)

```
organization ──< user
organization ──1 organization_settings
organization ──< mapping_preset ──< mapping_preset_version
organization ──< import_job ──> mapping_preset_version
import_job ──< import_job_error
import_job ──< normalized_transaction
organization ──< audit_log
```

- **organization**: the tenant (ADR 0006). Every row in a tenant-scoped table
  carries `organization_id`. Default resolution mode `single`.
- **user**: operator account with a `role` enum. `superadmin` is cross-tenant
  (`organization_id` NULL); all others belong to one organization.
- **organization_settings**: one config record per organization.
- **mapping_preset**: named template — not the definition itself. The editable
  metadata (name, bank_label). Soft-deletable.
- **mapping_preset_version**: immutable snapshot of the preset definition JSON
  at a point in time. A new version is created on every edit. Once referenced
  by a completed import job, the version is **frozen** — see ADR 0004.
- **import_job**: one CSV normalization run. Carries the full audit record.
  Terminal statuses are immutable.
- **import_job_error**: one row per transform or parse error. Links to
  `import_job`; contains raw_row_number, raw_snippet, message.
- **normalized_transaction**: staging table; one row per emitted
  StandardTransaction row. Tied to `import_job`. Not editable.
- **audit_log**: cross-cutting mutation log (ADR 0006 follow-up). One row per
  mutating operation across all entities.

---

## Import job state machine

```
              ┌─────────┐
   upload ──→ │ pending │
              └────┬────┘
                   │ worker picks up
                   ▼
              ┌─────────┐
              │ running │
              └────┬────┘
         ┌─────────┼──────────────┐
         │         │              │
         ▼         ▼              ▼
   ┌──────────┐ ┌─────────────────────┐ ┌────────┐
   │completed │ │completed_with_errors│ │ failed │
   └──────────┘ └─────────────────────┘ └────────┘
```

`completed`: all rows processed, zero row errors.
`completed_with_errors`: all rows attempted; ≥1 row in `import_job_errors`.
`failed`: job aborted — undecodable file, system error, no rows found.

Terminal statuses are **immutable**. No transition out of a terminal state.

---

## Preset versioning model

```
mapping_preset (1)
  └── mapping_preset_version (N, immutable after use)
        preset_id, version_number, definition_json, created_at

import_job ──→ mapping_preset_version (frozen reference)
```

- `mapping_preset_version.definition_json` is the complete, self-contained
  preset specification (encoding, delimiter, columns, transformers, skip rules,
  identity fields).
- Once an import job with this `preset_version_id` reaches a terminal status,
  `definition_json` MUST NOT be changed.
- Preset display name and metadata live on `mapping_preset`; version history
  is in `mapping_preset_version`.

---

## Immutability constraints by entity

| Entity | Immutability rule |
|---|---|
| `mapping_preset_version` | Frozen once referenced by a completed import job |
| `import_job` | Terminal status row: no field changes after completion |
| `import_job_error` | Write-once; no updates or deletes |
| `normalized_transaction` | Write-once per job; no updates or deletes |
| `import_job.original_file` (blob) | Never modified, renamed, or deleted by system processes |
| `audit_log` | Append-only; no updates or deletes |

---

## StandardTransaction output row

Each `normalized_transaction` row corresponds to one StandardTransaction record:

| Field | Required | Source |
|---|---|---|
| `schema_version` | yes | Constant `"1.0"` |
| `transaction_date` | yes | Transformed source column |
| `value_date` | yes | Source column or defaults to `transaction_date` |
| `amount_cents` | yes | Transformed; integer; deposit positive |
| `description` | yes | Transformed source column |
| `counterparty` | no | Source column or empty string |
| `balance_cents` | no | Source column if available |
| `currency` | yes | `"JPY"` (Phase 1–3 only) |
| `raw_row_number` | yes | 1-based source CSV line |
| `import_job_id` | yes | FK to `import_job` |
| `preset_version_id` | yes | FK to `mapping_preset_version` |
| `line_hash` | yes | `sha256(transaction_date + amount_cents + description)` |

---

## Related

- [`requirements.md`](./requirements.md)
- [`accounting-compliance.md`](./accounting-compliance.md)
- [`glossary.md`](./glossary.md)
- [ADR 0003](../adr/0003-transform-fidelity.md)
- [ADR 0004](../adr/0004-original-file-immutability.md)
- [ADR 0006](../adr/0006-multi-tenancy-and-roles.md)

Last updated: 2026-05-30
