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
  component's `data()` bindings.

## Company Catalog

Assign a catalog to companies → controls product **visibility (allowlist)**
and **pricing** for their members. Each catalog is backed by a hidden customer group
(`company_catalogs.customer_group_id`); prices write to `product_customer_group_prices` +
reindex (no core indexer changes); visibility is enforced by the extended `ProductRepository`
(bound in `B2BSuiteManager`) via a Prettus criterion + PDP/cart guards. Helper logic lives
in `Helpers/CompanyCatalog.php`. See AGENTS.md → *Company Catalog* for the full design.

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
