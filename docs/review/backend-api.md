# バックエンド API 自己レビュー

PHP/API コードの変更に使用する。

ソースポリシー: `docs/development/backend-standards.md`, `docs/development/naming-conventions.md`.

## レイヤーと配置

- [ ] Handler → UseCase → RepositoryInterface → PdoRepository のレイヤー構造を守っている
- [ ] Handler にビジネスロジックが含まれていない（HTTP パース・DTO 構築・UseCase 呼び出しのみ）
- [ ] UseCase が PDO・`$_SERVER`・raw HTTP に直接アクセスしていない
- [ ] SQL が `Pdo*Repository` 以外に記述されていない
- [ ] Handler・UseCase・Repository は適切なドメインフォルダに配置されている（`src/Handlers/` 等のレイヤーフォルダを使っていない）
- [ ] トランスフォームロジックが Handler や Repository に書かれていない

## クラス設計

- [ ] 全 PHP ファイルに `declare(strict_types=1);` がある
- [ ] アプリクラスは `final` かつ適用可能な場合は `readonly`
- [ ] UseCase インターフェースのメソッドは `execute` のみ
- [ ] Input/Output DTO は `final readonly`; raw 配列・PSR-7 を渡していない
- [ ] リポジトリインターフェースのメソッドはドメイン動詞（`findById`、`save`）; SQL 動詞禁止
- [ ] 戻り値型にドメインオブジェクト・プリミティブを使用; raw PDO 結果行を返していない

## マルチテナント

- [ ] 全テナントスコープクエリで `organization_id` によるフィルタリングがある
- [ ] superadmin 以外のクロステナント読み取り・書き込みがない

## HTTP & OpenAPI

- [ ] 新規ルートが `docs/openapi/openapi.yaml` に `operationId` 付きで記述されている
- [ ] 成功・エラーレスポンスシェイプがドキュメント化されている
- [ ] エラーレスポンスが RFC 9457 Problem Details 形式 (`https://nene-profile.dev/problems/…`)
- [ ] Admin 変更ルートに JWT Bearer 認証が設定されている
- [ ] スタックトレース・SQL・内部パスが公開エラーレスポンスに含まれていない

## 命名

- [ ] 識別子が `docs/explanation/terminology.md` と一致する
- [ ] 新しい識別子を追加した場合、同 PR でレジストリを更新している
- [ ] URL パスが kebab-case lowercase
- [ ] JSON プロパティが snake_case

## テスト

- [ ] UseCase テストが DB 不使用（InMemory リポジトリ注入）
- [ ] Repository テストが SQLite in-memory PDO
- [ ] `composer check` 全通過（PHPStan + PHP-CS-Fixer + PHPUnit）
