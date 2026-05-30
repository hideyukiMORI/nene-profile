# ADR 0011: Bilingual JP/EN Documentation — No Other Languages

## Status

accepted

## Context

ADR 0008 mandated English-only repository documentation to ensure clarity for
international contributors, AI agents, and accounting advisors.

Two trends have since emerged:

1. **Overseas professionals working in Japan** increasingly adopt Japan-specific
   OSS tools. They can read English repository documentation; requiring
   Japanese-native maintainers to write exclusively in English imposes friction
   without meaningful benefit for this audience.
2. **Expanding beyond JP/EN would misalign with the product domain.** NeNe
   Profile is built on Japanese bank CSV formats, Japanese accounting practices
   (消費税, 電子帳簿保存法), and Japan-specific preset definitions. Adding
   Chinese, Korean, or other localizations would imply a geographic scope the
   product cannot fulfill — Japanese bank presets and regulatory references
   would actively mislead non-Japan operators.

Alternatives considered:

1. **Keep English-only (ADR 0008)** — rejected; excludes Japanese-native
   maintainers from writing in their strongest language without a compensating
   benefit.
2. **Full multi-language (JP/EN/ZH/KO/…)** — rejected; creates a false
   impression of domain scope. The product is inherently Japan-specific.
3. **JP/EN bilingual only** (chosen) — matches the product's target operators:
   Japan-registered businesses run by Japanese staff or by overseas professionals
   working in Japan.

## Decision

NeNe Profile documentation and contribution language is **Japanese and English
only**. No other languages are in scope.

### Repository documentation

`README.md`, `AGENTS.md`, `CLAUDE.md`, everything under `docs/`, `.cursor/rules/`,
and OpenAPI descriptions may be written in **Japanese, English, or both**.
No requirement to duplicate content in both languages — write in whichever
language is clearest for the intended reader.

### Commits, Issues, PR titles and bodies

**Japanese or English** accepted. Mixing within a single message is acceptable
when a term is clearer in one language (e.g. a Japanese law name inside an
English sentence).

### Admin UI locale catalogs

**Japanese (primary) + English (secondary).** No other locales are added.

### Operator-facing install guides

Japanese (primary) + English (secondary), consistent with ADR 0007.

### Explicit language boundary

> **No language beyond Japanese and English is in scope.**
> NeNe Profile is built for Japan-registered businesses operating under Japanese
> tax law and Japanese bank CSV formats. Supporting other languages would imply
> a regulatory and geographic scope the product does not fulfill.

### Japanese law terms

Japanese statutory names and field labels may appear inline where needed for
operator clarity or advisor traceability (e.g., 電子帳簿保存法, 消費税).

## Consequences

**Benefits**

- Japanese-native maintainers can write in their strongest language without
  violating workflow rules.
- English documentation remains fully available for overseas contributors and
  AI agents.
- Explicit two-language cap prevents scope creep toward non-Japan accounting.

**Costs**

- Some documents may be written in Japanese only, reducing legibility for
  non-Japanese AI agents and reviewers. For docs aimed at international
  audiences (ADRs, integration contracts), English or bilingual content is
  preferred.

## Related

- Supersedes: [ADR 0008](./0008-english-only-repository-documentation.md)
- ADR 0007: product identity — tagline exists in JP + EN
- ADR 0009: domain boundary (Japan-specific scope)
