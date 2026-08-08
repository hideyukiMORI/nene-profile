# 依存脆弱性ゲート（audit-ci）の運用

フロントエンド依存の脆弱性ゲートは **audit-ci**（`frontend/audit-ci.jsonc`）が正本。
CI では `.github/workflows/frontend-ci.yml` の `npm run audit` として走り、
**allowlist に無い high / critical があれば fail** する。

- 実測は **必ず `frontend/` で叩く**。リポルートの `npm audit` は `package.json` 不在でも
  entries 0 の偽緑を返す（フリート実測 2026-08-08）。
- 設定: `moderate: false` / `high: true` / `critical: true`。**severity は下げない**。

## allowlist の現在値: **空**（2026-08-08・#128）

例外ゼロで緑を維持するのが最終形。追加するなら、その前に必ず
**「patch 版が本当に存在しないか」を GitHub Advisory API で一次実測**する:

```bash
gh api /advisories/<GHSA> \
  --jq '.vulnerabilities[]|"\(.package.name) \(.vulnerable_version_range) → \(.first_patched_version)"'
```

エントリを足すときの条件は従来どおり 4 点セット:
(1) advisory id (2) **この艦で当てはまらない理由（実測。想定でなく）** (3) 期限 (4) 何が来たら外せるか。
期限切れは「更新」ではなく **再検証タスク**。

### 🔴 理由文も腐る

有効性（advisory がまだ鳴るか）だけでなく、**書いた理由文そのものが古くなる**。
本艦では撤去した 2 件とも「advisory の脆弱範囲が後から再スコープされ、理由文の前提が偽になっていた」形だった。
allowlist を触るときは **生死と理由文を 2 コマンドで両方**検証すること:

```bash
npm audit --json | grep <GHSA>          # ① まだ鳴っているか
gh api /advisories/<GHSA> --jq '...'    # ② 脆弱範囲・patch 版は当時のままか
```

## 撤去したエントリの記録（削除ノート）

### GHSA-qwww-vcr4-c8h2 — react-router "RSC Mode CSRF Bypass"(high)

- **撤去日**: 2026-08-08（#128）／**撤去理由**: 修正版へ到達済み。
- 追加時（07-31）の前提は「脆弱範囲 7.12.0–8.2.0・**7.x 系に修正なし**（修正は v8>=8.2.1 ＝ 破壊的移行）」。
- 2026-08-08 の Advisory API 実測では **`>=7.12.0 <7.18.2 → 7.18.2`** と **`>=8.0.0 <8.3.0 → 8.3.0`** に
  再スコープされていた＝**7.x へ backport 済み**。追加時の前提は現在では偽。
- 本艦の lockfile は `react-router` / `react-router-dom` とも **7.18.2**（`package.json` は `^7.1.0`）＝ patched。
- **救出した知見（まだ生きている）**: 本艦の admin UI は Vite ビルドの client-only 静的 SPA。
  ルーターは `src/app/router.tsx` の `createBrowserRouter` のみで、route レベルの `action` / `loader` は 0。
  `@react-router/dev` / `react-router/rsc` / `createStaticHandler` / `StaticRouterProvider` はいずれも 0 件。
  → RSC 系 advisory は本艦の構成では攻撃経路の実体を持たない。**次に react-router の advisory が来たら、
  まずこの構成事実を再確認する**（バンドルが SSR/RSC を持つよう変わったら前提ごと無効）。

### GHSA-mh99-v99m-4gvg — brace-expansion "DoS via unbounded expansion"(high)

- **撤去日**: 2026-08-08（#128）／**撤去理由**: 全系列が patch 版へ到達済み。
- 追加時（07-31）の前提は「**1.x / 2.x 系には patched release が存在しない**」。
- 2026-08-08 の Advisory API 実測では **1.1.17 / 2.1.3 / 3.0.3 / 5.0.8** に patch が実在。追加時の前提は偽。
- 本艦の locked コピーは **1.1.18 / 2.1.4 / 5.0.9**（フリート最終形と一致）＝ 例外不要。
- **救出した知見（まだ生きている）**: brace-expansion は本艦では **dev 専用**
  （lockfile の全コピーが `dev: true`・出荷バンドルに含まれずリクエスト経路に到達しない）。

## 温存している override とその削除条件

`frontend/package.json` の `overrides`:

### `"@redocly/openapi-core": { "js-yaml": "^4.3.1" }` — スコープ限定（2026-08-08・#129 で追加）

- **なぜ必要か**: `@redocly/openapi-core` は js-yaml を **exact pin**（`"js-yaml": "4.3.0"`）している。
  本艦に js-yaml の flat override は無いため、**lockfile 更新だけでは 4.3.1 へ上がらない**
  （GHSA-5p4m-2wfm-xmqj）。親の exact pin を上書きするために親スコープ限定で当てている。
- caret（`^4.3.1`）にしてあるのは、exact pin にすると 4.3.x の patch が出るたびにこの行を
  触ることになるため。ツリー全体へ flat に効かせてはいない（`@eslint/eslintrc` は `^4.1.1` で
  もともと追随するので巻き込む必要がない）。
- 効いていることの実測: `npm ls js-yaml --all` が
  `@redocly/openapi-core@1.34.18 overridden → js-yaml@4.3.1` と表示する。
- 🔴 **削除条件 / 再読条件**:
  - `@redocly/openapi-core` が js-yaml 4.3.1+ を**自前で取り込んだら削除**する
    （`npm ls @redocly/openapi-core` の版を上げたうえで、この override を外して
    `npm ls js-yaml --all` が 4.3.1+ のままか確認する）。
  - **次に js-yaml の advisory が来たら、まずこの行を再読する**。
    「exact pin の親を override している」という事実自体が、親の更新で静かに消えうる
    （消えた後もこの行は無害に残り続けるため、腐りに気づけない）。

### `"brace-expansion@5": "^5.0.8"` — **スコープ限定のまま温存**

- **flat 化も撤去も既知の地雷**（deal #202）。`brace-expansion@5` をツリー全体へ flat に強制すると
  `minimatch@3` 系の消費者が壊れる（v5 で named export 化。v1 の関数 export を呼ぶ罠は健在）。
- caret なので 5.0.9 は既に範囲内。**版を上げる目的で書き換える必要はない**。
- 撤去できる条件: eslint 10 / plugin major 波でチェーンが `minimatch@10 → brace-expansion@^5` へ動き、
  `minimatch@3` 系の消費者がツリーから消えたとき。
- **lint が緑なことは override の無罪証明にならない**。判定するなら deal の toolchain テスト
  （`tests/toolchain/brace-expansion-override.test.ts` 型）を同梱すること。

## 触らないと決めているもの

- **esbuild low（GHSA-g7r4-m6w7-qqqr）**: 出ても触らない。修正は vite の major 移行待ちで、
  ゲートは `moderate: false` なので low は素通りする。board に受け皿あり。

## moderate 素通り分の prod 露出

ゲートは `moderate: false` なので moderate は fail しない。**素通りしたものが出荷バンドルに
載っていないか**は別途 1 回見ること（records の dompurify が実例）:

```bash
npm ls <pkg> --omit=dev --all
```
