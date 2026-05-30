# Terminology Registry — 識別子の唯一の正解

**Status: binding (non-negotiable).** このファイルは NeNe Profile における
すべての識別子の **唯一の正解（Single Source of Truth）** である。

コード・API・DB・テスト・ドキュメント・コミットメッセージ内のいかなる識別子も、
ここに登録された文字列と完全一致しなければならない。

> **ゼロ許容（Zero Tolerance）**
>
> - 登録済み用語のタイポ・スペルバリエーション・略称は **不具合** であり **マージブロック** の対象
> - 「ほぼ合っている」「よく似ている」は存在しない
> - 疑わしいときは必ずここを確認してから書く
> - 新しい識別子はここに登録してから使う

用語の **意味・定義** は [`glossary.md`](./glossary.md) を参照。
命名 **パターン・ルール** は [`../development/naming-conventions.md`](../development/naming-conventions.md) を参照。

---

## このファイルの使い方

### 識別子を書く前に

1. このファイルで正規スペリングを検索する
2. 見つかった → その文字列を使う（変えない）
3. 見つからない → §「新規識別子の登録手順」に従って追加してから使う

### 新規識別子の登録手順

1. 追加したい識別子を決める
2. **同じ PR** でこのファイルの適切なセクションに行を追加する
3. 意味・定義が必要なら `glossary.md` も同 PR で更新する
4. PR の説明に「terminology.md を更新した」旨を記載する

識別子なしで先に実装してから後でレジストリを更新することは禁止。

---

## §1 ドメインモジュール（`src/` フォルダ名）

PHP ネームスペースセグメントおよび `src/` 直下のフォルダ名。

| 正規名 | 禁止形式（使ってはいけない） | 役割 |
|---|---|---|
| `Organization` | `Org`, `Tenant`, `Organizations` | テナント + per-request 解決 |
| `Auth` | `Authentication`, `Login`, `Security` | JWT 認証・Role/Capability |
| `User` | `Users`, `Account`, `Member` | オペレーターアカウント |
| `OrgSettings` | `Settings`, `OrganizationSettings`, `OrgSetting`, `Config` | 組織設定 CRUD |
| `Preset` | `MappingPreset`, `Presets`, `Mapping` | mapping_preset + version |
| `ImportJob` | `Job`, `Jobs`, `Import`, `Importer`, `BatchJob` | インポートジョブライフサイクル |
| `Transformer` | `Transformers`, `Transform`, `Converter` | 組み込みトランスフォーマー |
| `Export` | `Exports`, `Output`, `Exporter` | StandardTransaction 出力 |
| `Audit` | `Audits`, `Logging`, `Log` | AuditRecorder・audit_log |
| `Http` | `HTTP`, `Routing`, `Router`, `Controller` | ルーティング・ブートストラップ |
| `Upstream` | `Downstream`, `Client`, `Integration` | 外部 HTTP クライアント (Phase 3+) |

---

## §2 エンティティ（DB テーブル名 → PHP クラス名）

| DB Table (snake_case plural) | PHP Class (PascalCase singular) | 禁止形式 |
|---|---|---|
| `organizations` | `Organization` | `Org`, `Tenant`, `Organization` (複数形) |
| `users` | `User` | `Account`, `Operator`, `Users` |
| `organization_settings` | `OrganizationSettings` | `OrgSettings`, `Settings`, `OrganizationSetting` |
| `mapping_presets` | `MappingPreset` | `Preset`, `MappingProfile`, `MappingTemplate` |
| `mapping_preset_versions` | `MappingPresetVersion` | `PresetVersion`, `MappingPresetRevision` |
| `import_jobs` | `ImportJob` | `Job`, `BatchJob`, `CsvJob`, `ImportBatch` |
| `import_job_errors` | `ImportJobError` | `JobError`, `ImportError`, `RowError` |
| `normalized_transactions` | `NormalizedTransaction` | `StandardTransaction` (PHP クラス名としては使わない), `Transaction`, `NormalizedRow` |
| `audit_logs` | `AuditLog` | `Audit`, `Log`, `AuditRecord` |

> **注意:** `StandardTransaction` はアウトプットスキーマの概念名であり、PHP クラス名として
> `src/` 内で使う場合は `NormalizedTransaction` を使う。
> JSON/OpenAPI では `StandardTransaction` 相当のフィールド群として定義する。

---

## §3 PHP キークラス名パターン

以下のサフィックス規則に従い命名する。命名規則の詳細は `naming-conventions.md` §1 参照。

