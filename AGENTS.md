# AGENTS.md — Bagisto B2B Suite

Guidance for AI agents (and humans) working inside the `bagisto/b2b-suite` package.
This file describes how the package is wired into Bagisto and the conventions you must
follow when changing it.

## Overview

B2B Suite extends Bagisto's storefront and admin with company accounts, company users
and roles, requisition lists, quick order, quotations (RFQ), purchase orders and
company catalogs (per-company product visibility + pricing).

- **Namespace:** `Webkul\B2BSuite` → `src/`
- **Package path:** `packages/bagisto/b2b-suite` (symlinked into `vendor/bagisto/b2b-suite`)
- **PHP:** 8.1+ · **Bagisto:** 2.x · Blade views styled via the core Shop/Admin themes
  (the package ships no build of its own — see *Styling* below)

## How the package is registered

Composer auto-discovery is intentionally **disabled** for this package. It is wired up
manually in the application:

1. `composer.json` (root) requires `bagisto/b2b-suite` via the `packages/*/*` path repo.
2. `bootstrap/providers.php` registers `Webkul\B2BSuite\Providers\B2BSuiteServiceProvider`
   **last** (it must load after the Shop package).

`B2BSuiteServiceProvider` itself registers `ModuleServiceProvider` (Concord models) and
`EventServiceProvider`, so there is **no** `config/concord.php` entry.

## The active flag

Almost everything is gated behind the admin config flag:

```php
core()->getConfigData('b2b.general.settings.active')
```

When inactive: B2B routes, menus, the company registration view and the company-specific
parts of overridden views are not shown. Keep new B2B-only behavior behind this flag.

## Extending core without editing it

- **Controllers / models / repositories:** swapped in the container by
  `Providers/B2BSuiteManager.php` (`$app->bind(CoreClass::class, B2BClass::class)` and
  `concord->registerModel(...)`). B2B classes `extend` the core ones and override only
  what they need. Current binds include the core `ProductRepository` (extended for
  company-catalog visibility — see *Company Catalog* below).
- **Inline content:** injected into core blades via `view_render_event(...)` listeners
  registered in `Providers/EventServiceProvider.php` (`$e->addTemplate('b2b::...')`).
- **View / component overrides:** published to the package-namespace override path
  `resources/views/vendor/<namespace>` (Laravel's standard override, registered by core's
  `loadViewsFrom('shop')` / `loadViewsFrom('admin')`). **One mechanism for everything** —
  regular `shop::`/`admin::` views (e.g. `shop::customers.sign-in`) *and* anonymous
  `x-shop::` components (e.g. the account navigation, which compile to `shop::components.<name>`
  and which theme view-path overrides can't reach). Mirror the namespaced path under
  `publishables/resources/vendor/<namespace>/<path>`; everything is published — no runtime
  namespace hacks.

## `publishables/` — the single source of everything that gets published

**Convention: anything that is published to the application lives under `publishables/`.**
Never publish directly from `src/`.

```
publishables/
├── storage/                   → storage/app/public                 sample data
└── resources/
    └── vendor/                → resources/views/vendor             all view/component overrides
        ├── shop/
        │   ├── customers/sign-in.blade.php                         overrides shop::customers.sign-in
        │   ├── checkout/cart/{index,summary,request-quote-modal}.blade.php
        │   └── components/layouts/account/navigation.blade.php     overrides x-shop::layouts.account.navigation
        └── admin/
            └── customers/customers/index/create.blade.php          overrides admin::customers.customers.index.create
```

A file published to `resources/views/vendor/<namespace>/<path>` overrides the matching
namespaced view (`shop::customers.sign-in`, `admin::customers.customers.index.create`, the
`x-shop::layouts.account.navigation` component, …). This is the single override mechanism.

To override another view/component: add it under `publishables/resources/vendor/<namespace>/<path>`
(mirror the namespaced path without the namespace prefix — components live under
`<namespace>/components/<name>`), then re-publish.

## Styling — the package has no build, but the core themes scan its views

This package ships **no compiled CSS/JS** of its own and has no Vite/Tailwind build. The
storefront/admin use the core Shop and Admin Vue apps and stylesheets; B2B Blade views are
styled with Tailwind utility classes.

**Important cross-package dependency:** B2B views live outside the core themes'
`src/Resources/**`, so Tailwind would not normally generate the utility classes they use.
To fix this, the core theme configs were extended to **scan the B2B views**:

- `packages/Webkul/Shop/tailwind.config.js` — `content` includes
  `../../bagisto/b2b-suite/src/Resources/views/shop/**` and `publishables/resources/vendor/shop/**`;
  also sets `darkMode: "class"` (the storefront is light-only, so `dark:` utilities never
  auto-activate via `prefers-color-scheme`).
- `packages/Webkul/Admin/tailwind.config.js` — `content` includes
  `../../bagisto/b2b-suite/src/Resources/views/admin/**` and `publishables/resources/vendor/admin/**`.

Consequences for anyone changing B2B views:

- If you introduce a **new** Tailwind utility class in a B2B view, you must rebuild the
  affected theme so the class is generated:
  ```bash
  cd packages/Webkul/Shop  && npm install && npm run build   # storefront views
  cd packages/Webkul/Admin && npm install && npm run build   # admin views
  ```
- Reusing classes the core theme already emits needs no rebuild.
- These two `tailwind.config.js` edits are the only changes made to core packages; keep
  them (or replace them with scoped `@push('styles')` blocks) if you ever vendor this out.

Do **not** add a second global stylesheet — loading another Tailwind utility sheet after
the core one lets its plain utilities override core's responsive variants (this previously
broke the responsive flash toasts). For one-off rules, prefer a scoped `@push('styles')`
block within the view.

