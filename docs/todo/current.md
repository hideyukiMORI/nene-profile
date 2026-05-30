# Current TODO

**Phase 0 — Governance** ✅ complete · **Phase 1 — Normalization API** 実装中

## Completed — Phase 0

- [x] #1 Governance bootstrap → PR #2
- [x] #3 Public repository visibility + ADR 0011 → PR #4
- [x] #5 コーディング標準・NENE2 継承 → PR #6
- [x] #7 terminology.md 唯一の正解化 → PR #8

## Completed — Phase 1

- [x] #9 マルチテナント基盤（Organization / Auth / OrgResolver / GET /health）→ PR #10
- [x] #11 監査ログ基盤（全 mutation の前後スナップショット）→ PR #12
- [x] #13 i18n メッセージカタログ基盤（frontend）→ PR #14
- [x] #15 OpenAPI 3.1 契約 + 検証ツール → PR #16
- [x] #17 User CRUD（組織スコープ + 監査）→ PR #18
- [x] #19 OrganizationSettings → PR #20
- [x] #21 MappingPreset CRUD + 不変バージョニング → PR #22
- [x] #23 トランスフォーマーエンジン（ADR 0003 コア）→ PR #24
- [x] #25 ImportJob ライフサイクル・正規化・エクスポート → PR #26

**Phase 1 API は機能的に完成**（全 OpenAPI エンドポイント実装済み）。

## Next

- [ ] CI（GitHub Actions で composer check + frontend check）
- [ ] Docker での E2E スモーク（GET /health → login → preset → import → export）
- [ ] frontend: i18n に続く API クライアント・画面実装
- [ ] ADR 0003 §3 元号変換トランスフォーマー（terminology §7 追加 + 実装）
- [ ] Clear downstream contract を `nene-clear` チームと調整

## Handoff

Clear should reference Profile export (StandardTransaction v1.0) in Phase 2.

Last updated: 2026-05-30
