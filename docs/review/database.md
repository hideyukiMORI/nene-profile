# データベース自己レビュー

マイグレーション・リポジトリ・スキーマ変更に使用する。

ソースポリシー: `docs/development/backend-standards.md`, `docs/development/naming-conventions.md`.

## チェックリスト

- [ ] マイグレーションファイル名が `YYYYMMDDHHMMSS_snake_description.php` 形式
- [ ] テーブル名が snake_case **複数形**; 金額カラムが `*_cents` サフィックス (integer)
- [ ] 外部キーカラム名が `{singular_entity}_id`
- [ ] SQL が `Pdo*Repository` クラス内にのみ記述されている
- [ ] スキーマスナップショットが `database/schema/` に更新されている（該当する場合）
- [ ] ソフトデリートカラムが `is_deleted`, `deleted_at` (ADR 別指定がない限り)
- [ ] テナントスコープテーブルに `organization_id` カラムがある
- [ ] リポジトリテストが SQLite in-memory PDO を使用している

## 不変テーブル（変更禁止）

以下のテーブルの完了済みレコードに UPDATE を発行していないこと:

- [ ] `mapping_preset_versions` (完了ジョブ参照済み行)
- [ ] `import_jobs` (terminal ステータス到達後)
- [ ] `import_job_errors` (write-once)
- [ ] `normalized_transactions` (write-once)
- [ ] `audit_logs` (append-only)

## ロールバック

- [ ] 破壊的変更をロールバック可能か検討した（不可能な場合は PR に理由を記載）
- [ ] データ変更はスキーマ変更と分離できている（実行可能な場合）
