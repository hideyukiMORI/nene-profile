# フロントエンド標準

**Status: Phase 2 — `frontend/` スキャフォールドは Issues で追跡。**
このドキュメントは binding ポリシー; 画面実装はこれに従う。

NeNe Profile の admin UI は JSON API の **React + TypeScript** クライアント。
UI はスキーマ・バリデーション・トランスフォームルール・プロベナンスルールの
ソースオブトゥルースではない — PHP API が所有する。
UI は API の型とエラーを反映するのみ; バリデーションを代替しない。

**基準・継承:** 姉妹製品 **nene-invoice** フロントエンド規約
([`../nene-invoice/docs/development/frontend-standards.md`](../../nene-invoice/docs/development/frontend-standards.md))
をベースとし、以下の Profile 固有ルールで上書きする。

---

## Profile 固有ルール（優先）

| トピック | ルール |
|---|---|
| **ロケール** | **`ja`（primary）+ `en`（secondary）のみ** — ADR 0011。他のロケールは追加しない。 |
| **JSON シェイプ** | API JSON は **snake_case**; API クライアントが typed model にマップ。フィールドをリネームしない。|
| **金額** | 常に **integer cents**。フロート禁止。表示変換は UI エッジのみ。 |
| **符号表示** | `amount_cents` 正=入金（緑）、負=出金（赤）。UI が符号を反転させない。 |
| **ファイルアップロード** | `<input type="file">` + `multipart/form-data`。ブラウザの File API を使用; 処理・解析はバックエンドで。 |
| **ジョブステータス表示** | `pending`/`running` は polling（5 秒間隔、TanStack Query refetchInterval）。`completed`/`completed_with_errors`/`failed` は静的。 |
| **エラーテーブル** | `import_job_errors` を行単位で表示; `raw_row_number` + `message` + raw snippet。 |
| **認証トークン** | In-memory デフォルト（`localStorage` 保存は ADR 必須）。 |
| **ビルド出力** | `public_html/admin/` (Tier A 同一オリジン)。 |
| **RBAC UI** | API 公開のケイパビリティで表示制御; UI ゲーティングは UX のみ — API が認可を強制。 |

---

## スタック

| レイヤー | 選択肢 | 備考 |
|---|---|---|
| UI | **React**（最新安定版）| 関数コンポーネント + フックのみ |
| 言語 | **TypeScript**（最新安定版）| 全ソース `.ts`/`.tsx` |
| バンドラ | **Vite** | 開発サーバ + 本番ビルド → `public_html/admin/` |
| パッケージマネージャ | **npm** | `frontend/package-lock.json` コミット; CI は `npm ci` |
| Node.js | **Active LTS (≥22)** | `engines` + `packageManager` in `package.json` |
| ルーティング | **React Router** | URL は共有可能な状態 |
| サーバー状態 | **TanStack Query v5** | クエリ・ミューテーション・キャッシュ |
| フォーム | **React Hook Form** + **Zod** | クライアント UX バリデーションのみ |
| Lint | **ESLint** flat config: `typescript-eslint` strict-type-checked, `react-hooks`, `jsx-a11y`, `import/no-restricted-paths` | `--max-warnings 0` |
| フォーマット | **Prettier** | — |
| ユニット/統合 | **Vitest** + **Testing Library** + **MSW** | jsdom |
| スタイリング | **Tailwind CSS v4** | セマンティックユーティリティ → CSS カスタムプロパティ |
| デザイントークン | **CSS カスタムプロパティ** in `shared/ui/theme/` | 全ビジュアル値の唯一の正解 |
| コンポーネントカタログ | **Storybook** | `shared/ui` プリミティブ + 組み合わせコンポーネント |
| API 型 | **openapi-typescript** | `docs/openapi/openapi.yaml` → `shared/api/schema.gen.ts` |

---

## アーキテクチャ（レイヤー）

`app → pages → features → entities → shared`

| レイヤー | 所有するもの | 所有しないもの |
|---|---|---|
| `shared/` | トランスポート、デザイントークン、純粋ユーティリティ、i18n | ルート、フィーチャー、リソースモデル |
| `entities/` | 1 API リソース: DTO マッピング、クエリキー、TanStack フック | JSX、クロスリソース処理 |
| `features/` | ユーザーワークフロー（entities + UI の合成）| raw HTTP、DTO 型、直接の TanStack キー文字列 |
| `pages/` | ルート配線、遅延ロード | ビジネスルール、API 呼び出し |
| `app/` | プロバイダー、ルーター、グローバルエラーバウンダリ | フィーチャー固有の画面 |

