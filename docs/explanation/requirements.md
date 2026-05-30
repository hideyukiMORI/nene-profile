# Requirements

NeNe Profile — bank CSV normalization only.

See [`csv-normalization-spec.md`](./csv-normalization-spec.md), [`output-schema.md`](./output-schema.md).

---

## Phase 1 — API

- [ ] CRUD `mapping_preset` + versioning
- [ ] `POST import-jobs` upload CSV + preset
- [ ] Row processing with transformers (spec §2.2)
- [ ] Export JSON + CSV (output schema v1.0)
- [ ] Multi-tenant RBAC (ADR 0006)
- [ ] OpenAPI + PHPUnit + PHPStan 8

## Phase 2 — Admin UI

- [ ] Preset editor (column dropdown from sample upload)
- [ ] Import job status + error table
- [ ] Download export
- [ ] ja + en UI

## Phase 3 — Ecosystem

- [ ] Bundled presets (MUFG, SMBC, PayPay, Rakuten)
- [ ] Preset export/import JSON between tenants
- [ ] MCP `runProfileImport`, `listMappingPresets`
- [ ] Clear HTTP pull integration

## Acceptance tests (MVP)

1. Create preset mapping 4 columns from sample CSV.
2. Run import job on 100 rows → 100 StandardTransaction rows.
3. Export CSV validates against schema v1.0.
4. Inject bad date row → `completed_with_errors` + error detail.

Last updated: 2026-05-29
