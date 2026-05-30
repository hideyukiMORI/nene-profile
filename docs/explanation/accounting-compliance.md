# Transform Integrity & Compliance — Binding Rules

**Status: binding (non-negotiable).** This document is the source of truth for
NeNe Profile's data-integrity and compliance obligations. A finance or accounting
professional (税理士, 公認会計士) reviewing the system design must find **zero
silent deviation** from the rules below.

These are **MUST** requirements. Where a rule here conflicts with UX, performance,
or implementation convenience, **compliance wins — every time, without exception.**

See also: [`csv-normalization-spec.md`](./csv-normalization-spec.md),
[`output-schema.md`](./output-schema.md),
[`../review/compliance.md`](../review/compliance.md),
[ADR 0003](../adr/0003-transform-fidelity.md),
[ADR 0004](../adr/0004-original-file-immutability.md).

---

## 0. Governing principles

1. **Compliance is non-negotiable.** Correct adherence to the rules in this
   document takes precedence over every other product goal.
2. **No silent failure.** A bad row, a failed transform, or a data anomaly must
   surface to the operator — never be silently dropped or hidden.
3. **Transform audit is a first-class obligation.** Every output row must trace
   back to a specific source row, preset version, and import job. This
   traceability is not optional logging; it is a design invariant.
4. **Profile is not the compliance endpoint.** Profile produces normalized
   accounting *input material*. The operator and downstream systems (NeNe Vault,
   accounting software) bear the final record-retention obligation. Profile's
   duty is **faithful transformation and full provenance**.
5. **Engineering is not the legal authority.** When a compliance rule is unclear,
   stop and consult a 税理士 — do not guess. Record the resolved interpretation
   here as a PR. Code may not merge a deviation without that record.

---

## 1. Statutory context

NeNe Profile normalizes **bank CSV** — transaction data provided electronically by
financial institutions. This data constitutes **電子取引データ** under the Act on
Preservation of Electronic Accounting Books (電子帳簿保存法; 電帳法).

Since January 1, 2024, operators are legally required to preserve such data
**electronically** — paper printout is no longer acceptable.

| Law | Relevance to Profile |
|---|---|
| 電子帳簿保存法 第7条〜第10条 | Electronic transaction data (電子取引データ) preservation — Profile supports operator compliance via immutable original storage and searchable normalized output |
| 法人税法 第126条 | 7-year retention of tax-basis records — the operator's accounting system bears this obligation; Profile provides traceability to support it |
| 会社法 第432条 | 10-year retention of accounting books — same; Profile's output is input material for those books, not the books themselves |

**NeNe Profile does not claim to be a 電帳法 compliant archive.** It provides
building blocks for a compliant operator workflow:

- **真実性の確保 (authenticity):** supported via immutable original file storage
  (SHA-256), per-row provenance fields, and import job audit trail — but Profile
  does not provide 第三者タイムスタンプ (third-party timestamp)
- **可視性の確保 (visibility / searchability):** supported via StandardTransaction
  fields `transaction_date`, `amount_cents`, `counterparty` — the three
  search keys required under 電帳法 施行規則

---

## 2. Profile's position in the compliance chain

```
[Bank] ──→ [Bank CSV / 電子取引データ] ──→ [Operator receives electronically]
                                                          │
                                               NeNe Profile: normalize + audit
                                                          │
                                           StandardTransaction (accounting input)
                                                 │                    │
                              [NeNe Clear: reconciliation]   [Accounting software]
                              [NeNe Vault: 電帳法 archive]
```

### Profile owns

- Faithful transformation of bank CSV to StandardTransaction
- Immutable storage of the original file with SHA-256 hash
- Full per-row provenance (source row number, preset version ID, import job ID)
- Import job audit trail (actor, timestamp, outcome, error log)
- Surfacing **all** transformation errors to the operator

### Profile does NOT own

