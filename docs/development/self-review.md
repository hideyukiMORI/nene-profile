# PR 自己レビュー手順

プッシュまたは PR 作成前に実施する。
[NENE2 self-review.md](https://github.com/hideyukiMORI/NENE2/blob/main/docs/development/self-review.md) から継承。

---

## 共通チェック（全 PR）

- [ ] 関連 GitHub Issue が存在し、ブランチ名が `type/issue-number-summary` 形式
- [ ] `docs/roadmap.md`・`docs/milestones/`・`docs/todo/current.md` を事前確認済み
- [ ] `main` への直接コミットなし
- [ ] コミットメッセージが Conventional Commits 形式 + `(#issue)` 含む
- [ ] `composer check` 全通過（PHPStan + PHP-CS-Fixer + PHPUnit）
- [ ] コンパイルエラー・未解決の TODO・デバッグログがない
- [ ] シークレット・トークン・`.env` ファイルが含まれていない
- [ ] 命名規則 (`naming-conventions.md`) 遵守・用語レジストリ (`terminology.md`) 更新済み（追加・変更がある場合）

---

## 変更種別別チェック

変更内容に該当するチェックリストを使用:

| 変更内容 | 使用するチェックリスト |
|---|---|
| トランスフォーマー・インポートジョブ・プロベナンス・エクスポート | [`../review/compliance.md`](../review/compliance.md) |
| PHP/API コード | [`../review/backend-api.md`](../review/backend-api.md) |
| DB スキーマ・マイグレーション・リポジトリ | [`../review/database.md`](../review/database.md) |
| 認証・ミドルウェア・テナント分離 | [`../review/middleware-security.md`](../review/middleware-security.md) |
| OpenAPI 変更 | [`../review/openapi-contract.md`](../review/openapi-contract.md) |
| フロントエンド (Phase 2+) | `../review/frontend.md` |

---

## PR 説明に含める項目

```
## 目的
この PR が解決する Issue と理由

## 変更サマリー
主な変更点を箇条書き

## 検証結果
- composer check: pass
- 手動検証: (実行したこと)

## コンプライアンスへの影響
該当なし / (影響がある場合は説明)

## 使用した自己レビューチェックリスト
- [ ] compliance.md
- [ ] backend-api.md
- ...

## 残課題・フォローアップ
```

`Closes #issue` を PR 説明に含める。
