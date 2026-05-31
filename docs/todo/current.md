# Current TODO

**Phase 0 — Governance** ✅ complete · **Phase 1 — Normalization API** ✅ complete · **Phase 2 — Admin SPA** ✅ mostly complete (visual column mapper pending)

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
- [x] #29 CI（GitHub Actions — backend + frontend 品質ゲート）→ PR #30

## Completed — Phase 2

- [x] #33 フロントエンド基盤 + login 縦スライス → PR #34
- [x] #35 organizations CRUD + DataTable/Pagination/ConfirmDialog 基盤 → PR #36
- [x] #37 users CRUD + Select primitive + 編集フォーム → PR #38
- [x] #39 mapping-presets 一覧/作成/削除 + 定義エディタ → PR #40
- [x] #41 import-jobs 一覧/CSVアップロード/エラー行/エクスポート → PR #42
- [x] #43 organization-settings + audit-logs 画面 → PR #44
- [x] #45 ダッシュボード（最近のジョブ + エラー率）→ PR #46
- [x] #47 mapping-preset 更新（PATCH=新バージョン）+ フォーム共有化 → PR #48

## Completed — テスト強化

- [x] #49 read 系 UseCase ユニットテスト → PR #50
- [x] #51 ExceptionHandler RFC 9457 + Handler 成功パス → PR #52
- [x] #53 Pdo リポジトリ SQLite 統合テスト → PR #54
- [x] #55 PdoUserRepository email 一意制約 Conflict マッピング → PR #56
- [x] #57 entity mapper / authStore / capabilities / api errors / 共通UI UT → PR #58
- [x] #59 Playwright E2E（全機能×全境界、API モック）→ PR #60

## Completed — コンプライアンス強化

- [x] #62 requirements / todo / milestones チェックボックス更新 → PR #64
- [x] #61 ADR 0003 §3 元号変換トランスフォーマー（`date_era`）実装

## Next

- [ ] Docker E2E スモーク（backend + frontend 結合: GET /health → login → preset → import → export）
- [ ] Clear downstream contract を `nene-clear` チームと調整

## Handoff

Clear should reference Profile export (StandardTransaction v1.0) in Phase 2.

Last updated: 2026-05-31