| What | Who owns it |
|---|---|
| 電帳法 retention of original bank data (7-year rule) | Operator / NeNe Vault |
| 第三者タイムスタンプ for 真実性の確保 | Operator / NeNe Vault |
| Accounting classification (仕訳) | Accounting software |
| Verification of amounts against actual bank balances | Operator / bank |
| Cross-job duplicate detection and reconciliation | NeNe Clear |
| Issuing statutory documents | NeNe Invoice |

---

## 3. Transform fidelity — binding rules

Deviations here are **accounting errors**, not software bugs.

### 3.1 Amount sign integrity (CRITICAL)

A sign inversion turns income into expense or vice versa — a material misstatement
that invalidates the operator's books.

**MUST:**
- Deposit / inflow (入金, 振込入金, 入金金額) → **positive** `amount_cents`
- Withdrawal / outflow (出金, 引落, 出金金額) → **negative** `amount_cents`
- No floating-point at any intermediate step; integer arithmetic throughout
- Two-column preset (`debit_credit_to_signed_cents`): if both the deposit
  column and the withdrawal column are non-zero on the same row, the row MUST
  be treated as a transform error and recorded in `import_job_errors`
- One-column preset (`single_column_signed_cents`): sign determined by DR/CR
  suffix or a leading minus; ambiguous rows treated as errors

This sign convention is **fixed**. It cannot be overridden per preset; it is an
invariant of the output schema.

See [ADR 0003](../adr/0003-transform-fidelity.md) §1.

### 3.2 Date integrity (CRITICAL)

A date error causes incorrect accounting period recognition (期ずれ), which
directly affects tax filings.

**MUST:**
- Output format: ISO 8601 **YYYY-MM-DD** without exception
- Two-digit year (e.g. `28/05/15`): resolved per ADR 0003 §2 pivot table —
  never silently assumed
- Japanese imperial era dates (令和, 平成, 昭和, 大正): converted per ADR 0003
  §3 conversion table — era codes outside valid ranges are errors
- If a date cannot be **unambiguously** parsed → row added to `import_job_errors`
  with the raw value; a guessed date MUST NOT be emitted
- Reiwa boundary (R1 = 2019, but R1.1.1–R1.4.30 = H31): see ADR 0003 §3

See [ADR 0003](../adr/0003-transform-fidelity.md) §2–3.

### 3.3 Encoding integrity

**MUST:**
- Detect encoding before parsing: UTF-8 (BOM optional) and Shift_JIS supported
- Mojibake (文字化け) detected by checking for broken multibyte sequences in
  `description` and `counterparty` fields → row added to `import_job_errors`
  with raw byte snippet (hex-escaped)
- No silent replacement of unrecognizable bytes with `?` or space in any field
  used in `line_hash` or required output fields

### 3.4 Formula injection (security + data integrity)

**MUST:**
- On **CSV export**, strip leading `=`, `+`, `-`, `@` from any cell value
- Applies to all fields; prevents spreadsheet formula injection and ensures
  exported amounts are data values, not formulas

---

## 4. Row error handling — no silent failure (CRITICAL)

**MUST:**
- Every unparseable or rule-violating row is logged in `import_job_errors` with:
  - 1-based source row number (`raw_row_number`)
  - Raw snippet of the failing content (max 500 bytes, UTF-8 safe)
  - Human-readable error message in English or Japanese (ADR 0011)
- A job with any row errors completes as **`completed_with_errors`**, never as
  `completed`
- Error rows are **excluded** from the normalized output; their exclusion is
  explicit in the job summary (`row_count` processed, `error_count` excluded)
- The operator MUST see error count and error details before exporting;
  errors must not be buried in hidden metadata

There is no "warn and continue silently" mode. Every anomaly is either an error
or is handled by an explicit rule documented in ADR 0003.

---

## 5. Provenance and traceability — binding

Every StandardTransaction output row MUST carry all five provenance fields:

| Field | Purpose |
|---|---|
| `raw_row_number` | 1-based source line — links output back to original CSV |
| `import_job_id` | Identifies the import run that produced this row |
| `preset_version_id` | Identifies the exact mapping definition applied |
| `line_hash` | SHA-256 of identity fields (`transaction_date` + `amount_cents` + `description`) |
| `schema_version` | Output schema version — contract signal for downstream systems |

