# ADR 0004: Original File Immutability and Import Provenance

## Status

accepted

## Context

NeNe Profile receives bank CSV files and produces normalized output. For the
output to be trustworthy in an accounting context, two invariants must hold:

1. **The original can be recovered and verified.** If a tax audit or accounting
   review raises a question about a normalized row, the operator must be able
   to produce the original bank CSV that contained the source row. Without the
   original, the claim "this normalized value accurately reflects the bank
   transaction" cannot be verified.

2. **The derivation path is traceable.** For each normalized row, an auditor
   must be able to identify exactly which source row, which preset version, and
   which import job produced it. Without this, the output is an unattributed
   aggregate — not an audit trail.

Bank CSV data is **電子取引データ** under 電子帳簿保存法 (電帳法). Profile is not
a 電帳法 compliant archive by itself (see
[`../explanation/accounting-compliance.md`](../explanation/accounting-compliance.md) §1–2),
but it must provide the raw material that operators and downstream systems
(NeNe Vault) need to meet their own 電帳法 obligations.

Alternatives considered:

1. **Store only the hash, not the file** — rejected; the hash proves integrity
   of a known file but does not enable re-reading or re-running when the original
   is lost. An auditor cannot examine a hash alone.
2. **Store the file but allow deletion after a configurable period** —
   rejected; automated purge could delete a file that a tax audit later
   requires; operator must make this decision explicitly.
3. **Store the file immutably; provide an explicit operator-controlled purge
   path (ADR-gated)** (chosen) — balances storage cost with audit safety.
4. **Deduplicate by content hash across organizations** — rejected; files with
   identical content uploaded by different organizations must remain
   independently owned and independently accessible per tenant isolation
   (ADR 0006).

## Decision

### Original file storage

- The original CSV file is stored **before any processing begins**.
- A SHA-256 hash of the raw file bytes is computed at upload time and stored
  in `import_job.original_file_hash`.
- The file is stored with a stable, organization-scoped path derived from
  `import_job_id` (not from content hash) to ensure per-job isolation.
- Phase 1: local filesystem under an operator-configured base path.
- Phase 2+: S3-compatible object storage; the path structure is unchanged.

### Immutability guarantee

- The original file **MUST NOT** be modified, deleted, or overwritten by any
  system process — including cleanup jobs, re-upload flows, or storage
  reclamation scripts.
- The original is retained regardless of whether the import job succeeded,
  failed, or was cancelled.
- Any operator-initiated purge of original files:
  1. Requires an explicit admin UI confirmation step (not a bulk API call)
  2. Is recorded in `audit_logs` with actor, timestamp, and file hash
  3. Is outside Phase 1–3 scope — no purge UI or API will be built until
     a separate ADR defines the retention policy and its compliance context

### Per-row provenance (StandardTransaction)

Every StandardTransaction output row MUST carry all five provenance fields:

| Field | Type | Derives from |
|---|---|---|
| `raw_row_number` | integer | 1-based source line in the original CSV |
| `import_job_id` | ULID/UUID | The job that processed this row |
| `preset_version_id` | ULID/UUID | The frozen preset snapshot used |
| `line_hash` | `sha256:…` | SHA-256 of `transaction_date + amount_cents + description` |
| `schema_version` | `"1.0"` | Contract version for downstream compatibility |

Omitting any field from an output row is a compliance violation.

### Import job audit record

Every import job captures a complete audit record at completion:

| Field | Meaning |
|---|---|
| `actor_user_id` | User who submitted the job (FK to `users`) |
| `organization_id` | Tenant (FK to `organizations`) |
| `preset_version_id` | Frozen preset snapshot used (FK to `mapping_preset_versions`) |
| `original_filename` | Filename as uploaded |
| `original_file_hash` | SHA-256 of raw bytes at upload time |
| `started_at` | UTC timestamp when processing began |
| `completed_at` | UTC timestamp when job reached terminal status |
| `status` | `pending` / `running` / `completed` / `completed_with_errors` / `failed` |
| `row_count` | Total source data rows processed (excluding header rows) |
| `error_count` | Number of rows logged in `import_job_errors` |

A completed job record **MUST NOT** be mutated after reaching a terminal status.

### Preset version freeze

- A `mapping_preset_version` record becomes **frozen** once any import job
  that references it reaches a terminal status.
- No field of a frozen version may be changed.
- Editing a preset creates a new `mapping_preset_version` with a new ID; the
  old version is permanently accessible via existing `import_job.preset_version_id`
  references.
- Soft-deleting a preset does not cascade to versions referenced by
  completed jobs. Those versions remain in the database indefinitely.

## Consequences

**Benefits**

- Original bank CSV can be produced on demand for any completed import job —
  satisfying audit or accounting review requests.
- Normalized output can be re-verified at any time: hash original file, compare
  to stored hash, re-run preset, compare output.
- Preset version freeze guarantees reproducibility: re-running the same import
  job with the same preset version produces identical output.
- Provides the traceability building blocks that NeNe Vault needs to meet
  operator 電帳法 obligations.

**Costs**

- Storage grows with every import job; no automatic reclamation.
- Purge policy must be added in a future ADR before Phase 4 to avoid
  unbounded growth.
- Every query touching import job data must respect immutability — no
  `UPDATE import_jobs SET status = … WHERE status = 'completed'`.

**Follow-up**

- Phase 2: S3-compatible storage path for original files.
- Phase 4+: ADR defining retention policy and operator-controlled purge UI.

## Related

- [`../explanation/accounting-compliance.md`](../explanation/accounting-compliance.md) §5–8
- [ADR 0006](./0006-multi-tenancy-and-roles.md): tenant isolation; files scoped per org
- [ADR 0010](./0010-standard-output-schema-stability.md): schema version in provenance
- Supersedes: none
- Superseded by: none
