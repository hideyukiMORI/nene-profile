# Transform Integrity & Compliance Self-Review

**Binding.** Use for **any** change touching transformers, import job processing,
original file storage, provenance fields, preset versioning, or export formatting.
If unsure whether a change has compliance impact, **assume it does** and run
this checklist.

Source of truth: [`../explanation/accounting-compliance.md`](../explanation/accounting-compliance.md).
Do not delete items to pass. Mark `N/A` only when genuinely not applicable and
explain why in the PR.

---

## 1. Amount sign integrity

- [ ] Deposit / inflow columns produce **positive** `amount_cents`
- [ ] Withdrawal / outflow columns produce **negative** `amount_cents`
- [ ] No float at any intermediate step; integer arithmetic throughout
- [ ] Row where both debit and credit columns are non-zero → `import_job_errors`, not emitted
- [ ] Row where both columns are empty → `import_job_errors`, not emitted
- [ ] Sign convention is **not** configurable per preset; this rule is invariant

## 2. Date integrity

- [ ] Output date format is ISO 8601 `YYYY-MM-DD` without exception
- [ ] Two-digit year: preset pivot year configured or default (50) applied; `job_warning` emitted when default is used
- [ ] Japanese era dates: converted via ADR 0003 §3 static table
- [ ] Reiwa 1 January–April boundary: treated as error (not valid Reiwa; use Heisei 31)
- [ ] Unambiguously unparseable date → `import_job_errors`; no guessed date emitted

## 3. Encoding

- [ ] UTF-8 (BOM optional) and Shift_JIS handled before parsing begins
- [ ] Mojibake (replacement chars or broken multibyte) in `description` or `counterparty` → `import_job_errors` with hex-escaped snippet
- [ ] No silent byte replacement with `?` or space in identity or required fields
- [ ] Fully undecodable file → job `failed` immediately

## 4. Row error handling

- [ ] Every error row logged in `import_job_errors` with 1-based `raw_row_number`, raw snippet (≤ 500 bytes), and error message
- [ ] Job with any errors completes as `completed_with_errors`, never `completed`
- [ ] Error count and row count present in job summary and surfaced in admin UI
- [ ] No "warn and continue silently" path exists in this change

## 5. Provenance fields on every output row

- [ ] `raw_row_number` present (1-based source line)
- [ ] `import_job_id` present
- [ ] `preset_version_id` present
- [ ] `line_hash` present (`sha256:…` of `transaction_date + amount_cents + description`)
- [ ] `schema_version` present (`"1.0"`)

## 6. Original file

- [ ] Original CSV stored before processing begins (ADR 0004)
- [ ] SHA-256 hash computed and stored in `import_job.original_file_hash` at upload time
- [ ] No code path modifies, deletes, or overwrites the original file
- [ ] Original retained even if job fails or is cancelled

## 7. Import job audit record

- [ ] `actor_user_id`, `organization_id`, `preset_version_id`, `original_filename`,
  `original_file_hash`, `started_at`, `completed_at`, `status`, `row_count`,
  `error_count` all populated at job completion
- [ ] No `UPDATE import_jobs` targeting a row already in terminal status
  (`completed`, `completed_with_errors`, `failed`)

## 8. Preset version immutability

- [ ] Editing a preset creates a **new** `mapping_preset_version`; old version unchanged
- [ ] No UPDATE on `mapping_preset_versions` rows referenced by any completed job
- [ ] Soft-deleting a preset does not cascade to versions referenced by completed jobs

## 9. Within-job duplicate detection

- [ ] Rows producing the same `line_hash` within the same job → each flagged in `import_job_errors`
- [ ] Duplicates not silently admitted to output

## 10. Export / CSV formatting

- [ ] CSV export strips leading `=`, `+`, `-`, `@` from all cell values
- [ ] All StandardTransaction required fields present in export
- [ ] UTF-8 (BOM optional for Excel compatibility) encoding on export

## 11. Money representation

- [ ] All amounts stored and transmitted as integer minimum currency units (`*_cents`)
- [ ] No float or DECIMAL in DB schema, API JSON, or test fixtures touched by this change

## 12. Compliance impact statement

- [ ] PR description states compliance impact explicitly ("no compliance impact" is also acceptable if true and justified)
- [ ] Any deviation from binding rules in `accounting-compliance.md` has an accompanying ADR before merging