These five fields are **non-optional** on every emitted row. An implementation
that omits any of them violates this rule.

See [ADR 0004](../adr/0004-original-file-immutability.md).

---

## 6. Original file — immutability

**MUST:**
- Original CSV is stored **before** any processing begins (ADR 0004)
- SHA-256 hash computed at upload time and stored in `import_job.original_file_hash`
- Original file MUST NOT be modified, deleted, or overwritten for any reason
  — including storage-cost reclamation, re-upload of "corrected" versions, or
  system cleanup jobs
- Original is retained regardless of whether the import job succeeded, failed,
  or was cancelled
- Any purge of original files requires an ADR, explicit operator confirmation
  in the admin UI, and is outside Phase 1–3 scope

---

## 7. Import job audit trail — binding

Every completed import job record MUST capture:

| Field | Content |
|---|---|
| `actor_user_id` | User who initiated the job |
| `organization_id` | Tenant |
| `preset_version_id` | Exact preset snapshot used |
| `original_filename` | As uploaded by the operator |
| `original_file_hash` | SHA-256 of file content |
| `started_at` / `completed_at` | Timestamps (UTC) |
| `status` | `pending` / `running` / `completed` / `completed_with_errors` / `failed` |
| `row_count` | Total source rows processed (excluding header) |
| `error_count` | Number of rows logged in `import_job_errors` |

Completed job records MUST NOT be mutated after reaching a terminal status
(`completed`, `completed_with_errors`, `failed`).

---

## 8. Preset version immutability

**MUST:**
- Once a preset version is referenced by a **completed** import job, the
  `mapping_preset_version` record is **frozen** — no field may be changed
- Editing a preset creates a **new version** (new ID, new record); old version
  remains accessible via `import_job.preset_version_id`
- A completed import job always references the exact preset definition that
  produced its output — this enables future re-verification
- Soft-deleting a preset does not delete versions referenced by completed jobs

---

## 9. Within-job duplicate detection

- If two or more rows in the same job produce the same `line_hash`, each
  duplicate row MUST be flagged in `import_job_errors`:
  `"duplicate line hash — matches row N (same date/amount/description)"`
- Duplicates are NOT silently admitted to the normalized output
- The operator reviews and decides; Profile does not auto-resolve
- Cross-job deduplication is **NeNe Clear's responsibility**, not Profile's

---

## 10. Money representation

All amounts: **integer minimum currency units** (JPY: ¥1 = 1 unit, column suffix
`_cents`). Float and DECIMAL are **prohibited** in the database, API JSON,
transform intermediates, and test fixtures.

---

## 11. What this document is NOT

- **Not** a complete 電帳法 compliance guide for operators. Operators must
  consult their 税理士 for their specific legal obligations under 電帳法.
- **Not** a declaration that Profile's normalized output has legal standing as
  a 帳簿 (accounting book) under Japanese law. Profile is a normalization utility.
- **Not** a substitute for operator review of import errors. Profile surfaces
  errors; the operator must act on them before relying on the output for
  accounting purposes.
- **Not** a substitute for a 第三者タイムスタンプ or 電子帳簿保存法適合ソフトウェア
  (JIIMA-certified system). NeNe Vault is the dedicated archive component.

---

## 12. How this rule applies to every change

Any change touching:
- Transformers (amount, date, encoding, sign logic)
- Row error handling or job status transitions
- Original file storage, hashing, or deletion
- Import job audit fields
- Preset versioning or version freeze logic
- StandardTransaction output fields or provenance fields
- Export formatting or formula-injection stripping

**MUST:**
1. Be reviewed against this document and [`../review/compliance.md`](../review/compliance.md)
2. State compliance impact explicitly in the PR description
3. If it deviates from any binding rule: carry an ADR before merging

---

Last updated: 2026-05-30
