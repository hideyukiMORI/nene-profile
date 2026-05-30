/**
 * Authoritative message catalog (primary locale = ja, ADR 0011).
 *
 * This is the single source of truth for every user-facing string in the admin
 * UI. Add new keys here first; `en.ts` is a Partial that falls back to these at
 * runtime, so an untranslated key never blocks rendering.
 *
 * Key naming convention: `{area}.{feature}.{element}`
 *  - common.*            — shared chrome, actions, statuses, errors
 *  - admin.auth.*        — login
 *  - admin.nav.*         — navigation
 *  - admin.{resource}.*  — per-resource screens (organizations, users, presets, …)
 *
 * Never hardcode user-facing strings in components — always reference a key here.
 */
export const jaMessages = {
  // ── Common ────────────────────────────────────────────────────────────────
  'common.appName': 'NeNe Profile 管理',
  'common.locale.label': '言語',
  'common.locale.ja': '日本語',
  'common.locale.en': 'English',
  'common.actions.create': '作成する',
  'common.actions.save': '保存する',
  'common.actions.edit': '編集',
  'common.actions.delete': '削除',
  'common.actions.cancel': 'キャンセル',
  'common.actions.retry': '再試行',
  'common.actions.signOut': 'ログアウト',
  'common.actions.back': '戻る',
  'common.actions.download': 'ダウンロード',
  'common.state.loading': '読み込み中…',
  'common.state.submitting': '送信中…',
  'common.state.saving': '保存中…',
  'common.state.empty': 'データがまだありません。',
  'common.error.generic': 'エラーが発生しました。',
  'common.error.network': '通信エラーが発生しました。時間をおいて再試行してください。',
  'common.error.forbidden': 'この操作を行う権限がありません。',
  'common.error.notFound': '対象が見つかりませんでした。',
  'common.pagination.summary': '{{total}} 件中 {{from}}–{{to}} 件を表示',
  'common.pagination.prev': '前へ',
  'common.pagination.next': '次へ',

  // ── Auth ──────────────────────────────────────────────────────────────────
  'admin.auth.title': 'ログイン',
  'admin.auth.email': 'メールアドレス',
  'admin.auth.password': 'パスワード',
  'admin.auth.submit': 'ログイン',
  'admin.auth.failed': 'メールアドレスまたはパスワードが正しくありません。',
  'admin.auth.emailRequired': 'メールアドレスを入力してください。',
  'admin.auth.passwordRequired': 'パスワードを入力してください。',
  'admin.account.signedInAs': '{{email}} でログイン中',

  // ── Navigation ────────────────────────────────────────────────────────────
  'admin.nav.dashboard': 'ダッシュボード',
  'admin.nav.organizations': '組織',
  'admin.nav.users': 'ユーザー',
  'admin.nav.mappingPresets': 'マッピングプリセット',
  'admin.nav.importJobs': 'インポートジョブ',
  'admin.nav.auditLogs': '監査ログ',
  'admin.nav.settings': '組織設定',

  // ── Dashboard ─────────────────────────────────────────────────────────────
  'admin.dashboard.title': 'ダッシュボード',
  'admin.dashboard.recentJobs': '最近のインポートジョブ',
  'admin.dashboard.errorRate': 'エラー率',
  'admin.dashboard.empty': 'まだジョブの実行履歴がありません。',

  // ── Organizations ─────────────────────────────────────────────────────────
  'admin.organizations.title': '組織一覧',
  'admin.organizations.empty': '組織がまだありません。',
  'admin.organizations.error': '組織を取得できませんでした。',
  'admin.organizations.col.name': '名称',
  'admin.organizations.col.slug': 'スラグ',
  'admin.organizations.col.status': '状態',
  'admin.organizations.col.customDomain': 'カスタムドメイン',
  'admin.organizations.col.actions': '操作',
  'admin.organizations.status.active': '有効',
  'admin.organizations.status.inactive': '無効',
  'admin.organizations.newButton': '組織を作成',
  'admin.organizations.create.title': '組織の作成',
  'admin.organizations.create.name': '名称',
  'admin.organizations.create.slug': 'スラグ（英小文字・数字・ハイフン）',
  'admin.organizations.create.customDomain': 'カスタムドメイン（任意）',
  'admin.organizations.create.submit': '作成する',
  'admin.organizations.create.nameRequired': '名称を入力してください。',
  'admin.organizations.create.slugRequired': 'スラグを入力してください。',
  'admin.organizations.create.slugInvalid':
    'スラグは英小文字・数字・ハイフンのみ使用できます。',
  'admin.organizations.create.error': '組織を作成できませんでした。入力内容を確認してください。',
  'admin.organizations.delete.title': '組織を削除しますか？',
  'admin.organizations.delete.message':
    '「{{name}}」を削除します。この操作は取り消せません。',
  'admin.organizations.delete.confirm': '削除する',
  'admin.organizations.delete.error': '削除できませんでした。',

  // ── Users ─────────────────────────────────────────────────────────────────
  'admin.users.title': 'ユーザー一覧',
  'admin.users.empty': 'ユーザーがまだいません。',
  'admin.users.error': 'ユーザーを取得できませんでした。',
  'admin.users.col.email': 'メールアドレス',
  'admin.users.col.role': '役割',
  'admin.users.col.status': '状態',
  'admin.users.col.actions': '操作',
  'admin.users.role.superadmin': 'スーパー管理者',
  'admin.users.role.admin': '管理者',
  'admin.users.role.member': 'メンバー',
  'admin.users.role.viewer': '閲覧者',
  'admin.users.status.active': '有効',
  'admin.users.status.invited': '招待中',
  'admin.users.newButton': 'ユーザーを作成',
  'admin.users.create.title': 'ユーザーの作成',
  'admin.users.create.email': 'メールアドレス',
  'admin.users.create.role': '役割',
  'admin.users.create.submit': '作成する',
  'admin.users.create.emailRequired': 'メールアドレスを入力してください。',
  'admin.users.create.error': 'ユーザーを作成できませんでした。',

  // ── Mapping presets ───────────────────────────────────────────────────────
  'admin.mappingPresets.title': 'マッピングプリセット一覧',
  'admin.mappingPresets.empty': 'プリセットがまだありません。',
  'admin.mappingPresets.error': 'プリセットを取得できませんでした。',
  'admin.mappingPresets.col.name': '名称',
  'admin.mappingPresets.col.bankLabel': '銀行',
  'admin.mappingPresets.col.version': 'バージョン',
  'admin.mappingPresets.col.actions': '操作',
  'admin.mappingPresets.newButton': 'プリセットを作成',
  'admin.mappingPresets.create.title': 'プリセットの作成',
  'admin.mappingPresets.create.name': '名称',
  'admin.mappingPresets.create.bankLabel': '銀行ラベル',
  'admin.mappingPresets.create.encoding': '文字コード',
  'admin.mappingPresets.create.delimiter': '区切り文字',
  'admin.mappingPresets.create.headerRow': 'ヘッダー行の位置',
  'admin.mappingPresets.create.columns': '列マッピング',
  'admin.mappingPresets.create.submit': '作成する',
  'admin.mappingPresets.create.nameRequired': '名称を入力してください。',
  'admin.mappingPresets.create.error': 'プリセットを作成できませんでした。',
  'admin.mappingPresets.field.transactionDate': '取引日',
  'admin.mappingPresets.field.valueDate': '起算日',
  'admin.mappingPresets.field.amount': '金額',
  'admin.mappingPresets.field.description': '摘要',
  'admin.mappingPresets.field.counterparty': '相手先',
  'admin.mappingPresets.field.balance': '残高',
  'admin.mappingPresets.versionFrozen':
    'このバージョンは完了済みジョブで参照されているため変更できません。',

  // ── Import jobs ───────────────────────────────────────────────────────────
  'admin.importJobs.title': 'インポートジョブ一覧',
  'admin.importJobs.empty': 'ジョブがまだありません。',
  'admin.importJobs.error': 'ジョブを取得できませんでした。',
  'admin.importJobs.col.filename': 'ファイル名',
  'admin.importJobs.col.preset': 'プリセット',
  'admin.importJobs.col.status': '状態',
  'admin.importJobs.col.rowCount': '行数',
  'admin.importJobs.col.errorCount': 'エラー数',
  'admin.importJobs.col.createdAt': '作成日時',
  'admin.importJobs.col.actions': '操作',
  'admin.importJobs.status.pending': '待機中',
  'admin.importJobs.status.running': '処理中',
  'admin.importJobs.status.completed': '完了',
  'admin.importJobs.status.completedWithErrors': 'エラーあり完了',
  'admin.importJobs.status.failed': '失敗',
  'admin.importJobs.newButton': 'CSV をインポート',
  'admin.importJobs.create.title': 'CSV インポート',
  'admin.importJobs.create.file': 'CSV ファイル',
  'admin.importJobs.create.preset': 'マッピングプリセット',
  'admin.importJobs.create.submit': 'インポート開始',
  'admin.importJobs.create.fileRequired': 'CSV ファイルを選択してください。',
  'admin.importJobs.create.presetRequired': 'プリセットを選択してください。',
  'admin.importJobs.create.error': 'インポートを開始できませんでした。',
  'admin.importJobs.detail.title': 'ジョブ詳細',
  'admin.importJobs.detail.summary':
    '{{rowCount}} 行を処理、{{errorCount}} 行がエラーで除外されました。',
  'admin.importJobs.errors.title': 'エラー行',
  'admin.importJobs.errors.col.rowNumber': '行番号',
  'admin.importJobs.errors.col.message': 'エラー内容',
  'admin.importJobs.errors.col.snippet': '元データ',
  'admin.importJobs.export.json': 'JSON でエクスポート',
  'admin.importJobs.export.csv': 'CSV でエクスポート',

  // ── Organization settings ─────────────────────────────────────────────────
  'admin.settings.title': '組織設定',
  'admin.settings.defaultEncoding': 'デフォルト文字コード',
  'admin.settings.maxFileSize': '最大ファイルサイズ（バイト）',
  'admin.settings.clearBearerToken': 'NeNe Clear 連携トークン（任意）',
  'admin.settings.save': '保存する',
  'admin.settings.saved': '設定を保存しました。',
  'admin.settings.error': '設定を保存できませんでした。',

  // ── Audit logs ────────────────────────────────────────────────────────────
  'admin.auditLogs.title': '監査ログ',
  'admin.auditLogs.empty': '監査ログがまだありません。',
  'admin.auditLogs.error': '監査ログを取得できませんでした。',
  'admin.auditLogs.col.createdAt': '日時',
  'admin.auditLogs.col.actor': '実行ユーザー',
  'admin.auditLogs.col.action': '操作',
  'admin.auditLogs.col.entity': '対象',
  'admin.auditLogs.col.changes': '変更内容',
  'admin.auditLogs.actor.system': 'システム',
  'admin.auditLogs.changes.created': '作成',
  'admin.auditLogs.changes.deleted': '削除',
  'admin.auditLogs.changes.before': '変更前',
  'admin.auditLogs.changes.after': '変更後',
  'admin.auditLogs.viewDiff': '差分を表示',
} as const