### Vue inside Blade

B2B interactive views register inline components (`app.component('v-…', { template:
'#…-template' })`) inside `@pushOnce('scripts')`. **Put the component's markup in its own
`<script type="text/x-template">` — do not pass it as slotted content to the component.**
Slot content compiles in the *parent* (root app) scope, so the component's `data()` is not
in scope and bindings like `v-if="items.length"` throw on `undefined`. (This bit the shared
catalog form once.) Blade/`@lang`/`{{ route() }}` inside `<script>` produce false-positive
IDE diagnostics — ignore them.

## Company Catalog

Company catalogs: assign a catalog to companies to control **which products
their members can see/buy (allowlist)** and **what prices they pay**. The design reuses
Bagisto's existing customer-group price index instead of touching the core indexer.

**Core idea — each catalog is backed by a hidden customer group.**

- `CompanyCatalog` (`company_catalogs`) holds `name`, `description`, `status`, and a
  `customer_group_id` pointing at a dedicated group (`code = company_catalog_<id>`,
  `is_user_defined = 0`) created on first save by `Helpers/CompanyCatalog::provisionGroup()`.
- `company_catalog_products` is the visibility **allowlist** (catalog ↔ product).
- `customers.company_catalog_id` assigns a **company** (a `customers` row with
  `type = company`) to a catalog.

**Assignment & group sync** (`Helpers/CompanyCatalog`):

- `assignCompanies($catalog, $ids)` diff-syncs companies; `attachCompany`/`detachCompany`
  set the company **and all its members'** `customer_group_id` to the catalog group (or back
  to `general` on removal).
- New members inherit the group via the `Listeners/Company` listener
  (`customer.registration.after` / `customer.update.after`).
- `cleanup($catalog)` (called before delete) reverts companies/members and drops the group.

**Pricing — no core indexer changes** (`Helpers/CompanyCatalog::setPrices()`):

- Per-product fixed prices are written to `product_customer_group_prices` for the catalog
  group, then `UpdateCreatePriceIndex` is dispatched for the affected products. The existing
  price index serves catalog prices automatically. Reindexing every catalog product (even
  unpriced ones) ensures each has a price-index row for the group — the admin form posts a
  `prices[ID]` entry for every assigned product so this happens.
- Reindex timing depends on `QUEUE_CONNECTION`: `sync` runs inline; otherwise a queue worker
  must run for prices to appear.

**Storefront visibility (allowlist)** — `Repositories/ProductRepository` (extends core,
bound in `B2BSuiteManager`):

- Resolves the current customer's catalog via group id; if present, pushes a Prettus
  `CompanyCatalogVisibilityCriteria` (a `whereIn('products.id', <catalog products>)` subquery)
  so listing/search/`getMaxPrice` are restricted. `findBySlug` is guarded for the PDP.
- **Inert for guests, admins, and unassigned customers** (no catalog → no criterion).
- The cart-add guard lives in the shop API `CartController::isWithinCompanyCatalog()`.
- The DB path is covered; an **Elasticsearch** storefront would need an equivalent ES filter.

**Admin UI:** `Http/Controllers/Admin/CompanyCatalogController`, `DataGrids/Admin/
CompanyCatalogDataGrid`, views under `Resources/views/admin/company-catalogs/`
(`index`, `create`, `edit`, shared `form`). The form's product picker reuses
`admin.catalog.products.search`; the company picker uses the dedicated
`admin.b2b.company_catalogs.companies` endpoint, which searches/returns the **company name**
(`company_flat.business_name`), not the contact person's name. ACL keys live under
`b2b.company-catalogs.*`; the menu entry is in `Config/admin/menu.php`.

**Not implemented (v1):** a "public/default" catalog that restricts *all* customers
(non-assigned customers currently see the full storefront), and ES visibility.

## Publishing

The only things published are the view overrides and sample storage (see `publishables/`):

```bash
php artisan vendor:publish --provider="Webkul\B2BSuite\Providers\B2BSuiteServiceProvider" --force
php artisan optimize:clear
```

Re-run this after editing anything under `publishables/resources/` (the override copies
live in the app's `resources/views/vendor/...`, not the package).

## Commands

- `php artisan b2b-suite:install` — migrate, seed, publish (storage + view overrides), clear caches.

## Conventions

- **Repositories** for all DB access (interfaces in `Contracts/`, never query models directly).
- **Proxies** when type-hinting models across packages.
- **Translations:** add new keys for **all** locales (currently only `en/app.php` exists)
  and verify with `php artisan bagisto:translations:check`. Keep the lang array nesting
  correct — e.g. shop sign-in keys live at `app.shop.sign-in.*` (a direct child of `shop`).
- **Code style:** `vendor/bin/pint` (run from the application root).
- After changing providers/config/routes: `php artisan optimize:clear`.