**依存方向（ハードルール）:** 矢印は上向きに向けない。クロスフィーチャー共有は `entities/` または `shared/`（ADR）へ。

---

## リポジトリレイアウト

```text
frontend/
  package.json  package-lock.json  tsconfig.json  vite.config.ts
  vitest.config.ts  eslint.config.js  .prettierrc  knip.json
  src/
    main.tsx
    app/
      providers.tsx    # QueryClientProvider, Router, theme, i18n, auth gate
      router.tsx
      root-error-boundary.tsx
      auth-gate.tsx    # fail-closed セッションチェック
    pages/
      login/
      presets/         # プリセット一覧・編集
      import-jobs/     # ジョブ一覧・詳細・エラーテーブル・エクスポート
      organizations/   # (superadmin)
      users/
    features/
      list-import-jobs/
        index.ts
        hooks/use-list-import-jobs.ts
        ui/ListImportJobs.tsx
        ui/ListImportJobs.test.tsx
      run-import-job/
      edit-preset/
      ...
    entities/
      import-job/
        index.ts  ids.ts  enum.ts  api-types.ts  model.ts  mapper.ts
        query-keys.ts  queries.ts  mutations.ts  mapper.test.ts
      mapping-preset/
      organization/
      user/
      auth/
    shared/
      api/
        client.ts          # fetch() はここのみ
        errors.ts          # Problem Details → AppError
        schema.gen.ts      # openapi-typescript 出力 (生成物; 編集不可)
      config/env.ts        # Zod バリデーション済み env
      i18n/
        locales.ts  messages/ja.ts  messages/en.ts
        i18n-context.tsx  use-translation.ts  translate.ts
      lib/
      ui/
        theme/
          index.css        # Tailwind エントリ + アクティブテーマ import
          active.css       # @import './themes/default.css'
          themes/default.css
        primitives/        # Button, Input, Text, Stack, FileUpload, StatusBadge, ...
        components/        # Dialog, ConfirmDialog, EmptyState, JobStatusPoller, ...
        index.ts
  .storybook/
  tests/
    setup/  msw/  factories/  render/
```

---

## データフロー

### 読み取りパス

```text
API JSON
  → shared/api/client.ts
  → entities/{r}/api-types.ts   (wire shape; snake_case)
  → entities/{r}/mapper.ts      (model へマップ; 金額/日付をエッジで整形)
  → entities/{r}/queries.ts     (TanStack Query キャッシュ)
  → features/{f}/hooks/*.ts
  → features/{f}/ui/*.tsx
```

### 書き込みパス

```text
UI event → features/{f}/hooks → entities/{r}/mutations.ts → shared/api/client.ts → API
  → onSuccess: クエリキー無効化（明示的; コロケーション）
  → onError: Problem Details → AppError → UI フィードバック
```

---

## ジョブステータスポーリング

`running` / `pending` ステータスのジョブ詳細画面では 5 秒間隔で自動リフレッシュ:

```ts
useQuery({
  queryKey: importJobKeys.detail(id),
  queryFn: () => fetchImportJob(id),
  refetchInterval: (query) =>
    ['pending', 'running'].includes(query.state.data?.status ?? '')
      ? 5_000
      : false,
})
```

ターミナルステータス到達後はポーリング停止。

---

## TypeScript 厳格設定

`tsconfig.app.json` 最小要件: `strict`, `noUncheckedIndexedAccess`,
`noImplicitOverride`, `exactOptionalPropertyTypes`, `verbatimModuleSyntax`,
`noUnusedLocals`, `noUnusedParameters`, `noFallthroughCasesInSwitch`,
`forceConsistentCasingInFileNames`, `isolatedModules`, `jsx: react-jsx`,
`moduleResolution: bundler`, `noEmit`.

- `any` 禁止 — `unknown` を使い絞り込む
- `@ts-expect-error` / `@ts-ignore` は Issue/ADR id をコメントに要求
- `!` 非 null アサーションは不変条件コメント必須
- コンポーネント props は `interface`; union/mapped 型は `type`
- リソース ID: `entities/{r}/ids.ts` でブランド ID — レイヤーを跨いで bare `string` を使わない

