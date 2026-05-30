# OpenAPI コントラクト自己レビュー

OpenAPI 変更・新規エンドポイント追加時に使用する。

## チェックリスト

- [ ] 全公開ルートが `docs/openapi/openapi.yaml` に記述されている
- [ ] `operationId` が `terminology.md` の登録済み値と一致する
- [ ] `operationId` がリリース済みの場合はリネームしていない
- [ ] リクエストボディ・パスパラメータ・クエリパラメータのスキーマが定義されている
- [ ] 成功レスポンスシェイプが定義されている
- [ ] Problem Details エラーレスポンスが定義されている (4xx/5xx)
- [ ] スキーマ名が命名規則に従っている (`{Resource}Response`, `Create{Resource}Request`)
- [ ] JSON プロパティが snake_case
- [ ] 金額フィールドが integer (`amount_cents` 等)、float 禁止
- [ ] タイムスタンプフィールドが `_at` サフィックス + ISO 8601 文字列
- [ ] OpenAPI サマリー・説明・例が English
- [ ] `composer openapi` が通過する

## MCP 連動

- [ ] MCP に公開するオペレーションが `docs/mcp/tools.json` に記述されている
- [ ] MCP `name` が OpenAPI `operationId` と一致する
- [ ] `composer mcp` が通過する
