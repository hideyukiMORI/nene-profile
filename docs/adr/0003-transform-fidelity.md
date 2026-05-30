# ADR 0003: Transform Fidelity Rules — Amount Sign, Date, and Encoding

## Status

accepted

## Context

NeNe Profile transforms raw bank CSV into StandardTransaction rows. Three
transform operations are especially dangerous because a silent error creates
a **material accounting misstatement** — not a cosmetic bug:

1. **Amount sign** — inverting income and expense causes wrong P&L and BS
2. **Date year ambiguity** — two-digit years and Japanese era dates can resolve
   to wrong years, causing wrong accounting period recognition (期ずれ)
3. **Encoding** — mojibake (文字化け) in `description` or `counterparty` fields
   corrupts line_hash identity and makes the output untrustworthy

Each of these cases has multiple plausible interpretations, and choosing the
wrong one silently — without operator notification — is a compliance failure
regardless of intent.

These decisions are binding for all transformer implementations (Phase 1+).
See [`../explanation/accounting-compliance.md`](../explanation/accounting-compliance.md)
§3.

---

## Decisions

### §1 — Amount sign convention

**Decision:** Deposit / inflow → **positive** `amount_cents`. Withdrawal /
outflow → **negative** `amount_cents`. This is fixed and cannot be overridden
per preset.

**Rationale:** Japanese accounting convention (and international double-entry
bookkeeping) records inflows to a bank account as increases (positive) and
outflows as decreases (negative). A preset that inverts signs for a specific
bank would produce output incompatible with the StandardTransaction schema
and with downstream Clear / accounting software expectations.

**Error cases (MUST log in `import_job_errors`, MUST NOT guess):**

| Case | Handling |
|---|---|
| Both deposit and withdrawal columns non-zero on same row | Error: `"both debit and credit non-zero on row N"` |
| Both deposit and withdrawal columns empty | Error: `"no amount on row N"` |
| One-column signed: ambiguous sign marker | Error: `"unrecognized sign marker on row N"` |

Alternatives considered:
- Per-preset sign override — rejected; a wrong preset setting would silently
  invert all amounts with no way for a downstream reviewer to detect it
- Warn and emit best-guess — rejected; a guessed sign for a financial amount
  is a compliance failure

---

### §2 — Two-digit year resolution

**Decision:** Two-digit years are resolved by a **configurable pivot year**
(default: `50`). Years 00–49 → 2000–2049; years 50–99 → 1950–1999.
The pivot is stored in the preset and surfaced in the admin UI.

**Rationale:** Japanese bank CSV formats sometimes output two-digit years
(e.g. `26/05/15` for 2026-05-15). No single universal rule works for all
operator contexts — a payroll-related preset may see dates in 1999; an active
business account will not. Making the pivot configurable per preset allows
correctness without per-bank hardcoding.

**Error cases:**

| Case | Handling |
|---|---|
| Two-digit year with no pivot configured | Use default pivot (50); emit a `job_warning` in job metadata |
| Year value not in 00–99 range after parse | Error |
| Date that is structurally invalid after year expansion (e.g. 2026-13-45) | Error |

Alternatives considered:
- Fixed pivot at 68 (POSIX/UNIX convention) — rejected; Japanese bank context
  makes dates in 1969–1999 plausible; operators must set their own boundary
- Reject all two-digit years — rejected; too strict for real bank CSV formats
  in active use
- Silent default to current century — rejected; silently wrong for legacy data

---

### §3 — Japanese imperial era date conversion

**Decision:** Built-in static conversion table for the four modern eras.
Era codes outside valid date ranges are errors.

| Era | Prefix | Gregorian start | Max year | Notes |
|---|---|---|---|---|
| 令和 (Reiwa) | R / 令 | 2019-05-01 | R99 = 2117 | R1.1.1–R1.4.30 = Heisei 31 boundary (see below) |
| 平成 (Heisei) | H / 平 | 1989-01-08 | H31 = 2019 | H31.4.30 is the last valid Heisei date |
| 昭和 (Showa) | S / 昭 | 1926-12-25 | S64 = 1989 | S64.1.7 is the last valid Showa date |
| 大正 (Taisho) | T / 大 | 1912-07-30 | T15 = 1926 | Accepted; rare in active bank data |

**Reiwa boundary rule:** Dates R1.1.1 through R1.4.30 are valid Reiwa dates
(Reiwa 1 started on 2019-05-01; January–April 2019 are Heisei 31 only). If
a preset receives R1.M.D where M ∈ {1,2,3,4}, treat as an error:
`"Reiwa 1 January–April is not a valid date; use Heisei 31"`.

**Error cases:**

| Case | Handling |
|---|---|
| Era year exceeds maximum for era | Error |
| Unrecognized era prefix | Error |
| Valid era but month/day invalid | Error |

Alternatives considered:
- On-demand era lookup from NTA API — rejected; adds external dependency and
  network failure risk; the conversion table is static law, not dynamic data
- Support only 令和 and 平成 — rejected; legacy 昭和 data appears in some long-
  running account histories

---

### §4 — Encoding error handling

**Decision:** Mojibake in required or identity fields is a row error, not a
warning. No silent byte replacement.

**Detection method:** After decoding, scan `description` and `counterparty`
for replacement characters (U+FFFD) or known Shift_JIS overlong sequences.
If detected → row error with hex-escaped raw bytes.

**Error cases:**

| Case | Handling |
|---|---|
| File encoding ambiguous (BOM absent, heuristic fails) | Default UTF-8; emit `job_warning`; operator can force via preset |
| Row contains replacement character in identity/required field | Row error |
| Row contains replacement character in optional field only | Row error with note which field |
| File is fully undecodable | Job fails immediately (`status: failed`) |

Alternatives considered:
- Replace unrecognizable bytes with `?` silently — rejected; corrupts
  `line_hash` identity fields and produces untraceable output
- Always prefer Shift_JIS — rejected; UTF-8 bank CSV is increasingly common

---

## Consequences

**Benefits**
- Accounting errors from wrong signs, wrong years, or wrong encoding are
  surfaced as errors, not silently emitted
- Downstream systems (Clear, accounting software) can trust the sign and date
  of every admitted StandardTransaction row
- Preset configuration is explicit and auditable — no hidden rules

**Costs**
- More rows may land in `import_job_errors` compared to a "best-effort" approach
- Operators must configure pivot year for presets with two-digit year data
- Reiwa boundary edge case requires operator awareness for legacy data

## Related

- [`../explanation/accounting-compliance.md`](../explanation/accounting-compliance.md) §3
- [`../explanation/csv-normalization-spec.md`](../explanation/csv-normalization-spec.md) §2
- Supersedes: none
- Superseded by: none
