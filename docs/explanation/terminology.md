# Terminology Registry

**Status: binding.** 全識別子の正規スペリングの唯一の正解。

コード・API・DB・テスト・ドキュメント内の識別子はここに登録された文字列と完全一致しなければならない。
登録済み用語のタイポ・スペルバリエーションは不具合としてマージブロックの対象。

識別子の追加・リネームは必ず同 PR でこのファイルを更新すること。
意味・定義は [`glossary.md`](./glossary.md) を参照。

---

## エンティティ（テーブル名 → クラス名）

| Table (snake_case plural) | PHP Class (PascalCase singular) |
|---|---|
| `organizations` | `Organization` |
| `users` | `User` |
| `organization_settings` | `OrganizationSettings` |
| `mapping_presets` | `MappingPreset` |
| `mapping_preset_versions` | `MappingPresetVersion` |
| `import_jobs` | `ImportJob` |
| `import_job_errors` | `ImportJobError` |
| `normalized_transactions` | `NormalizedTransaction` |
| `audit_logs` | `AuditLog` |

---

## インポートジョブステータス値

| 値 | 意味 |
|---|---|
| `pending` | アップロード受付済み; ワーカー待ち |
| `running` | 行処理中 |
| `completed` | 全行処理完了; エラーゼロ |
| `completed_with_errors` | 全行処理試行済み; 1行以上エラー |
| `failed` | ジョブ中断 (ファイル読み取り不可、システムエラー等) |

---

## ロール値

| 値 | 意味 |
|---|---|
| `superadmin` | クロステナント; `organization_id` NULL 可 |
| `admin` | 単一組織; 管理者 |
| `member` | 単一組織; CSV インポートオペレーター |
| `viewer` | 単一組織; 読み取り専用 (Phase 2+) |

---

## ケイパビリティ値

| 値 | 保有ロール |
|---|---|
| `manage_organizations` | superadmin のみ |
| `manage_users` | superadmin, admin |
| `manage_organization_settings` | superadmin, admin |
| `manage_presets` | superadmin, admin, member |
| `manage_import_jobs` | superadmin, admin, member |
| `view_import_jobs` | superadmin, admin, member, viewer |

---

## 組み込みトランスフォーマー ID

| ID | 動作 |
|---|---|
| `trim` | Unicode 空白トリム |
| `date_ymd_slash` | `YYYY/MM/DD`、`YY/MM/DD` → ISO 8601 |
| `date_ymd_dash` | `YYYY-MM-DD` → ISO 8601 (正規化) |
| `date_ymd_compact` | `YYYYMMDD` → ISO 8601 |
| `amount_yen_to_cents` | 円文字列 → integer cents (float 禁止) |
| `debit_credit_to_signed_cents` | 入金/出金 2 列 → signed integer |
| `single_column_signed_cents` | 1 列 (DR/CR サフィックスまたは符号) → signed integer |
| `regex_extract` | パラメータ: `pattern`, `group` |

---

## StandardTransaction フィールド名

| フィールド | 型 | 必須 |
|---|---|---|
| `schema_version` | string `"1.0"` | yes |
| `transaction_date` | string `YYYY-MM-DD` | yes |
| `value_date` | string `YYYY-MM-DD` | yes |
| `amount_cents` | integer | yes |
| `description` | string (max 500) | yes |
| `counterparty` | string (max 200) | no |
| `balance_cents` | integer | no |
| `currency` | string `"JPY"` | yes |
| `raw_row_number` | integer | yes |
| `import_job_id` | string (ULID) | yes |
| `preset_version_id` | string (ULID) | yes |
| `line_hash` | string `sha256:…` | yes |

---

## API パスとオペレーション ID

| operationId | Method | Path |
|---|---|---|
| `getHealth` | GET | `/health` |
| `listOrganizations` | GET | `/admin/organizations` |
| `createOrganization` | POST | `/admin/organizations` |
| `getOrganizationById` | GET | `/admin/organizations/{id}` |
| `updateOrganization` | PATCH | `/admin/organizations/{id}` |
| `deleteOrganization` | DELETE | `/admin/organizations/{id}` |
| `listUsers` | GET | `/admin/users` |
| `createUser` | POST | `/admin/users` |
| `getUserById` | GET | `/admin/users/{id}` |
| `updateUser` | PATCH | `/admin/users/{id}` |
| `deleteUser` | DELETE | `/admin/users/{id}` |
| `getOrganizationSettings` | GET | `/admin/organization-settings` |
| `updateOrganizationSettings` | PATCH | `/admin/organization-settings` |
| `listMappingPresets` | GET | `/admin/mapping-presets` |
| `createMappingPreset` | POST | `/admin/mapping-presets` |
| `getMappingPresetById` | GET | `/admin/mapping-presets/{id}` |
| `updateMappingPreset` | PATCH | `/admin/mapping-presets/{id}` |
| `deleteMappingPreset` | DELETE | `/admin/mapping-presets/{id}` |
| `createImportJob` | POST | `/admin/import-jobs` |
| `listImportJobs` | GET | `/admin/import-jobs` |
| `getImportJobById` | GET | `/admin/import-jobs/{id}` |
| `listImportJobErrors` | GET | `/admin/import-jobs/{id}/errors` |
| `exportImportJobJson` | GET | `/admin/import-jobs/{id}/export.json` |
| `exportImportJobCsv` | GET | `/admin/import-jobs/{id}/export.csv` |

MCP ツール: `listMappingPresets`, `runProfileImport` (Phase 3+).

---

## Problem Details スラグ

| スラグ | HTTP Status | 意味 |
|---|---|---|
| `validation-failed` | 422 | リクエストバリデーションエラー |
| `mapping-preset-not-found` | 404 | プリセット未発見 |
| `import-job-not-found` | 404 | インポートジョブ未発見 |
| `import-job-already-running` | 409 | ジョブが既に実行中 |
| `preset-version-frozen` | 409 | 完了ジョブ参照済みバージョンへの変更 |
| `file-too-large` | 413 | ファイルサイズ上限超過 |
| `unsupported-encoding` | 422 | 検出・変換不能なエンコーディング |
| `organization-not-found` | 404 | 組織未発見 |
| `user-not-found` | 404 | ユーザー未発見 |
| `forbidden` | 403 | ケイパビリティ不足 |
| `unauthenticated` | 401 | JWT 未提供または無効 |

Last updated: 2026-05-30
