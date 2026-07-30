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
- **Own bundle, never the core's (no core edits).** The package builds its **own** stylesheet
  into `public/themes/b2b-suite/{admin,shop}/build` — its own manifest, registered as a
  `bagisto-vite` viter and injected into the core layout heads. **Never write to or publish
  over `themes/{admin,shop}/default`** (the core replaces those on upgrade), and **keep the
  injection on `head.before`, not after** — see AGENTS.md → *Styling* → *Load order*. Prebuilt
  bundles ship via `publishables/public` and are published on install (no Node needed
  normally). Adding a **new** utility class in a B2B view → rebuild: `npm ci` once per clone,
  then `npm run build` from the package. For one-offs a scoped `@push('styles')` block still
  wins over both sheets.
- **Vue in Blade:** put a component's markup in its own `<script type="text/x-template">`,
  not as slotted content — slot content compiles in the parent scope and breaks the
  component's `data()` bindings. **A Vue `@event` whose name matches a Blade directive
  (`@error`, `@empty`, `@checked`, `@class`, …) is eaten by Blade and breaks the compiled
  view — use the long form `v-on:error` etc.** `@click`/`@change`/`@input` are safe.

## Company Catalog

Assign a catalog to companies → controls product **visibility (allowlist)**, **category
visibility** and **pricing** for their members. Each catalog is backed by a hidden customer
group (`b2b_company_catalogs.customer_group_id`). Pricing writes per-leaf **fixed / percentage /
quantity-tier** rows to `product_customer_group_prices` + reindex (no core indexer changes);
product visibility is enforced by the extended `ProductRepository` (Prettus criterion +
PDP/cart guards) and category visibility by the extended `CategoryRepository` (filtered
tree + 404 on disallowed slugs) — both bound in `B2BSuiteManager`. Visible categories are
**derived** from the assigned products (+ ancestors) into `b2b_company_catalog_categories` on
save. Helper logic lives in `Helpers/CompanyCatalog.php`. See AGENTS.md → *Company Catalog*
for the full design.

## Installation

Main install — the [README](./README.md) is canonical: `composer require bagisto/b2b-suite`
→ register `B2BSuiteServiceProvider` in `bootstrap/providers.php` **after Shop** (auto-discovery
is disabled) → `php artisan b2b-suite:install`. Installs into `vendor/bagisto/b2b-suite`.

**This repo is a development checkout** — the package lives at `packages/bagisto/b2b-suite`,
wired via the root `packages/*/*` path repo (`"bagisto/b2b-suite": "@dev"`) and symlinked into
`vendor/bagisto/b2b-suite`. That's the dev layout, not the install layout.

## Publishing

```bash
php artisan vendor:publish --provider="Webkul\B2BSuite\Providers\B2BSuiteServiceProvider" --force
php artisan optimize:clear
```

## Conventions

- **All DB access via repositories** — no `DB` facade or `Proxy::…::query()` in
  controllers / helpers / listeners / datagrids (only `DB::transaction()` and `Schema::`
  metadata are allowed). Proxies are for cross-package model type-hints in relationships only;
  for queries Prettus can't express, add a method to the repository.
- **`b2b_` prefix** on every package-owned table; reference it everywhere — model `$table`,
  FKs, `DB::raw`, and `exists:`/`unique:` rules. Each concrete model sets an explicit `$table`
  (core-table-backed models like `Customer` inherit it).
- **Member ordering** — constructors: repositories first, then helpers. Controllers: RESTful
  lifecycle → mass actions → other endpoints → protected helpers. Models (Laravel-standard):
  constants → Laravel props (`$table`/`$fillable`/`$casts`/`$timestamps`) → extra props →
  methods (relationships → accessors → overrides → helpers → static). Properties/constants
  before methods; every constant gets its own docblock.
- **Docblocks** on every method/property/constant — one-line, punctuated. Inline notes use
  `/** … */`, not `//`.
- **Events:** symmetric `*.before`/`*.after` on CRUD mutations. **Migrations:** one `create_*`
  per table (`b2b_`-prefixed), core-table `add_*` last, explicit short FK names,
  `constrained(table: …)`. **Seeders:** `delete()` not `truncate()` (no FK-check toggling).
  **No dead code** (drop `parent::`-only overrides and unused members).
- **Blade:** tags with 2+ attributes are multiline (0–1 inline), preserving Vue/`:`/`@`/`x-slot`
  bindings exactly; label comments Title Case, sentence comments punctuated; shop views use
  `x-shop::*`, admin `x-admin::*` — never mix.
- **Routes:** each route file is self-contained (owns its `Route::group` middleware + prefix);
  `web.php` only `require`s them; order route groups to match the menu.
- **Translations:** all **22 locales** exist; `en` is the canonical structure and every locale
  shares its exact keys/order (values translated, `:placeholders` preserved). Lang file order:
  top-level `admin, shop, emails, seeders, commands`; within admin/shop `acl, layouts → menu
  features → configuration`; leaf keys alphabetical; no dead keys. Run
  `php artisan bagisto:translations:check` after changes.
- `vendor/bin/pint` for style; `php artisan optimize:clear` after provider/config/route changes.
- **Comments in Blade-embedded Vue/CSS** use multi-line JSDoc blocks (`/** … */`,
  capitalised and punctuated) — one consistent style per view.
- Full detail in [AGENTS.md](./AGENTS.md) → *Conventions*.

## Gotchas (these have caused real bugs — see AGENTS.md → *Vue inside Blade*)

- **`view:cache` is not a syntax check** — it reports success even when the compiled PHP has
  a parse error (which only fires at render). Verify by linting `storage/framework/views/*.php`.
- **Verify Tailwind utilities against the compiled bundle** — now B2B's own
  (`public/themes/b2b-suite/{admin,shop}/build/assets/*.css` via its `manifest.json`), not the
  core theme's. The sheet purges, so an unknown class silently does nothing. A **responsive
  variant** (e.g. `max-md:flex-col`) needs one extra check: B2B's sheet loads *before* core's,
  so if core's bundle emits the plain counterpart and not the variant, core's rule wins —
  confirm the rendered result, not just that the class exists. Prefer scoped `@push('styles')`
  / inline `style` for one-offs.
- **`Helpers\CompanyCatalog::setPrices()` is destructive** — it deletes the whole catalog
  group's `product_customer_group_prices` rows and rewrites from the posted payload; never
  call it with a partial payload or tinker-test it on a real catalog.
