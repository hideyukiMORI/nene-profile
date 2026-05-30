# ADR 0002: Separate Product from Sibling NeNe Applications

## Status

accepted

## Context

NeNe Profile is a **CSV normalization utility**. Siblings own reconciliation,
documents, and billing.

## Decision

- Independent repository and deployable unit.
- Dependency direction: `NeNe Clear → NeNe Profile API` (optional). Never embed Profile in Clear repo.
- No shared database. Handoff via HTTP or exported file.
- MCP tools map to Profile OpenAPI only.

## Related

- ADR 0009
- [`../integrations/clear-downstream-contract.md`](../integrations/clear-downstream-contract.md)
