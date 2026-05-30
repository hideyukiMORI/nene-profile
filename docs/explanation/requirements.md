# Requirements

NeNe Profile — bank CSV normalization. MVP scope maps to **Phase 1–2** unless
noted.

See also: [`product-vision.md`](./product-vision.md),
[`domain-model.md`](./domain-model.md),
[`accounting-compliance.md`](./accounting-compliance.md).

---

## 1. Tenancy and user roles

NeNe Profile is **multi-tenant from the foundation** — see
[ADR 0006](../adr/0006-multi-tenancy-and-roles.md). Every tenant-scoped table
carries `organization_id`. A single install may run as one organization via the
default `single` resolution mode; agencies use `path` / `subdomain` /
`custom_domain`.

| Role | Scope | Capabilities | Phase |
|---|---|---|---|
| **superadmin** | Cross-tenant | Everything, incl. `manage_organizations`. `organization_id` may be NULL | 1 |
| **admin** | One org | Everything except `manage_organizations` — users, org settings, all presets and jobs | 1 |
| **member** | One org | CSV import operator: `manage_presets`, `manage_import_jobs`, `view_import_jobs`. Cannot manage users or settings | 1 |
| **viewer** | One org | Read-only: `view_import_jobs` — normalized output and job history | 3+ |

Authorization: `Role` enum + `Capability` enum, resolved per route by
`CapabilityMiddleware`. Admin JWT for mutating routes.

---

## 2. Core entities (Phase 1)

All tenant-scoped entities carry **`organization_id`** (ADR 0006).

| Entity | Purpose | Key fields |
|---|---|---|
| **organization** | Tenant | name, slug (unique), is_active, resolution_mode |
| **user** | Operator account | email, password_hash, role, organization_id (NULL for superadmin), status |
| **organization_settings** | Org-level config | organization_id, default_encoding, max_file_size_bytes, webhook_url (Phase 3) |
| **mapping_preset** | Named column-mapping template | organization_id, name, bank_label, is_deleted |
| **mapping_preset_version** | Immutable snapshot per preset edit | preset_id, version_number, definition_json, created_at — **frozen once used in a completed job** |
| **import_job** | One CSV normalization run | organization_id, actor_user_id, preset_version_id, original_filename, original_file_hash, status, row_count, error_count |
| **import_job_error** | Per-row error detail | import_job_id, raw_row_number, raw_snippet, message |
| **normalized_transaction** | Staging: one row per emitted StandardTransaction | import_job_id, all StandardTransaction fields |
| **audit_log** | Cross-entity mutation log | actor_user_id, organization_id, action, entity_type, entity_id, before_json, after_json |

---

## 3. Compliance requirements (binding)

> These rules are governed by
> [`accounting-compliance.md`](./accounting-compliance.md) (non-negotiable).
> A finance professional reviewing the system must find zero deviations.
> Any departure requires an ADR.

- [ ] Amount sign: deposit/inflow → positive `amount_cents`; withdrawal/outflow → negative. No float intermediate. (ADR 0003 §1)
- [ ] Date output: ISO 8601 YYYY-MM-DD. Two-digit years resolved by preset pivot. Japanese era dates converted by static table. Unresolvable dates → row error. (ADR 0003 §2–3)
- [ ] Encoding: UTF-8 and Shift_JIS detected before parse. Mojibake in required fields → row error; no silent byte replacement. (ADR 0003 §4)
- [ ] Row errors: logged in `import_job_errors` with row number, raw snippet, message. Job status `completed_with_errors` when any errors exist. No silent drops.
- [ ] Five provenance fields on every output row: `raw_row_number`, `import_job_id`, `preset_version_id`, `line_hash`, `schema_version`
- [ ] Original CSV: stored immutably before processing; SHA-256 hash in `import_job.original_file_hash`; never deleted by system processes (ADR 0004)
- [ ] Import job audit fields: actor, timestamps, preset version, row count, error count; completed records immutable
- [ ] Preset version freeze: versions referenced by completed jobs cannot be mutated
- [ ] Within-job duplicate detection: same `line_hash` twice → both rows in `import_job_errors`
- [ ] CSV export: formula injection strip on all cell values (leading `=`, `+`, `-`, `@`)
- [ ] All amounts: integer `_cents`; no float in DB, JSON, or test fixtures

---

## 4. Import job lifecycle

| Status | Meaning |
|---|---|
| `pending` | Upload received; awaiting worker |
| `running` | Processing rows |
| `completed` | All rows processed; zero errors |
| `completed_with_errors` | All rows attempted; one or more row errors |
| `failed` | Job aborted (undecodable file, system error) |

Terminal statuses (`completed`, `completed_with_errors`, `failed`) are **immutable**
— no status transition out of a terminal state.

---

## 5. Mapping preset model

A preset (`mapping_preset_version.definition_json`) defines:

- Encoding and delimiter detection hints (or override)
- Header row index (default 0)
- Column mappings: source column header → logical field + transformer
- Skip-row patterns
- Identity fields for `line_hash`

