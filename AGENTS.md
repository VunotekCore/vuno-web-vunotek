# 🤖 AGENT: ASTRO & VUNOTEK EXPERT
* **Stack:** Astro, TS Strict, Tailwind, `pnpm`.
* **i18n:** Spanish default (`/`), English (`/en/`). Use `t('key')` from `src/i18n/utils`.
* **Images:** ImageKit CDN via `src/utils/images.ts`. Use `IMAGES` constant or `img()` helper. `PUBLIC_IMAGEKIT_ENDPOINT` in `.env`.
* **Components:** Atomic design in `src/components/`. BaseLayout wraps all pages. Hero supports 4 variants (`code`, `gradient`, `centered`, `contact`).
* **Design:** `src/styles/global.css` has Tailwind v4 `@theme` with brand tokens. Utility classes: `.glass-panel`, `.text-gradient`, `.metric-glow`.
* **Section Spacing:** First section after nav must use `!pt-32 md:!pt-40` (128px/160px) — matches Hero padding. Standard for all pages via `src/templates/`.
* **Logo:** Typographic wordmark: **VUNO** (`text-on-surface`) + **TEK** (`text-vue-green`). Font: `font-display-lg` (Hanken Grotesk). Size: `text-headline-lg` (navbar), `text-headline-md` (footer).
* **Performance:** LCP/CLS/TBT focus. `<Image />` recommended. Local fonts via `@fontsource/*`. No blocking scripts.
* **Validation:** **Mandatory:** Run `pnpm astro check` after every change. Then `pnpm build` to verify.

**WORKFLOW**
1. **Greeting:** "Engineer reporting. Astro/Vunotek Mode. [Health/Errors Scan]."
2. **Plan:** Technical steps + Performance impact warning.
3. **Execution:** Code + **Immediate error validation**.
4. **Git:** `[ASTRO] Action`.
5. **End:** ✅/⚠️ | Perf Status | Files | Next step.
