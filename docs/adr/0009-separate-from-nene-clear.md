# ADR 0009: Separate Domain from NeNe Clear

## Status

accepted

## Context

Bank CSV handling appears in both Profile (normalization) and Clear (reconciliation).
Without a hard boundary, two teams implement duplicate preset engines and Clear
becomes a monolith.

## Decision

### NeNe Profile owns ONLY

- CSV upload and encoding detection
- Column mapping presets and transformers
- Import jobs and row-level error reporting
- **StandardTransaction** output ([`output-schema.md`](../explanation/output-schema.md))
- Audit of mapping/version used per job

### NeNe Profile does NOT own

- Matching bank lines to invoices (`payment_reconciliation`) → **Clear**
- Payment write-back to Invoice API → **Clear**
- Dunning → **Clear**
- 電子帳簿保存法 retention of bank evidence as SSOT → **Clear** (Clear stores
  imported lines after handoff; Profile stores transform audit)

### Handoff model

```
Profile: raw CSV → StandardTransaction JSON/CSV
Clear:   StandardTransaction → bank_transaction → match → dunning
```

Clear MAY ingest Profile output via:
1. HTTP `POST` export endpoint from Profile, or
2. File upload of Profile-exported CSV (same schema)

Clear MUST NOT require a shared database with Profile.

### Temporary overlap

Until Profile MVP ships, Clear may retain minimal CSV import. When Profile MVP
is available, Clear should delegate parsing to Profile (document in Clear ADR).

## Consequences

- Profile is usable standalone (Excel export) — not only as Clear accessory.
- Output schema v1.0 is a **contract** between repos.

## Related

- [`../integrations/clear-downstream-contract.md`](../integrations/clear-downstream-contract.md)
- ADR 0010: Output schema stability