| サフィックス | 例（Preset ドメイン） | 禁止形式 |
|---|---|---|
| `Handler` | `CreateMappingPresetHandler` | `CreateMappingPresetController`, `PresetController` |
| `UseCaseInterface` | `CreateMappingPresetUseCaseInterface` | `ICreateMappingPresetUseCase`, `CreatePresetUseCase` (Interface なし) |
| `UseCase` | `CreateMappingPresetUseCase` | `CreateMappingPresetService`, `PresetCreator` |
| `Input` | `CreateMappingPresetInput` | `CreateMappingPresetDto`, `CreateMappingPresetRequest` (PHP DTO として) |
| `Output` | `CreateMappingPresetOutput` | `CreateMappingPresetResult`, `CreateMappingPresetResponse` (PHP DTO として) |
| `RepositoryInterface` | `MappingPresetRepositoryInterface` | `IMappingPresetRepository`, `MappingPresetRepo` |
| `PdoRepository` (prefix) | `PdoMappingPresetRepository` | `MappingPresetPdoRepository`, `MappingPresetRepository` (Pdo なし) |
| `NotFoundException` | `MappingPresetNotFoundException` | `PresetNotFoundException`, `MappingPresetNotFound` |
| `ServiceProvider` | `PresetServiceProvider` | `PresetProvider`, `MappingPresetServiceProvider` |
| `Transformer` (suffix) | `DateYmdSlashTransformer` | `DateYmdSlashConverter`, `YmdSlashTransform` |

---

## §4 インポートジョブステータス値

| 値 | 禁止形式 | 意味 |
|---|---|---|
| `pending` | `queued`, `waiting`, `scheduled` | アップロード受付済み; ワーカー待ち |
| `running` | `processing`, `in_progress`, `active` | 行処理中 |
| `completed` | `done`, `success`, `finished` | 全行処理完了; エラーゼロ |
| `completed_with_errors` | `partial`, `partial_success`, `done_with_errors`, `completedWithErrors` | 全行処理試行済み; 1行以上エラー |
| `failed` | `error`, `aborted`, `cancelled` | ジョブ中断 |

---

## §5 ロール値

| 値 | 禁止形式 | 意味 |
|---|---|---|
| `superadmin` | `super_admin`, `superAdmin`, `platform_admin`, `owner` | クロステナント |
| `admin` | `administrator`, `Admin` | 単一組織管理者 |
| `member` | `operator`, `user`, `staff` | CSV インポートオペレーター |
| `viewer` | `readonly`, `read_only`, `guest` | 読み取り専用 (Phase 2+) |

---

## §6 ケイパビリティ値

| 値 | 禁止形式 | 保有ロール |
|---|---|---|
| `manage_organizations` | `manageOrganizations`, `admin_organizations` | superadmin のみ |
| `manage_users` | `manageUsers`, `admin_users` | superadmin, admin |
| `manage_organization_settings` | `manage_settings`, `manageSettings` | superadmin, admin |
| `manage_presets` | `manage_mapping_presets`, `managePresets` | superadmin, admin, member |
| `manage_import_jobs` | `manage_jobs`, `manageImportJobs` | superadmin, admin, member |
| `view_import_jobs` | `view_jobs`, `read_import_jobs`, `viewImportJobs` | superadmin, admin, member, viewer |

---

## §7 組み込みトランスフォーマー ID

| ID (正規形) | 禁止形式 | 動作 |
|---|---|---|
| `trim` | `strip`, `strip_whitespace` | Unicode 空白トリム |
| `date_ymd_slash` | `dateYmdSlash`, `ymd_slash`, `date_slash` | `YYYY/MM/DD` → ISO 8601 |
| `date_ymd_dash` | `dateYmdDash`, `ymd_dash`, `date_dash` | `YYYY-MM-DD` → ISO 8601 |
| `date_ymd_compact` | `dateYmdCompact`, `ymd_compact`, `yyyymmdd` | `YYYYMMDD` → ISO 8601 |
| `amount_yen_to_cents` | `amountYenToCents`, `yen_to_cents`, `to_cents` | 円文字列 → integer cents |
| `debit_credit_to_signed_cents` | `debitCreditToSignedCents`, `two_column_amount` | 入金/出金 2列 → signed integer |
| `single_column_signed_cents` | `singleColumnSignedCents`, `one_column_amount` | 1列符号付き → signed integer |
| `regex_extract` | `regexExtract`, `regex`, `extract` | 正規表現抽出 |

---

## §8 StandardTransaction 出力フィールド名

JSON/CSV エクスポートおよび `normalized_transactions` テーブルのカラム名。

