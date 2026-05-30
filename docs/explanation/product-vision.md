# Product Vision

**NeNe Profile** — bank CSV normalization for Japan SMB on NENE2.

## Origin

Every bank exports CSV differently. Operators paste into Excel, manually delete
preamble rows, and reformat dates — every month. Mega-SaaS tools hide this behind
closed presets operators cannot fix when the bank changes a column name.

Clear needs normalized lines; **Clear should not own a preset engine**.

## North Star

- Upload any bank CSV
- Pick or create a **mapping preset**
- Download **StandardTransaction** JSON/CSV
- Clear (or Excel) consumes output without knowing the original bank format

## Non-goals

- Invoice matching, dunning, document storage, accounting

## Success criteria (MVP)

- One preset (MUFG or SMBC) + custom mapping UI
- 1000-row import with row errors reported
- Export validates against output schema v1.0
- Clear team can ingest export per downstream contract

## Related

- [`requirements.md`](./requirements.md)
- [`scope-contract.md`](./scope-contract.md)

Last updated: 2026-05-29
