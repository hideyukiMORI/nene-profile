# Standard Output Schema (binding)

**Status: binding.** Version **1.0** — breaking changes require ADR 0010 amendment
and coordinated Clear update.

NeNe Profile emits **StandardTransaction** records. All amounts are **integer
cents** (JPY). Dates are **ISO 8601** `YYYY-MM-DD`.

---

## JSON record

```json
{
  "schema_version": "1.0",
  "transaction_date": "2026-05-15",
  "value_date": "2026-05-15",
  "amount_cents": 150000,
  "description": "振込 カブシキガイシャアオイ",
  "counterparty": "カブシキガイシャアオイ",
  "balance_cents": 5025000,
  "currency": "JPY",
  "raw_row_number": 42,
  "import_job_id": "01J...",
  "preset_version_id": "01J...",
  "line_hash": "sha256:..."
}
```

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `schema_version` | string | yes | `"1.0"` |
| `transaction_date` | date | yes | Bank transaction date |
| `value_date` | date | yes | Value/settlement date |
| `amount_cents` | integer | yes | Signed; inflow positive |
| `description` | string | yes | Max 500 chars |
| `counterparty` | string | no | Max 200 chars |
| `balance_cents` | integer | no | Running balance if source had it |
| `currency` | string | yes | `"JPY"` only Phase 1 |
| `raw_row_number` | integer | yes | Source CSV line |
| `import_job_id` | string | yes | ULID/UUID |
| `preset_version_id` | string | yes | Mapping version used |
| `line_hash` | string | yes | SHA-256 of identity fields |

---

## CSV export columns (ordered)

```
schema_version,transaction_date,value_date,amount_cents,description,counterparty,balance_cents,currency,raw_row_number,import_job_id,preset_version_id,line_hash
```

UTF-8 with BOM optional for Excel.

---

## Clear consumption rules

NeNe Clear **MUST** accept Profile output schema v1.0 as bank line input without
re-parsing original bank CSV. See [`clear-downstream-contract.md`](../integrations/clear-downstream-contract.md).

---

## Versioning policy

- Patch: clarifications, optional fields
- Minor: new optional fields backward compatible
- Major: rename/remove required fields → new `schema_version`, ADR, Clear adapter

---

Last updated: 2026-05-29