| フィールド名 (正規形) | 禁止形式 | 型・備考 |
|---|---|---|
| `schema_version` | `schemaVersion`, `version`, `schema` | string `"1.0"` |
| `transaction_date` | `transactionDate`, `date`, `txn_date` | string `YYYY-MM-DD` |
| `value_date` | `valueDate`, `settlement_date`, `value` | string `YYYY-MM-DD` |
| `amount_cents` | `amountCents`, `amount`, `value_cents`, `amount_jpy` | integer; 入金正/出金負 |
| `description` | `memo`, `note`, `text`, `narration` | string (max 500) |
| `counterparty` | `counter_party`, `counterParty`, `partner`, `recipient` | string (max 200) |
| `balance_cents` | `balanceCents`, `balance`, `running_balance` | integer (optional) |
| `currency` | `currency_code`, `currencyCode`, `ccy` | string `"JPY"` (Phase 1–3 固定) |
| `raw_row_number` | `rawRowNumber`, `row_number`, `row_num`, `line_number` | integer; 1始まり |
| `import_job_id` | `importJobId`, `job_id`, `batch_id` | string ULID |
| `preset_version_id` | `presetVersionId`, `version_id`, `preset_id` | string ULID |
| `line_hash` | `lineHash`, `hash`, `row_hash`, `content_hash` | string `sha256:…` |

---

## §9 API パスと operationId

### パス（URL セグメント）

| 正規パス | 禁止形式 |
|---|---|
| `/health` | `/healthcheck`, `/status`, `/ping` |
| `/admin/organizations` | `/admin/orgs`, `/admin/tenants` |
| `/admin/users` | `/admin/accounts`, `/admin/operators` |
| `/admin/organization-settings` | `/admin/settings`, `/admin/org-settings` |
| `/admin/mapping-presets` | `/admin/presets`, `/admin/mappings` |
| `/admin/import-jobs` | `/admin/jobs`, `/admin/imports`, `/admin/batches` |
| `/admin/import-jobs/{id}/errors` | `/admin/import-jobs/{id}/job-errors` |
| `/admin/import-jobs/{id}/export.json` | `/admin/import-jobs/{id}/download.json` |
| `/admin/import-jobs/{id}/export.csv` | `/admin/import-jobs/{id}/download.csv` |

### operationId（camelCase; リリース後リネーム禁止）

| operationId | Method | Path |
|---|---|---|
| `getHealth` | GET | `/health` |
| `login` | POST | `/admin/auth/login` |
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
| `listAuditLogs` | GET | `/admin/audit-logs` |

MCP ツール (Phase 3+): `listMappingPresets`, `runProfileImport`

---

## §10 OpenAPI スキーマ名

| スキーマ名 | 禁止形式 | 用途 |
|---|---|---|
| `MappingPresetResponse` | `PresetResponse`, `MappingPresetDto` | GET/POST/PATCH レスポンス |
| `MappingPresetListResponse` | `PresetListResponse`, `MappingPresetsResponse` | 一覧レスポンス |
| `CreateMappingPresetRequest` | `NewPresetRequest`, `MappingPresetInput` | POST ボディ |
| `UpdateMappingPresetRequest` | `EditPresetRequest` | PATCH ボディ |
| `ImportJobResponse` | `JobResponse`, `ImportResponse` | GET/POST レスポンス |
| `ImportJobListResponse` | `JobListResponse` | 一覧レスポンス |
| `ImportJobErrorResponse` | `JobErrorResponse`, `RowErrorResponse` | エラー行レスポンス |
| `OrganizationResponse` | `OrgResponse`, `TenantResponse` | 組織レスポンス |
| `UserResponse` | `AccountResponse`, `OperatorResponse` | ユーザーレスポンス |
| `OrganizationSettingsResponse` | `SettingsResponse`, `OrgSettingsResponse` | 設定レスポンス |

---

## §11 Problem Details スラグ

| スラグ (正規形; kebab-case) | 禁止形式 | HTTP Status |
|---|---|---|
| `validation-failed` | `validationFailed`, `validation_failed`, `invalid-request` | 422 |
| `mapping-preset-not-found` | `preset-not-found`, `preset_not_found` | 404 |
| `mapping-preset-version-frozen` | `preset-version-frozen`, `version-immutable` | 409 |
| `import-job-not-found` | `job-not-found`, `import_job_not_found` | 404 |
| `import-job-already-running` | `job-already-running`, `job_in_progress` | 409 |
| `file-too-large` | `file_too_large`, `payload-too-large` | 413 |
| `unsupported-encoding` | `invalid-encoding`, `encoding_error` | 422 |
| `organization-not-found` | `org-not-found`, `tenant_not_found` | 404 |
| `user-not-found` | `account-not-found`, `operator_not_found` | 404 |
| `forbidden` | `access-denied`, `permission_denied`, `not-authorized` | 403 |
| `unauthenticated` | `unauthorized`, `not-authenticated`, `auth_required` | 401 |

> **注意:** `401 Unauthorized` という HTTP ステータス名は OAuth の用語で混乱するが、
> NeNe Profile では `unauthenticated`（認証なし）に `401` を使い、
> `forbidden`（認可失敗）に `403` を使う。

---

## §12 フロントエンドエンティティフォルダ名 (Phase 2+)

