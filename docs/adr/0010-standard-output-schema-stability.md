# ADR 0010: Standard Output Schema Stability

## Status

accepted

## Context

Clear and third-party tools depend on Profile output. Ad hoc field changes break downstream.

## Decision

1. **`schema_version` `1.0`** is defined in [`output-schema.md`](../explanation/output-schema.md).
2. Required fields cannot be removed without major version bump + ADR.
3. Profile OpenAPI MUST document export endpoints with embedded JSON Schema.
4. Breaking changes require simultaneous Clear adapter update or deprecation window (min 1 release).

## Related

- [`../explanation/output-schema.md`](../explanation/output-schema.md)
