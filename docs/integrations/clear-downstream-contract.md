# Clear Downstream Contract (binding)

**Status: binding** between `nene-profile` (producer) and `nene-clear` (consumer).

## Purpose

Define how **StandardTransaction** output from Profile becomes input for Clear's
`bank_transaction` ingestion — without shared databases.

---

## Producer obligations (NeNe Profile)

1. Emit records conforming to [`output-schema.md`](../explanation/output-schema.md) v1.0.
2. Expose:
   - `GET /admin/profile/import-jobs/{id}/export.json`
   - `GET /admin/profile/import-jobs/{id}/export.csv`
3. Include `import_job_id`, `preset_version_id`, `line_hash` on every row.
4. Document row-level errors; do not omit failed rows silently from export without flag.

---

## Consumer obligations (NeNe Clear)

1. Accept Profile export as alternative to raw bank CSV import.
2. Map StandardTransaction → `bank_transaction` fields:
   | Profile | Clear |
   | --- | --- |
   | `transaction_date` | `value_date` |
   | `amount_cents` | `amount_cents` |
   | `description` | `description` |
   | `counterparty` | `counterparty_name` |
   | `line_hash` | duplicate detection key |
   | `import_job_id` | `external_import_ref` (metadata) |
3. Clear stores bank evidence per Clear ADR 0012 after ingest — Profile job audit
   remains in Profile DB.
4. Clear MUST NOT re-parse original bank CSV when Profile export is provided.

---

## HTTP integration (Phase 2)

```
Clear  POST  Profile /admin/profile/import-jobs/{id}/export.json
       ←  StandardTransaction[]
Clear  →  persist bank_import_batch + bank_transaction
```

Auth: service bearer token (`NENE_PROFILE_BEARER_TOKEN` on Clear side;
`NENE_CLEAR_CALLBACK` optional on Profile for async jobs — Phase 3).

---

## Failure modes

| Condition | Behavior |
| --- | --- |
| Profile unavailable | Clear MAY fall back to legacy raw CSV import (until removed) |
| Schema version mismatch | Clear rejects with explicit error; no partial ingest |
| Duplicate `line_hash` | Clear warns operator; does not auto-skip without confirm |

---

## Change control

Schema or field mapping changes require:
1. ADR in owning repo
2. Update to this contract file in **both** repos (same PR cycle or linked PRs)

---

Last updated: 2026-05-29