`frontend/src/entities/` 配下のフォルダ名（kebab-case）。

| 正規名 | 禁止形式 | 対応 API リソース |
|---|---|---|
| `mapping-preset` | `preset`, `mapping_preset`, `mappingPreset` | `/admin/mapping-presets` |
| `import-job` | `job`, `import_job`, `importJob` | `/admin/import-jobs` |
| `import-job-error` | `job-error`, `row-error` | `/admin/import-jobs/{id}/errors` |
| `organization` | `org`, `tenant` | `/admin/organizations` |
| `user` | `account`, `operator` | `/admin/users` |
| `organization-settings` | `settings`, `org-settings` | `/admin/organization-settings` |
| `auth` | `authentication`, `login`, `session` | 認証 |

---

## §13 主要環境変数名

| 変数名 (UPPER_SNAKE_CASE) | 禁止形式 |
|---|---|
| `NENE_PROFILE_PORT` | `PORT`, `APP_PORT`, `NENE_PORT` |
| `NENE_PROFILE_ENV` | `ENV`, `APP_ENV`, `ENVIRONMENT` |
| `NENE_PROFILE_JWT_SECRET` | `JWT_SECRET`, `TOKEN_SECRET` |
| `NENE_PROFILE_STORAGE_PATH` | `STORAGE_PATH`, `UPLOAD_PATH` |
| `NENE_CLEAR_BEARER_TOKEN` | `CLEAR_TOKEN`, `DOWNSTREAM_TOKEN` |
| `DB_HOST` | `DATABASE_HOST`, `MYSQL_HOST` |
| `DB_PORT` | `DATABASE_PORT`, `MYSQL_PORT` |
| `DB_NAME` | `DATABASE_NAME`, `MYSQL_DATABASE` |
| `DB_USER` | `DATABASE_USER`, `MYSQL_USER` |
| `DB_PASSWORD` | `DATABASE_PASSWORD`, `MYSQL_PASSWORD` |

---

## §14 よくある禁止スペル早見表

コードレビュー・AI 出力で発生しやすいタイポ・スペルバリエーションをまとめる。
**左列（禁止）を書いたらマージブロック。右列（正規）を必ず使う。**

| 禁止形式 | 正規形 | 分類 |
|---|---|---|
| `mappingPreset` | `mapping_preset` (DB/JSON) / `MappingPreset` (PHP) | JSON では `mapping_preset` を含む snake_case |
| `importJob` | `import_job` (DB/JSON) / `ImportJob` (PHP) | 同上 |
| `presetVersion` | `preset_version` / `MappingPresetVersion` | 同上 |
| `orgId` | `organization_id` | 外部キーカラム名は省略禁止 |
| `org_id` | `organization_id` | 同上 |
| `presetId` | `preset_version_id` (出力フィールド) または `mapping_preset_id` | コンテキストにより異なる |
| `jobId` | `import_job_id` | 同上 |
| `rowNumber` | `raw_row_number` | prefix の `raw_` を省略しない |
| `row_num` | `raw_row_number` | 同上 |
| `hash` | `line_hash` | 同上 |
| `row_hash` | `line_hash` | 同上 |
| `amountCents` | `amount_cents` | JSON プロパティは snake_case |
| `amount` (金額のみ) | `amount_cents` | 単位を必ず明示 |
| `lineNumber` | `raw_row_number` | — |
| `status: "done"` | `status: "completed"` | §4 参照 |
| `status: "error"` | `status: "failed"` | §4 参照 |
| `status: "partial"` | `status: "completed_with_errors"` | §4 参照 |
| `role: "operator"` | `role: "member"` | §5 参照 |
| `role: "super_admin"` | `role: "superadmin"` | §5 参照 |
| `capability: "manage_jobs"` | `capability: "manage_import_jobs"` | §6 参照 |
| `StandardTransaction` (PHP クラス名) | `NormalizedTransaction` (PHP クラス) | スキーマ概念名と PHP 実装クラス名を混同しない |
| `manage_mapping_presets` | `manage_presets` | §6 参照 |
| `preset-not-found` | `mapping-preset-not-found` | §11 参照 |
| `unauthorized` | `unauthenticated` (401) / `forbidden` (403) | §11 参照 |
| `MappingPresetController` | `CreateMappingPresetHandler` 等 | Controller → Handler |
| `PresetService` | `CreateMappingPresetUseCase` 等 | Service → UseCase |
| `PresetRepo` | `MappingPresetRepositoryInterface` 等 | 略称禁止 |
| `src/Handlers/` | `src/Preset/`, `src/ImportJob/` 等 | レイヤーフォルダ禁止 |
| `src/Repositories/` | ドメインフォルダ内 `PdoXxxRepository.php` | 同上 |

---

Last updated: 2026-05-30
