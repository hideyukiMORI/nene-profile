# Scope Contract — GOAL / DO / DON'T (binding)

**Status: binding (non-negotiable).** Charter for NeNe Profile.

Read first: [ADR 0009](../adr/0009-separate-from-nene-clear.md),
[`csv-normalization-spec.md`](./csv-normalization-spec.md),
[`output-schema.md`](./output-schema.md).

---

## GOAL

> **NeNe Profile turns any Japanese bank CSV into a predictable, bank-agnostic
> transaction stream — so operators stop fighting column layouts and Clear can
> focus on matching, not parsing.**

Concretely:

1. Operator uploads raw bank CSV.
2. Selects or creates a **mapping preset** (column → field, transformers).
3. Profile outputs **StandardTransaction** rows (JSON or CSV).
4. Downstream tools (Clear, Excel, scripts) consume output **without** re-parsing bank formats.
5. Presets are shareable/exportable within tenant (community presets Phase 3+).

---

## DO — Profile owns these

| # | Profile does |
| --- | --- |
| D1 | Parse CSV with encoding detection (UTF-8, Shift_JIS) |
| D2 | **Column mapping** UI/API: source column → logical field |
| D3 | **Transformers**: date parse, amount sign, debit/credit merge, trim, regex extract |
| D4 | **Presets** per bank/format version (`preset` entity) |
| D5 | **Import job** with row-level errors (skip bad rows, report) |
| D6 | Emit **StandardTransaction** per [`output-schema.md`](./output-schema.md) |
| D7 | Store mapping definition + job provenance (hash, actor, timestamp) |
| D8 | Export normalized CSV/JSON download |
| D9 | Multi-tenant RBAC (ADR 0006) |
| D10 | OpenAPI + MCP for `runImport`, `listPresets` |

---

## DON'T — Profile must never do these

| # | Profile must NOT | Belongs to |
| --- | --- | --- |
| X1 | Match bank lines to invoices | **NeNe Clear** |
| X2 | Write payments to Invoice API | **NeNe Clear** |
| X3 | Send dunning notices | **NeNe Clear** |
| X4 | Store received PDFs / 電帳法 document archive | **NeNe Vault** |
| X5 | Issue quotes or invoices | **NeNe Invoice** |
| X6 | Hardcode only 3 banks with no user mapping | Violates product goal |
| X7 | Mutate source CSV file in place | Provenance — keep original upload |
| X8 | Share DB with Clear | HTTP/file handoff only (ADR 0002) |
| X9 | Claim 真実性の確保 for **accounting evidence** alone | Clear/Vault own retention posture; Profile owns **transform audit** |
| X10 | Auto-import from bank API (MVP) | CSV upload first; API via ADR later |

---

## Handoff to Clear

Profile output is **input** to Clear. Contract:
[`../integrations/clear-downstream-contract.md`](../integrations/clear-downstream-contract.md).

Clear MAY call Profile HTTP API or accept uploaded Profile export — both documented.
Clear MUST NOT re-implement preset engine internally long-term (Clear may ship
thin adapter until Profile MVP lands — document in Clear repo).

---

## Compliance obligation

Profile's primary compliance obligation is **transform fidelity and provenance** —
not 電帳法 archiving. See
[`accounting-compliance.md`](./accounting-compliance.md) for binding rules.

The operator bears the 電帳法 retention obligation for original bank data;
Profile provides the building blocks (immutable original storage, per-row
traceability, searchable normalized output). NeNe Vault is the dedicated archive.

## Related

- **Compliance (binding):** [`accounting-compliance.md`](./accounting-compliance.md)
- ADR 0003: transform fidelity rules (amount sign, date, encoding)
- ADR 0004: original file immutability and provenance
- ADR 0009, ADR 0010 (output schema stability)
- [`csv-normalization-spec.md`](./csv-normalization-spec.md)

Last updated: 2026-05-29
