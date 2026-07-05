# ADR 0005: Audit Logging of All Mutating Operations

## Status

accepted — write/read mechanism superseded by ADR 0012 (schema, action
naming, and sanitization rules below are unchanged and still apply)

## Context

`accounting-compliance.md` §7 requires an audit trail for import job operations,
and the operator needs to be able to reconstruct the history of any entity change
for compliance, debugging, and accountability purposes. A reviewer or auditor must
be able to answer: who changed what, when, and what were the before/after states.

A consistent, cross-cutting mechanism is needed that works for all current and
future domains (organizations, users, mapping presets, import jobs) without
scattering ad-hoc logging.

Alternatives considered:

1. **Middleware-level logging** — rejected; middleware sees the HTTP request but
   not the domain before/after state and cannot name the entity changed.
2. **Repository-level logging** — rejected; repositories know the row but not
   the actor (request/auth context) nor the business action name.
3. **UseCase-level recording via an `AuditRecorder`** (chosen) — the use case
   has both the actor/tenant context and the before/after entity state, and names
   the business action. This is where the change is meaningful.

This design mirrors [NeNe Invoice ADR 0008](../../nene-invoice/docs/adr/0008-audit-logging.md).

## Decision

A dedicated `audit_logs` table records one row per mutating operation.

### Schema

| Column | Meaning |
|---|---|
| `id` | Auto-increment primary key |
| `actor_user_id` | The authenticated user who performed it (null for system/seed) |
| `organization_id` | Tenant the change belongs to (null for superadmin cross-org operations) |
| `action` | `{entity}.{verb}` e.g. `organization.created`, `import_job.completed` |
| `entity_type` | The affected entity name e.g. `organization`, `mapping_preset` |
| `entity_id` | The affected entity's primary key |
| `before_json` | Sanitized snapshot before the change (null for create) |
| `after_json` | Sanitized snapshot after the change (null for delete) |
| `created_at` | When the operation occurred (UTC) |

### Recording rules

- **Recording happens in the UseCase** via `Nene2\Audit\AuditRecorderFactoryInterface`
  (ADR 0012). Use cases receive the actor user ID and organization ID through
  their execute signature.
- **Before/after snapshots are sanitized arrays** — all public non-secret fields
  of the entity. Secrets (e.g. `password_hash`) are **never** included.
- **All create / update / delete operations** record an entry. Reads are not
  audited.
- **Atomic with the mutation** (ADR 0012): the audit write and the business
  mutation run in the same DB transaction via `DatabaseTransactionManagerInterface`,
  so both commit or both roll back. The one exception is `CreateImportJobUseCase`'s
  CSV parse/normalize phase, which stays outside any transaction by design
  (ADR 0004) — its parse-failure branch was never audited and still isn't.
- **Append-only**: no UPDATE or DELETE on `audit_logs` rows.

### Action naming convention

Format: `{entity_type}.{verb}` — all lowercase snake_case.

| Action | Trigger |
|---|---|
| `organization.created` | Superadmin creates an organization |
| `organization.deleted` | Superadmin deletes an organization |
| `user.created` | Admin creates a user (Phase 1+) |
| `user.updated` | Admin updates a user |
| `user.deleted` | Admin deletes a user |
| `mapping_preset.created` | Member creates a preset |
| `mapping_preset.updated` | Member updates a preset |
| `mapping_preset.deleted` | Member deletes a preset |
| `import_job.created` | Member starts an import job |
| `import_job.completed` | System marks job completed |
| `import_job.failed` | System marks job failed |

### JWT user ID

The JWT `sub` claim carries the **user's integer ID** (not email) so that
`AuthContext::userId()` can extract the actor without a database lookup. This
differs from RFC 7519's common practice of using a subject string, but is
consistent with NeNe Invoice's convention.

### Snapshot sanitization rules

| Entity | Included fields | Excluded fields |
|---|---|---|
| `organization` | id, name, slug, is_active, custom_domain, created_at, updated_at | — |
| `user` | id, email, role, organization_id, status, created_at, updated_at | **password_hash** |
| `mapping_preset` | id, name, bank_label, organization_id, is_deleted, created_at, updated_at | — |
| `mapping_preset_version` | id, preset_id, version_number, definition_json | — |
| `import_job` | id, organization_id, preset_version_id, original_filename, original_file_hash, status, row_count, error_count | — |

## Consequences

**Benefits**

- Uniform, compliance-aligned trail of who changed what, with before/after.
- Secrets excluded by explicit sanitization (not by convention).
- Future-proof: new mutating use cases follow the same pattern.
- `GET /admin/audit-logs` exposes the trail to admins for oversight.

**Costs / limitations**

- Use cases gain an `AuditRecorderFactoryInterface` dependency and actor parameters.

**Follow-up**

- ~~Wrap mutation + audit in one DB transaction when `DatabaseTransactionManagerInterface`
  is introduced to use cases.~~ Done — see ADR 0012.
- Add `GET /admin/audit-logs` pagination and filtering by `entity_type` or date range.

## Related

- [`../explanation/accounting-compliance.md`](../explanation/accounting-compliance.md) §7
- NeNe Invoice ADR 0008 (precedent)
- Issue: `#11`