---

## 国際化 (ja + en のみ — ADR 0011) — **実装済み**

`frontend/src/shared/i18n/` にメッセージカタログ基盤を実装済み（Issue #13）。

| モジュール | 役割 |
|---|---|
| `messages/ja.ts` | **正本カタログ** — 全画面の文言を最初にここへ定義（`MessageKey` の型ソース）|
| `messages/en.ts` | 英語 `Partial` — 不足キーは実行時に ja へフォールバック |
| `messages/catalog.test.ts` | **整合性ガード** — en キー ⊆ ja キー、空値なし、補間プレースホルダ一致 |
| `translate.ts` | 純粋関数: カタログ解決 + ja フォールバック + `{{param}}` 補間 |
| `locales.ts` | `SupportedLocale`, `DEFAULT_LOCALE`, `resolveLocale`, `LOCALES` |
| `i18n-context.tsx` | `I18nProvider` — localStorage 永続化 + navigator 検出 |
| `use-translation.ts` | `useTranslation()` フック |
| `LocaleSwitcher.tsx` | ja/en 切替ボタン群 |

```ts
type SupportedLocale = 'ja' | 'en'
```

- **ユーザー向け文字列はハードコード禁止** → 必ず `t('admin.mappingPresets.title')`
- キー命名: `{area}.{feature}.{element}`（例: `admin.importJobs.status.completed`）
- `ja` が正本（authoritative）; `en` は `Partial`; 不足キーは `ja` にフォールバック
- 検出順序: `localStorage['nene-profile-locale']` → `navigator.language` → `ja`
- 言語切替は再レンダリングのみで即時反映（リロード・通信なし）
- `catalog.test.ts` が ja/en の乖離をビルド時に検出
- ADR 0011 を更新せずに第三のロケールを追加しない

### 文言の追加手順

1. `messages/ja.ts` にキーを追加（正本）
2. `messages/en.ts` に英訳を追加
3. コンポーネントで `t('your.key')` 参照（リテラル直書き禁止）
4. `npm run test` が ja/en 整合性を強制

---

## セキュリティ

| トピック | ルール |
|---|---|
| シークレット | リポジトリに含めない。フロントエンド env は `VITE_*` 公開値のみ。 |
| 認証トークン | In-memory デフォルト; `localStorage`/`sessionStorage` またはクッキーセッションは ADR 必須 |
| XSS | `dangerouslySetInnerHTML` は DOMPurify + Issue なしに使用不可 |
| リンク | `target="_blank"` には `rel="noopener noreferrer"` |
| RBAC UI | API ケイパビリティで表示制御; UI ゲーティングは UX のみ |

---

## コマンドと CI

```bash
npm ci --prefix frontend
npm run dev --prefix frontend          # Vite 開発サーバ; API を PHP アプリにプロキシ
npm run codegen --prefix frontend      # schema.gen.ts を OpenAPI から再生成
npm run check --prefix frontend        # 型チェック + lint + format + test + knip + build-storybook
npm run build --prefix frontend        # 本番ビルド → public_html/admin/
```

CI (フロントエンド変更時): `npm ci` → `npm run check` → `npm audit --audit-level=high`.

---

## 禁止パターン

`useEffect`+`fetch` でサーバーデータ取得 · サーバーデータを 3 層以上 prop-drilling ·
グローバル pub/sub · API レスポンスを `useState` に保存 · クラスコンポーネント ·
**デフォルトエクスポート** · `shared/ui` 内のビジネスルール · features 内の文字列クエリキー ·
ADR なしの `dangerouslySetInnerHTML` · ADR なしの `localStorage` 認証トークン ·
コンポーネント内の raw カラー/スペーシング/タイポリテラル ·
Tailwind 任意値 (`p-[13px]`) · デザインリテラルを含む inline `style` ·
`amount_cents` をフロートとして表示計算.

---

## 関連ドキュメント

- 自己レビュー: `docs/review/frontend.md` (Phase 2 追加)
- API コントラクト: `docs/openapi/openapi.yaml`
- 命名規則: `docs/development/naming-conventions.md`
- 用語（識別子）: `docs/explanation/terminology.md`
- コンプライアンス: `docs/explanation/accounting-compliance.md`
- ロケール方針: `docs/adr/0011-bilingual-jp-en-documentation.md`