Transformers (Phase 1): `trim`, `date_ymd_slash`, `date_ymd_dash`,
`date_ymd_compact`, `amount_yen_to_cents`, `debit_credit_to_signed_cents`,
`single_column_signed_cents`, `regex_extract`.

Custom transformers require ADR + plugin interface (Phase 3+).

---

## 6. StandardTransaction output schema (v1.0)

See [`output-schema.md`](./output-schema.md) and
[ADR 0010](../adr/0010-standard-output-schema-stability.md).

Required fields: `schema_version`, `transaction_date`, `value_date`,
`amount_cents`, `description`, `currency` (`"JPY"`), `raw_row_number`,
`import_job_id`, `preset_version_id`, `line_hash`.

Optional fields: `counterparty`, `balance_cents`.

Breaking changes require a major version bump and ADR + Clear adapter update.

---

## 7. Phase features

### Phase 1 — API only

- [ ] Organization resolution middleware (default `single`; path/subdomain/custom_domain) + `organization_id` scoping on every query (ADR 0006)
- [ ] Admin JWT auth + `Role`/`Capability` RBAC
- [ ] Organization CRUD — superadmin (`/admin/organizations`)
- [ ] User CRUD — admin within organization (`/admin/users`)
- [ ] Organization settings CRUD
- [ ] Mapping preset CRUD + versioning
- [ ] Import job: upload CSV + select/inline preset → run → export
- [ ] Row-level error logging per compliance rules (§3)
- [ ] Export: `GET /admin/profile/import-jobs/{id}/export.json` and `.csv`
- [ ] `GET /health` unauthenticated
- [ ] OpenAPI 3.1 + PHPUnit + PHPStan 8

### Phase 2 — Admin UI

- [ ] Admin SPA: preset editor (column-drop from sample file), job status, error table, export download
- [ ] Admin UI locale catalogs: **ja (primary) + en (secondary)** (ADR 0011)
- [ ] Dashboard: recent jobs, error rate

### Phase 3 — Ecosystem

- [ ] Bundled bank presets: MUFG, SMBC, PayPay Bank, Rakuten Bank
- [ ] Preset export / import JSON between tenants (community sharing)
- [ ] MCP tools: `runProfileImport`, `listMappingPresets`
- [ ] Clear HTTP pull: `POST /admin/profile/import-jobs/{id}/export.json` from Clear (downstream contract)
- [ ] Webhook callback for async job completion (optional, org-settings)

---

## 8. API requirements

- JSON API; OpenAPI 3.1 contract
- RFC 9457 Problem Details for errors
- snake_case JSON properties
- Pagination: `limit`, `offset`, `items` envelope
- Admin routes under `/admin/…`
- `GET /health` unauthenticated

---

## 9. Security requirements

- Admin JWT for mutating routes; `Capability` enforced per route (ADR 0006)
- **Tenant isolation**: every query scoped by resolved `organization_id`; cross-tenant reads/writes prohibited. Only superadmin operates cross-tenant
- CSV formula injection: strip leading `=`, `+`, `-`, `@` on export
- No stack traces in production responses
- Original CSV stored with restricted read access (operator filesystem permissions); not served via public URL
- Secrets in `.env` only — never committed

---

## 10. Explicit non-goals

| Item | Reason |
|---|---|
| Invoice / payment matching | NeNe Clear's domain (ADR 0009) |
| Dunning / collection notices | NeNe Clear's domain |
| PDF / document storage | NeNe Vault's domain |
| Issuing statutory documents | NeNe Invoice's domain |
| 電帳法 compliant archive (JIIMA 認証レベル) | NeNe Vault; Profile provides input material, not the archive |
| 第三者タイムスタンプ | NeNe Vault |
| Accounting classification (仕訳) | Accounting software |
| Consumption tax calculation | Not Profile's concern |
| Bank API live import (CSV first) | Phase 1–3 scope is file upload; bank API is ADR-gated Phase 4+ |
| Multi-currency (Phase 1–3) | JPY only; extending requires ADR |
| Multilingual UI beyond ja/en | Domain locked to Japanese accounting rules (ADR 0011) |

---

## 11. Acceptance tests (Phase 1 smoke)

1. Create preset mapping `transaction_date`, `amount_cents` (debit/credit columns), `description` from a sample MUFG CSV.
2. Run import job on 100-row CSV → 100 StandardTransaction rows in normalized output.
3. One injected bad-date row → job `completed_with_errors`; error logged with row number and raw snippet.
4. Export CSV validates against schema v1.0 (all required fields present, amount integers, ISO dates).
5. Re-upload identical file → operator sees clear duplicate warning when downloading (line_hash collision).
6. SHA-256 of stored original file matches `import_job.original_file_hash`.

---

## Related

- **Compliance (binding):** [`accounting-compliance.md`](./accounting-compliance.md)
- **Scope contract:** [`scope-contract.md`](./scope-contract.md)
- **Domain model:** [`domain-model.md`](./domain-model.md)
- **CSV spec:** [`csv-normalization-spec.md`](./csv-normalization-spec.md)
- **Output schema:** [`output-schema.md`](./output-schema.md)
- **Roadmap:** [`../roadmap.md`](../roadmap.md)

Last updated: 2026-05-30
