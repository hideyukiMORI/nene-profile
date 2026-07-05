# ADR 0012: Adopt `Nene2\Audit\*` for the Audit Trail

## Status

accepted

## Context

ADR 0005 introduced a hand-rolled audit trail (`Audit\AuditLog`, `AuditRecorder`,
`AuditRecorderInterface`, `PdoAuditLogRepository`) with two known weaknesses,
both flagged in ADR 0005 itself:

1. **Timestamp drift**: `PdoAuditLogRepository::append()` called `date()`
   directly instead of using an injected clock, so audit timestamps could not
   be made deterministic in tests and were not guaranteed to match the "now"
   used elsewhere in a request.
2. **Non-atomic recording** (ADR 0005 "Follow-up"): "Wrap mutation + audit in
   one DB transaction when `DatabaseTransactionManagerInterface` is introduced
   to use cases." `DatabaseTransactionManagerInterface` has been registered in
   `RuntimeServiceProvider` since profile's inception but no use case actually
   used it — the audit row and the business mutation were separate,
   non-transactional writes, so a crash between them could drop one entry.

NENE2 shipped a framework audit module (`Nene2\Audit\*`) that generalizes the
"transaction-atomic recorder bound to the executor of the enclosing
transaction" pattern already proven in NeNe Payout (pilot: Payout PR #145).
Adopting it against profile's **existing** `audit_logs` table (no
re-migration) fixes both issues at once.

## Decision

Replace the hand-rolled `NeneProfile\Audit\*` write/record path with the
framework's `Nene2\Audit\AuditRecorderFactory` + `Nene2\Audit\AuditEvent`, and
the read path with `Nene2\Audit\AuditEventRepositoryInterface` +
`Nene2\Audit\AuditQuery`.

### Table mapping (`AuditTableConfig`, in `AuditServiceProvider`)

| Config field | Value |
|---|---|
| `table` | `audit_logs` (existing table, unchanged) |
| `mode` | `AuditPayloadMode::BeforeAfter` |
| `idColumn` / `idIsAutoIncrement` | `id` / `true` (int autoincrement, unlike Payout's ULID) |
| `actionColumn` | `action` |
| `entityTypeColumn` / `entityIdColumn` | `entity_type` / `entity_id` |
| `actorColumn` | `actor_user_id` |
| `organizationColumn` | `organization_id` |
| `occurredAtColumn` | `created_at` |
| `beforeColumn` / `afterColumn` | `before_json` / `after_json` |
| `metadataColumn` | `null` (the table has no metadata column) |

Action strings (`organization.created`, `user.updated`, …) are unchanged and
remain product-owned, per framework design.

### Write side

Every mutating use case now takes a `DatabaseTransactionManagerInterface`, a
`Closure(DatabaseQueryExecutorInterface): <Repo>Interface` factory for its
repository (or repositories), and an `AuditRecorderFactoryInterface`. The
mutation and the audit write both run inside one
`$tx->transactional(function (DatabaseQueryExecutorInterface $exec) { … })`
closure via `$this->auditFactory->forExecutor($exec)->record(new AuditEvent(...))`,
so they commit or roll back together — this is the direct fulfillment of the
ADR 0005 follow-up.

`CreateImportJobUseCase` is the one exception: steps 1-3 (resolve preset,
store the original file + hash, parse/normalize) intentionally stay outside any
transaction, per ADR 0004 — the original file and the initial "running" job row
must be durable *before* the (potentially slow) parse step starts, so a crash
mid-parse still leaves a recoverable job. Only the terminal write (row/error
persistence + status flip) and the audit record are transactional; the
parse-failure branch was never audited before this change and still isn't
(unchanged behavior).

No `RequestScopedHolder` is registered on the recorder factory: every profile
use case already passes `organizationId` explicitly on the `AuditEvent`
(including `null` for superadmin cross-org actions), so the framework's
optional tenant-fallback is unused — same reasoning as Payout's
`AuditServiceProvider`.

### Read side

`ListAuditLogsUseCase` now depends on `Nene2\Audit\AuditEventRepositoryInterface`
directly and builds an `AuditQuery` from `ListAuditLogsInput::$organizationId`
(`null` → superadmin cross-org view, matching the pre-adoption behavior of
`findAll()`/`countAll()`). `ListAuditLogsHandler`'s JSON response shape is
byte-for-byte unchanged (`id`, `actor_user_id`, `organization_id`, `action`,
`entity_type`, `entity_id`, `before`, `after`, `created_at`); profile has no
actor-email join or CSV export to preserve, unlike Payout.

### Removed

`Audit\AuditLog`, `Audit\AuditLogRepositoryInterface`, `Audit\AuditRecorder`,
`Audit\AuditRecorderInterface`, `Audit\PdoAuditLogRepository`, and their
dedicated tests (`AuditRecorderTest`, `PdoAuditLogRepositorySqliteTest`,
`InMemoryAuditLogRepository`) — superseded by the framework types and a thin
`AuditTableConfigSqliteTest` that checks profile's config against the real
schema (the framework repository's own SQL logic is tested upstream in NENE2).

## Consequences

**Benefits**

- Audit timestamps come from the same injected `Nene2\Http\ClockInterface` as
  the rest of the request (`UtcClock` in production, `FixedClock` in tests) —
  no more `date()` drift.
- Audit row and business mutation commit or roll back atomically for every
  mutating use case except the CSV-parse phase of import jobs (by design, see
  above).
- Deletes ~5 hand-rolled classes and their tests in favor of a framework
  module shared with other NeNe products.

**Costs**

- Every mutating use case's constructor grew (transaction manager + repository
  factory closure + audit recorder factory) — more wiring in
  `*ServiceProvider`s, though mechanically so.

## Related

- ADR 0005 (superseded write/read mechanism; audit table schema, action naming,
  and sanitization rules are unchanged and still apply)
- ADR 0004 (original file immutability — constrains `CreateImportJobUseCase`'s
  transaction boundary)
- NENE2 `Nene2\Audit\*` (#1495), pilot: NeNe Payout PR #145
