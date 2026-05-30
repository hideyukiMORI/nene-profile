# コミットメッセージ規約

NeNe Profile は Conventional Commits を使用する。
[NENE2](https://github.com/hideyukiMORI/NENE2/blob/main/docs/development/commit-conventions.md)
と姉妹 NeNe プロダクトから継承。

## フォーマット

```text
<type>(<optional scope>): <description> (#<issue>)

[optional body]

[optional footer]
```

## 言語 (ADR 0011)

- `type`, `scope`, `BREAKING CHANGE` などの Conventional Commits キーワードは **English**
- description と body は **日本語または English** (ADR 0011; どちらも可)
- 全ての作業で Issue 番号を subject に含めること

例:

```text
docs(standards): NENE2 コーディング規約を nene-profile 用に整備する (#5)
```

```text
feat(preset): マッピングプリセット CRUD UseCase を追加する (#7)
```

```text
fix(transformer): 2桁年のピボット年デフォルトが適用されない不具合を修正する (#11)
```

## Issue 番号

| 状況 | ルール |
|---|---|
| 通常の作業 | Subject に `(#issue)` を **必ず** 含める |
| 同 Issue の docs-only フォローアップ | 同じ Issue 番号を再利用 |

Issue なしで編集を始めた場合は **先に Issue を作成する** — `docs/workflow.md` 参照。

## タイプ一覧

| Type | 用途 |
|---|---|
| `feat` | 新機能 |
| `fix` | バグ修正 |
| `docs` | ドキュメントのみの変更 |
| `refactor` | 機能追加・バグ修正を伴わないコード変更 |
| `test` | テストの追加・変更 |
| `build` | 依存関係・ビルド設定 |
| `ci` | CI 設定 |
| `chore` | メンテナンス |

## Body

理由が subject から明らかでない場合に body を使う。
変更が存在する理由・選択したトレードオフ・残っているフォローアップ作業を説明する。

## Breaking Changes

公開 API・設定・CLI・ドキュメント化された動作が非互換に変わる場合は
`!` または `BREAKING CHANGE:` フッターを使う。

公開 API 変更時は OpenAPI と `docs/mcp/tools.json` も同時に更新する。
