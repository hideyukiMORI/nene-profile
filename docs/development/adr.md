# ADR ポリシー

NeNe Profile は Architecture Decision Records (ADR) を `docs/adr/` に記録する。
[NENE2 adr.md](https://github.com/hideyukiMORI/NENE2/blob/main/docs/development/adr.md) から継承。

## ADR を書くタイミング

以下の変更には ADR が必要:

- 公開 API コントラクト（StandardTransaction スキーマ、OpenAPI）の変更
- 依存パッケージの追加・変更
- レイヤー構造・モジュールレイアウトの変更
- コンプライアンス binding ルール (`accounting-compliance.md`) からの逸脱
- マルチテナント・認証・権限モデルの変更
- トランスフォーマー拡張ポイント（Phase 3+）の設計変更
- データ保持・削除ポリシーの変更
- 永続化戦略の変更

## ファイル名規則

```
docs/adr/NNNN-kebab-case-title.md
```

例: `docs/adr/0003-transform-fidelity.md`

番号は連番。次の未使用番号を割り当てる。

## テンプレート

`docs/adr/0000-template.md` を使用する。

```markdown
# ADR NNNN: タイトル

## Status
accepted / rejected / superseded by [ADR XXXX]

## Context
背景と問題。

## Decision
決定内容。

## Consequences
**Benefits** / **Costs** / **Follow-up**

## Related
- 関連 ADR、ドキュメント、Issue
```

## 現在の ADR 一覧

| ADR | 内容 | Status |
|---|---|---|
| 0001 | NENE2 ガバナンス継承 | accepted |
| 0002 | 姉妹製品からの分離 | accepted |
| 0003 | トランスフォーム整合性ルール | accepted |
| 0004 | 原本不変性とインポートプロベナンス | accepted |
| 0006 | マルチテナンシーとロール階層 | accepted |
| 0007 | プロダクトアイデンティティ | accepted |
| 0008 | English-only ドキュメント | superseded by 0011 |
| 0009 | NeNe Clear からの分離 | accepted |
| 0010 | 出力スキーマ安定性 | accepted |
| 0011 | 日英バイリンガル方針 | accepted |
