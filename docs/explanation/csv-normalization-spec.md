# CSV Normalization Specification (binding)

**Status: binding for NeNe Profile engineering.**

Defines how raw bank CSV becomes **StandardTransaction** rows.

See: [`output-schema.md`](./output-schema.md), [`scope-contract.md`](./scope-contract.md).

---

## 1. Input handling

| Rule | Detail |
| --- | --- |
| Encodings | UTF-8 (BOM optional), Shift_JIS — detect via BOM + fallback |
| Delimiter | Auto: comma or tab; operator override per preset |
| Header row | Configurable index (default 0); skip preamble rows |
| Max rows | Configurable (default 50,000 per job) |
| Max file size | Default 10 MB Phase 1 |
| Original file | Stored immutable with SHA-256; never edited |

---

## 2. Mapping preset model

A **preset** (`mapping_preset`) contains:

```json
{
  "name": "MUFG ordinary deposit v2024",
  "bank_label": "MUFG",
  "encoding": "auto",
  "delimiter": "auto",
  "header_row_index": 0,
  "columns": {
    "transaction_date": { "source": "日付", "transform": "date_ymd_slash" },
    "value_date": { "source": "起算日", "transform": "date_ymd_slash", "optional": true },
    "amount_cents": { "source": ["入金金额", "出金金额"], "transform": "debit_credit_to_signed_cents" },
    "description": { "source": "摘要", "transform": "trim" },
    "balance_cents": { "source": "残高", "transform": "amount_yen_to_cents", "optional": true },
    "counterparty": { "source": "相手先", "transform": "trim", "optional": true }
  },
  "skip_rows_matching": ["^$"],
  "line_identity": ["transaction_date", "amount_cents", "description"]
}
```

### 2.1 Logical fields (required vs optional)

| Field | Required in output | Notes |
| --- | --- | --- |
| `transaction_date` | **Yes** | Calendar date of transaction |
| `amount_cents` | **Yes** | Signed integer; deposit positive |
| `description` | **Yes** | Bank memo text |
| `value_date` | No | Defaults to transaction_date |
| `counterparty` | No | Empty string if absent |
| `balance_cents` | No | If bank provides running balance |
| `raw_row_number` | **Yes** (system) | 1-based source line for audit |

### 2.2 Built-in transformers (Phase 1)

| ID | Behavior |
| --- | --- |
| `trim` | Unicode trim whitespace |
| `date_ymd_slash` | `YYYY/MM/DD`, `YY/MM/DD` |
| `date_ymd_dash` | `YYYY-MM-DD` |
| `date_ymd_compact` | `YYYYMMDD` |
| `amount_yen_to_cents` | Parse yen string → integer cents (no float) |
| `debit_credit_to_signed_cents` | Two columns: deposit positive, withdrawal negative |
| `single_column_signed_cents` | One column with sign or DR/CR suffix |
| `regex_extract` | Params: `pattern`, `group` |

Custom transformers require ADR + plugin interface (Phase 3+).

---

## 3. Import job lifecycle

1. `POST /admin/profile/import-jobs` — upload CSV + preset_id (or inline mapping)
2. Validate header row matches preset expectations (warn on mismatch)
3. Process rows → `normalized_transaction` staging table OR stream to export
4. Row errors collected in `import_job_error` (row number, message, raw snippet)
5. Job status: `pending` → `running` → `completed` | `completed_with_errors` | `failed`
6. Output artifacts: JSON array, CSV download, optional webhook (Phase 3+)

---

## 4. Duplicate detection (within job)

If `line_identity` fields hash to duplicate within same job → flag row, do not
silently drop (operator review in UI).

Cross-job duplicate detection is **Clear's responsibility**, not Profile MVP.

---

## 5. Preset versioning

- Editing preset creates new `mapping_preset_version`; old imports reference old version.
- Never mutate preset definition used by completed jobs.

---

## 6. Security

- CSV formula injection: strip leading `=`, `+`, `-`, `@` from cell values on export
- No arbitrary code execution in transformers (declarative only Phase 1)

---

## Related

- Output: [`output-schema.md`](./output-schema.md)
- Clear handoff: [`../integrations/clear-downstream-contract.md`](../integrations/clear-downstream-contract.md)

Last updated: 2026-05-29
