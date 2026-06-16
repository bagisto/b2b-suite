# CLAUDE.md — Bagisto B2B Suite

This package's working guide lives in [AGENTS.md](./AGENTS.md). Read it before making
changes. The essentials:

- **Namespace** `Webkul\B2BSuite` → `src/`. Registered manually in the app's
  `bootstrap/providers.php` (auto-discovery is disabled); no `config/concord.php` entry.
- **Gate B2B behavior** behind `core()->getConfigData('b2b.general.settings.active')`.
- **Extend core, never edit it:** container binds in `Providers/B2BSuiteManager.php`,
  `view_render_event` listeners in `Providers/EventServiceProvider.php`, and view/component
  overrides published to the package-namespace path `resources/views/vendor/<namespace>`.
- **`publishables/` is the single source of everything that gets published** — sample
  storage and all view/component overrides (`publishables/resources/vendor` →
  `resources/views/vendor`, covering both regular `shop::`/`admin::` views and anonymous
  `x-shop::` components like the account navigation). Never publish directly from `src/`.
- **Own theme build (no core edits).** The package has its own Vite/Tailwind build that
  `import`s each core theme's config and regenerates the theme bundle with B2B views folded
  in (one coherent Tailwind pass) — `tailwind.{admin,shop}.config.js`, `vite.{admin,shop}.config.js`.
  Prebuilt bundles ship via `publishables/public` and are published on install (no Node
  needed normally). Adding a **new** utility class in a B2B view → rebuild: from the package
  run `npm run build` (after `npm install` in `packages/Webkul/{Shop,Admin}`). Don't add a
  second global stylesheet; for one-offs use a scoped `@push('styles')` block. Full details
  in AGENTS.md → *Styling*.
- **Vue in Blade:** put a component's markup in its own `<script type="text/x-template">`,
  not as slotted content — slot content compiles in the parent scope and breaks the
  component's `data()` bindings. **A Vue `@event` whose name matches a Blade directive
  (`@error`, `@empty`, `@checked`, `@class`, …) is eaten by Blade and breaks the compiled
  view — use the long form `v-on:error` etc.** `@click`/`@change`/`@input` are safe.

## Company Catalog

Assign a catalog to companies → controls product **visibility (allowlist)**, **category
visibility** and **pricing** for their members. Each catalog is backed by a hidden customer
group (`company_catalogs.customer_group_id`). Pricing writes per-leaf **fixed / percentage /
quantity-tier** rows to `product_customer_group_prices` + reindex (no core indexer changes);
product visibility is enforced by the extended `ProductRepository` (Prettus criterion +
PDP/cart guards) and category visibility by the extended `CategoryRepository` (filtered
tree + 404 on disallowed slugs) — both bound in `B2BSuiteManager`. Visible categories are
**derived** from the assigned products (+ ancestors) into `company_catalog_categories` on
save. Helper logic lives in `Helpers/CompanyCatalog.php`. See AGENTS.md → *Company Catalog*
for the full design.

## Publishing

```bash
php artisan vendor:publish --provider="Webkul\B2BSuite\Providers\B2BSuiteServiceProvider" --force
php artisan optimize:clear
```

## Conventions

- Repositories for DB access; Proxies for cross-package model type-hints.
- Add new translation keys for **all** locales (currently `en` only) and run
  `php artisan bagisto:translations:check`. Mind the lang array nesting.
- `vendor/bin/pint` for style; `php artisan optimize:clear` after provider/config/route changes.
- **Comments in Blade-embedded Vue/CSS** use multi-line JSDoc blocks (`/** … */`,
  capitalised and punctuated) — one consistent style per view.

## Gotchas (these have caused real bugs — see AGENTS.md → *Vue inside Blade*)

- **`view:cache` is not a syntax check** — it reports success even when the compiled PHP has
  a parse error (which only fires at render). Verify by linting `storage/framework/views/*.php`.
- **Verify Tailwind utilities against the compiled bundle** (`public/themes/<theme>/default/
  build/assets/app-*.css` via `manifest.json`) — the B2B theme purges, so an unknown class or
  responsive variant (e.g. `max-md:flex-col`) silently does nothing. Prefer scoped
  `@push('styles')` / inline `style` for one-offs.
- **`Helpers\CompanyCatalog::setPrices()` is destructive** — it deletes the whole catalog
  group's `product_customer_group_prices` rows and rewrites from the posted payload; never
  call it with a partial payload or tinker-test it on a real catalog.
