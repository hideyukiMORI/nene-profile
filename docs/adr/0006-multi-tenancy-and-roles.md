# ADR 0006: Adopt Multi-Tenancy and Role Hierarchy as Foundational

## Status

accepted

## Context

The early product docs assumed Phase 1 was single-tenant, with multi-tenancy
deferred ("`company_settings` singleton per organization (Phase 1 single-tenant;
multi-tenant adds `organization_id`)"). In practice the product must support, from
the foundation:

- agencies and hosting operators running **one install for multiple client
  organizations**, and
- a **role hierarchy** where a platform operator manages organizations and an
  organization administrator manages that organization's users.

Sibling product [NeNe Records](https://github.com/hideyukiMORI/nene-records)
already implements exactly this on NENE2 (per-request organization resolution,
`Role`/`Capability` enums, `CapabilityMiddleware`, `organization_id` on
tenant-scoped tables). Retrofitting tenancy later would touch every table,
repository, and route — far more costly than building tenant-aware from day one.

Alternatives considered:

1. **Single-tenant first, retrofit later** — rejected; a later `organization_id`
   migration across all preset and import job tables plus auth rework is high-risk and
   re-opens compliance-sensitive code (CSV mapping integrity, audit, retention).
2. **Separate install per tenant only** — rejected; does not serve agencies and
   duplicates operations; the data model should still be tenant-aware.
3. **Multi-tenant foundation, mirroring NeNe Records** (chosen) — tenant-aware
   schema and middleware from the start; a single install may still run as one
   organization via the `single` resolution mode.

## Decision

NeNe Profile is **multi-tenant from the foundation**, adopting the NeNe Records
architecture.

### Tenancy

- Every tenant-scoped table carries **`organization_id`** (`organization_settings`,
  `mapping_presets`, `mapping_preset_versions`, `import_jobs`, `import_job_errors`,
  `normalized_transactions`, `audit_events`, `users`).
- The **organization** (`organizations` table) is the tenant. Each organization
  is an independent **operator** running bank CSV normalization, with its own
  `organization_settings` (encoding preference, max file size override, and optional
  Clear bearer token for downstream handoff).
- Per-request **organization resolution** runs in middleware before authorization
  (mirroring `OrgResolverMiddleware`). Supported modes: **`single`** (default —
  one organization per install), `path` (`/{org-slug}/…`), `subdomain`, and
  `custom_domain`. The resolved organization id is held in a request-scoped holder
  and **every repository query is org-scoped**.
- Profile issues **no** statutory document numbers. Import provenance is keyed
  per organization by `file_hash` + `import_job_id`.

### Roles and capabilities

A `Role` enum and a `Capability` enum, resolved per route by a capability
resolver and enforced by `CapabilityMiddleware` (mirroring NeNe Records):

| Role | Scope | Capabilities |
| --- | --- | --- |
| **`superadmin`** | Cross-tenant (platform operator) | All, **including `manage_organizations`**. `organization_id` may be `NULL`. |
| **`admin`** | Single organization | All **except** `manage_organizations` — manages the org's **users**, **organization settings** (encoding defaults, Clear bearer token), presets, and import jobs. |
| **`member`** | Single organization | CSV import operator — `manage_presets`, `manage_import_jobs`, `view_import_jobs`. **Cannot** manage users or settings. |
| **`viewer`** | Single organization | Read-only (`view_import_jobs`) — normalized output and job history. Optional; Phase 2+. |

Capabilities (Profile-specific): `manage_organizations`, `manage_users`,
`manage_organization_settings`, `manage_presets`, `manage_import_jobs`,
`view_import_jobs`.

- **Superadmin manages organizations** (`/admin/organizations` — create, list,
  delete tenants).
- **Admin manages users** within the organization (`/admin/users`).
### Compliance interaction

- Each organization's mapping presets, import jobs, and normalized output are
  scoped to that organization; cross-tenant reads are prohibited.
- Immutability and audit apply **per organization**: completed import job artifacts
  and preset version snapshots must not be mutated.

## Consequences

**Benefits**

- Serves agencies/hosting operators and single SMBs from one codebase; `single`
  mode keeps the simple case simple.
- Avoids a high-risk tenancy retrofit across compliance-sensitive billing tables.
- Consistent with the NeNe ecosystem; patterns and reviews transfer.

**Costs**

- Every repository query must be org-scoped — a standing review item for future self-review checklists.
- Auth, org resolution, and RBAC are part of the runtime foundation (Issue #4),
  enlarging it beyond a bare health endpoint.

**Follow-up**

- **PR-B (Issue #4 expanded):** runtime foundation — org resolution + JWT auth +
  RBAC wiring + `GET /health`.
- **PR-C+:** organization CRUD (superadmin) and user CRUD (admin).

## Related

- Reference implementation: NeNe Records `src/Organization/`, `src/Auth/` (Role, Capability, CapabilityResolver, CapabilityMiddleware), `src/Organization/Resolution/`
- Follow-up: Issue #4 (runtime foundation — org resolution + JWT auth + RBAC)
- Supersedes: none
- Superseded by: none
