# Glossary

Binding term definitions for NeNe Profile. When a term below conflicts with
informal usage elsewhere in the codebase, this glossary governs.

---

## Japanese law and accounting terms

| Term | Reading | Definition in Profile context |
|---|---|---|
| 電子帳簿保存法 | でんしちょうぼほぞんほう | Act on Preservation of Electronic Accounting Books. Sets retention requirements for electronic accounting data. Profile handles 電子取引データ (see below) and provides building blocks for operator compliance; Profile is not a compliant archive by itself. |
| 電子取引データ | でんしとりひきデータ | Electronic transaction data exchanged between business parties — in Profile's context, this includes bank CSV files downloaded from online banking. Operators must preserve this data electronically since 2024-01-01. |
| 真実性の確保 | しんじつせいのかくほ | Ensuring authenticity of preserved records. Profile contributes via immutable original file storage (SHA-256) and import provenance. 第三者タイムスタンプ is not provided by Profile. |
| 可視性の確保 | かしせいのかくほ | Ensuring visibility / searchability of preserved records. Profile's StandardTransaction output provides `transaction_date`, `amount_cents`, and `counterparty` — the three search keys required under 電帳法施行規則. |
| 第三者タイムスタンプ | だいさんしゃタイムスタンプ | Third-party timestamp from a certified time-stamp authority. Not provided by Profile. Operators requiring 真実性の確保 via timestamp must use NeNe Vault or an external certified service. |
| 仕訳 | しわけ | Journal entry — classifying a transaction into debit and credit accounts. Explicitly outside Profile scope. |
| 期ずれ | きずれ | Accounting period misassignment — recording a transaction in the wrong fiscal period. A date transform error (wrong year) causes this. ADR 0003 §2–3 governs how Profile prevents it. |
| 入金 | にゅうきん | Inflow to the bank account (deposit). Maps to **positive** `amount_cents` in Profile output. |
| 出金 | しゅっきん | Outflow from the bank account (withdrawal). Maps to **negative** `amount_cents` in Profile output. |
| 摘要 | てきよう | Transaction description field on bank statements. Maps to `description` in StandardTransaction. |
| 相手先 | あいてさき | Counterparty (paying or receiving party). Maps to `counterparty` in StandardTransaction. |
| 残高 | ざんだか | Running balance. Maps to `balance_cents` (optional) in StandardTransaction. |
| 適格請求書 | てきかくせいきゅうしょ | Qualified invoice under the Japanese invoice system (インボイス制度). **Not** issued by Profile — see NeNe Invoice. |
| 税理士 | ぜいりし | Licensed tax accountant (Certified Tax Accountant). The professional whose review of Profile's design must find zero deviation from binding compliance rules. |
| 公認会計士 | こうにんかいけいし | Certified Public Accountant (CPA). Same review standard as 税理士. |

---

## Domain terms

| Term | Definition |
|---|---|
| **StandardTransaction** | The normalized output record emitted by Profile for each successfully processed source row. Defined in [`output-schema.md`](./output-schema.md) v1.0. |
| **mapping_preset** | A named template that defines how a specific bank CSV format maps to StandardTransaction fields. Not the definition itself — the editable metadata. |
| **mapping_preset_version** | An immutable snapshot of a preset's definition JSON at a point in time. Frozen once referenced by a completed import job. |
| **import_job** | A single CSV normalization run: one file, one preset version, one operator, one outcome. The unit of work and audit. |
| **import_job_error** | A per-row error record within an import job. Contains raw row number, raw snippet, and message. Write-once. |
| **normalized_transaction** | A staging row holding one StandardTransaction output record, tied to an import job. Write-once. |
| **line_hash** | `sha256(transaction_date + amount_cents + description)` — the identity fingerprint of a StandardTransaction row. Used for within-job duplicate detection and downstream deduplication by Clear. |
| **transformer** | A named function applied to a source column value to produce a logical field value. Declarative and Phase-1-built-in only (no arbitrary code). |
| **preset_version_id** | The ULID/UUID of the `mapping_preset_version` record used in an import job. Recorded on every output row for traceability. |
| **raw_row_number** | The 1-based line number in the original source CSV (counting from the first data row after the header). Included in every output row and every error record. |
| **original_file_hash** | SHA-256 of the raw bytes of the uploaded CSV, computed before any processing. Stored in `import_job`. |
| **amount_cents** | Signed integer representing a monetary amount in the minimum currency unit of the currency (for JPY: ¥1 = 1). Positive = inflow, negative = outflow. No floats anywhere in the pipeline. |
| **schema_version** | A string field on every StandardTransaction row declaring the output schema version (currently `"1.0"`). Used by downstream systems to detect breaking changes. |
| **completed_with_errors** | Import job terminal status indicating all rows were attempted but some failed transform validation. The job output exists but is incomplete; operator must review errors. |
| **line_identity** | The set of fields used to compute `line_hash`. Defined per preset. Default: `[transaction_date, amount_cents, description]`. |
| **organization** | The tenant in the multi-tenant model (ADR 0006). Every tenant-scoped table row carries `organization_id`. |
| **superadmin** | Cross-tenant operator (platform owner or agency). `organization_id` is NULL. Can manage organizations. |
| **admin** | Single-organization operator. Manages users, settings, presets, and jobs within the org. |
| **member** | Single-organization CSV import operator. Can manage presets and run import jobs. Cannot manage users or settings. |
| **two-digit year** | A year component in a source date formatted as 2 digits (e.g. `26` instead of `2026`). Resolved via a pivot year configured on the preset (ADR 0003 §2). |
| **mojibake (文字化け)** | Garbled text resulting from encoding mismatch. Treated as a row error in required and identity fields (ADR 0003 §4). |

---

## Sign convention (binding)

The sign convention for `amount_cents` is **fixed and invariant**:

| Direction | `amount_cents` | Example |
|---|---|---|
| Money flows INTO the account | **positive** | 振込入金 ¥100,000 → `+10000000` |
| Money flows OUT OF the account | **negative** | 引落 ¥30,000 → `-3000000` |

This matches standard bank statement convention and Japanese bookkeeping
practice. A preset cannot override this — see ADR 0003 §1.

---

## Out of scope — terms that do NOT apply to Profile

| Term | Where it applies |
|---|---|
| 適格請求書 / インボイス制度 | NeNe Invoice |
| 消費税計算 | NeNe Invoice / accounting software |
| 消込 (消し込み) | NeNe Clear |
| 督促 | NeNe Clear |
| 電子帳簿保存法 archive (JIIMA 認証) | NeNe Vault |
| 仕訳 / 勘定科目 | Accounting software |

Last updated: 2026-05-30
