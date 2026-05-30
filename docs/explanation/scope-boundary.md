# Scope Boundary — NeNe Profile vs Siblings

| | **Profile** | **Clear** | **Vault** | **Invoice** |
| --- | --- | --- | --- | --- |
| **Core question** | "What column is the amount?" | "Which invoice did this deposit pay?" | "Where is the vendor PDF?" | "What did we bill?" |
| **Input** | Raw bank CSV | StandardTransaction or CSV | PDF/image | Business data |
| **Output** | StandardTransaction | Reconciliation + dunning | Searchable archive | Issued documents |

Profile sits **upstream of Clear** in the bank pipeline only. No dependency on Vault or Invoice.

Last updated: 2026-05-29
