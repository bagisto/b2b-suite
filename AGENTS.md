# AGENTS.md — Bagisto B2B Suite

Guidance for AI agents (and humans) working inside the `bagisto/b2b-suite` package.
This file describes how the package is wired into Bagisto and the conventions you must
follow when changing it.

## Overview

B2B Suite extends Bagisto's storefront and admin with company accounts, company users
and roles, requisition lists, quick order, quotations (RFQ), purchase orders and
company catalogs (per-company product **and category** visibility, custom pricing and
quantity-tier/volume pricing).

- **Namespace:** `Webkul\B2BSuite` → `src/`
- **Installed path:** `vendor/bagisto/b2b-suite` (via `composer require` — see *Installation
  & registration*). This repo is a **development checkout** at `packages/bagisto/b2b-suite`,
  symlinked into `vendor/` by the root `packages/*/*` path repository.
- **PHP:** 8.1+ (per `composer.json`); developed against **Bagisto 2.4 / Laravel 12**. Blade
  views are styled via the core Shop/Admin themes, which the package rebuilds into its own
  bundles (see *Styling* below).

## Installation & registration

**Installing the package — the [README](./README.md) is canonical:**

1. `composer require bagisto/b2b-suite` (installs into `vendor/bagisto/b2b-suite`).
2. Register `Webkul\B2BSuite\Providers\B2BSuiteServiceProvider` in `bootstrap/providers.php`,
   **after the Shop package** (or last in the array). Composer auto-discovery is intentionally
   **disabled** — discovery would load the provider too early, before Shop.
3. `php artisan b2b-suite:install` (migrate, seed, publish assets/overrides, clear caches).

**Development checkout (this repo):** instead of a registry install, the package lives at
`packages/bagisto/b2b-suite` and is wired via the root `composer.json` path repository
(`"type": "path", "url": "packages/*/*"` + `"bagisto/b2b-suite": "@dev"`), which symlinks it
into `vendor/bagisto/b2b-suite`. Provider registration in `bootstrap/providers.php` is the same.
Do not confuse this dev layout with the install steps above.

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

## Styling — the package builds its own theme bundles

B2B Blade views are styled with the core **Shop/Admin Tailwind themes**, but they live
outside those themes' `src/Resources/**`, so the core builds don't scan them. Rather than
editing the core theme configs, the package ships **its own build** that regenerates each
theme's bundle with the B2B views folded in — a single coherent Tailwind pass (correct
layer order, no second stylesheet) and **no changes to the core Shop/Admin packages**.

How it works (all in the package root):

- `tailwind.{admin,shop}.config.js` — reuse the core theme's Tailwind config (theme tokens,
  plugins, safelist, `darkMode`) and add the B2B views to `content` (absolute paths, so the
  build is cwd-independent).
- `vite.{admin,shop}.config.js` — `import` the core theme's *own* Vite config and override
  only PostCSS to use the config above; output goes to the theme's own
  `public/themes/{admin,shop}/default/build`.
- Core themes are resolved at `<app-root>/packages/Webkul/<theme>`, so the build works
  whether this package sits in `packages/bagisto/b2b-suite` (source) or
  `vendor/bagisto/b2b-suite` (installed).

Commands (run from the package). The build reuses the core themes' `node_modules`, so make
sure `npm install` has been run in `packages/Webkul/{Shop,Admin}` first:

```bash
npm install
npm run build          # admin + shop → straight into public/themes/.../build
npm run build:admin    # one theme only
npm run build:shop
npm run dev:admin      # hot-reload while developing
npm run dev:shop
```

**Prebuilt bundles ship with the package**, so a normal install needs **no Node/Tailwind
build**: `npm run publishables` (maintainer) rebuilds both and copies them into
`publishables/public/`, and `b2b-suite:install` publishes them into `public/`. Only rebuild
if you change a B2B view (new utility class) or the core theme changes and you spot breakage.

Do **not** add a second global stylesheet — loading another Tailwind utility sheet after
the core one lets its plain utilities override core's responsive variants (this previously
broke the responsive flash toasts and the admin sidebar layout). For one-off rules, prefer
a scoped `@push('styles')` block within the view.

### Vue inside Blade

B2B interactive views register inline components (`app.component('v-…', { template:
'#…-template' })`) inside `@pushOnce('scripts')`. **Put the component's markup in its own
`<script type="text/x-template">` — do not pass it as slotted content to the component.**
Slot content compiles in the *parent* (root app) scope, so the component's `data()` is not
in scope and bindings like `v-if="items.length"` throw on `undefined`. (This bit the shared
catalog form once.) Blade/`@lang`/`{{ route() }}` inside `<script>` produce false-positive
IDE diagnostics — ignore them.

**Gotchas that have bitten this package — follow these:**

- **Vue `@event` bindings collide with Blade directives.** In a `.blade.php` file an
  `@error="…"`, `@empty="…"`, `@checked`, `@selected`, `@class`, `@style` Vue binding is
  parsed by Blade as the *directive* of that name, producing broken compiled PHP. `@error`,
  for example, opens an `@error … @enderror` conditional that never closes →
  `syntax error, unexpected end of file, expecting "endif"` **at render time** (the page
  500s, not the build). **Always use the long form** — `v-on:error`, `v-on:empty`, … — for
  any Vue event whose name matches a Blade directive. `@click`, `@change`, `@input`,
  `@submit` are safe (no Blade twin).
- **`php artisan view:cache` is NOT a syntax check.** It writes the compiled file and reports
  *"Blade templates cached successfully"* even when that PHP has a parse error — the error
  only fires when the view renders. To actually verify a view, lint the compiled output:

  ```bash
  php artisan view:clear && php artisan view:cache
  for f in storage/framework/views/*.php; do php -l "$f" | grep -v 'No syntax errors'; done
  ```

  (Pre-existing breakage in core's `components/example.blade.php` is unrelated — ignore it.)
- **Verify Tailwind classes against the built bundle, not from memory.** The B2B theme
  purges, so a utility — or a responsive variant such as `max-md:flex-col`, or `h-80`,
  `pl-10`, `text-blue-900` — used in a B2B view but absent from the compiled CSS *silently
  does nothing*. Check it against `public/themes/<theme>/default/build/assets/app-*.css`
  (resolve the actual file via `manifest.json`) before relying on it, or express the rule in
  a scoped `@push('styles')` block / inline `style="…"`.
- **Comment style in these views.** Use multi-line JSDoc blocks (`/** … */`, one ` * ` per
  line, sentences capitalised and punctuated) for *both* the Vue component JS and the
  `@push('styles')` CSS — keep one consistent comment style across the whole view.
- **Indentation inside `<script type="text/x-template">` is not auto-fixed.** `blade-formatter`
  (and most HTML formatters) treat a script-template's body as opaque and won't re-indent its
  block structure or wrap inline-element attributes; reflow such templates carefully by hand.

## Company Catalog

Company catalogs: assign a catalog to companies to control **which products
their members can see/buy (allowlist)** and **what prices they pay**. The design reuses
Bagisto's existing customer-group price index instead of touching the core indexer.

**Core idea — each catalog is backed by a hidden customer group.**

- `CompanyCatalog` (`b2b_company_catalogs`) holds `name`, `description`, `status`, and a
  `customer_group_id` pointing at a dedicated group (`code = company_catalog_<id>`,
  `is_user_defined = 0`) created on first save by `Helpers/CompanyCatalog::provisionGroup()`.
- `b2b_company_catalog_products` is the visibility **allowlist** (catalog ↔ product).
- `b2b_company_catalog_categories` is a **derived** category allowlist (catalog ↔ category),
  recomputed on every save (`deriveCategories()`) from the assigned products' categories plus
  their NestedSet ancestors — it drives storefront category visibility.
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

- Per-leaf **fixed or percentage-discount** prices are written to
  `product_customer_group_prices` for the catalog group, then `UpdateCreatePriceIndex` is
  dispatched for the affected products. The existing price index serves catalog prices
  automatically. "Leaf" = the price-bearing product: a simple/virtual product itself, or each
  variant / associated / bundle child of a composite (configurable / grouped / bundle);
  booking is visibility-only. Reindexing every catalog product (even unpriced ones) ensures
  each has a price-index row for the group — the admin form posts a `prices[ID]` entry for
  every assigned leaf so this happens.
- Reindex timing depends on `QUEUE_CONNECTION`: `sync` runs inline; otherwise a queue worker
  must run for prices to appear.
- **`setPrices()` is destructive — it wipes the whole group first.** It runs
  `delete where customer_group_id = <group>` and then rewrites only the rows in the posted
  payload, so calling it with a *partial* payload (or testing it in tinker against a real
  catalog) **erases every other override for that catalog**. The admin form guards this by
  posting a `prices[ID]` entry for every assigned leaf; never call it with a subset. If you
  ever need to test it, use a throwaway catalog — and note that `product_price_indices`
  retains the last effective price per `(customer_group_id, product_id)`, which is the only
  way deleted overrides can be recovered.
- **Tier (volume) pricing rides the same table.** Each price break is another
  `product_customer_group_prices` row keyed by `qty` (qty 1 = base catalog price, qty > 1 =
  volume breaks); core's `AbstractType::getCustomerGroupPrice($product, $qty)` resolves the
  right tier in cart and `getCustomerGroupPricingOffers()` renders the PDP break table — no
  custom storefront code. Core only applies a tier when it is *cheaper* than the base/earlier
  tiers, so break prices must descend as qty rises.

**Storefront visibility (allowlist)** — `Repositories/ProductRepository` (extends core,
bound in `B2BSuiteManager`):

- Resolves the current customer's catalog via group id; if present, pushes a Prettus
  `CompanyCatalogVisibilityCriteria` (a `whereIn('products.id', <catalog products>)` subquery)
  so listing/search/`getMaxPrice` are restricted. `findBySlug` is guarded for the PDP.
- **Inert for guests, admins, and unassigned customers** (no catalog → no criterion).
- The cart-add guard lives in the shop API `CartController::isWithinCompanyCatalog()`.
- The DB path is covered; an **Elasticsearch** storefront would need an equivalent ES filter.

**Storefront category visibility** — `Repositories/CategoryRepository` (extends core, bound in
`B2BSuiteManager`):

- For a catalog customer it restricts the category tree/menu (`getVisibleCategoryTree`) to the
  catalog's allowed category ids and returns `null` from `findBySlug` for disallowed categories
  (→ 404), so the storefront nav and category pages match the product allowlist.
- Gated to **storefront requests only** via `isAdminRequest()` (admin requests are inert), and
  inert for guests / unassigned customers.

**Admin UI:** `Http/Controllers/Admin/CompanyCatalogController`, `DataGrids/Admin/
CompanyCatalogDataGrid`, views under `Resources/views/admin/company-catalogs/`
(`index`, `create`, `edit`, shared `form`). The form's product picker uses the dedicated
`admin.b2b.company_catalogs.products` endpoint (restricted to `visible_individually`
products, with a type filter); a composite's priceable children/tiers come from
`admin.b2b.company_catalogs.product_children`, and the save dialog previews the derived
category tree via `admin.b2b.company_catalogs.category_preview`. The company picker uses
`admin.b2b.company_catalogs.companies`, which searches/returns the **company name**
(`b2b_company_flat.business_name`), not the contact person's name. ACL keys live under
`b2b.company-catalogs.*`; the menu entry is in `Config/admin/menu.php`.

**Not implemented (v1):** a "public/default" catalog that restricts *all* customers
(non-assigned customers currently see the full storefront), and ES visibility.

## Publishing

Everything published lives under `publishables/` (see that section above) and is published
by the provider:

- `publishables/resources/vendor` → `resources/views/vendor` — view/component overrides
- `publishables/storage` → `storage/app/public` — sample data
- `publishables/public/themes/{admin,shop}/default/build` → the matching `public/` build
  dirs — the **prebuilt theme bundles** (scoped to those two folders; the rest of `public/`
  is never touched)

```bash
php artisan vendor:publish --provider="Webkul\B2BSuite\Providers\B2BSuiteServiceProvider" --force
php artisan optimize:clear
```

Re-run this after editing anything under `publishables/resources/`. After rebuilding the
theme bundles, refresh `publishables/public/` with `npm run publishables` and re-publish.

## Commands

- `php artisan b2b-suite:install` — migrate, seed, publish (storage + view overrides), clear caches.

## Conventions

### Data access

- **Repositories for ALL database access** — no `DB` facade and no direct
  model / `Proxy::modelClass()::query()` calls in controllers, helpers, listeners or
  datagrids. Inject the repository and use `find` / `findWhere` / `findWhereIn` /
  `findOneByField` / `create` / `update` / `deleteWhere`. For queries Prettus can't express
  (joins, `lockForUpdate`, search+paginate), add a method to the repository — use
  `$this->model->…` *inside the repository* rather than reaching for the facade in the caller.
  Mass updates the repo can't do in one statement are looped via `$repo->update($attrs, $id)`.
- **Allowed facades** (not data access, no repository equivalent): `DB::transaction()` for
  atomicity, `Schema::` for table/column metadata.
- **Proxies** are only for cross-package model type-hints — relationship definitions in models
  (`belongsTo(CustomerProxy::modelClass())`). Never use a proxy to run a query.

### Tables

- Every package-owned table is prefixed **`b2b_`** (e.g. `b2b_company_catalogs`). Core tables
  (`customers`, `cart`, …) keep their names — the package only adds columns to them.
- Prefix the table **everywhere** it appears: model `$table`, FK `->on()`, `DB::raw` SQL, and
  validation rules (`exists:b2b_…`, `unique:b2b_…`). Quote-anchored find/replace misses
  table names that sit mid-string in raw SQL / rules — grep for bare names after a rename.
- Every concrete model declares an explicit `protected $table = 'b2b_…'`. A model that maps a
  core table (e.g. `Customer` extends the core customer) declares none and inherits it.

### Class member ordering

- **Constructors** — repository dependencies first (primary-entity repo first, then
  supporting repos), then helpers / managers / services.
- **Controllers** — RESTful lifecycle (`index, create, store, show/view, edit, update,
  <update-variants>, destroy`) → mass actions (`mass*`) → other public / AJAX endpoints →
  `protected`/`private` helpers last.
- **Models** (Laravel-standard) — **constants** (each with its own docblock) → **Laravel
  properties** (`$table, $fillable/$guarded, $casts, $timestamps`) → **extra / trait
  properties** (`$translatedAttributes, $statusLabel, …`) → **methods** (relationships →
  accessors/mutators → Eloquent overrides → instance helpers → `static` helpers). All
  properties and constants come before any method.
- **Listeners / helpers** — public event-handlers / API first, `protected` helpers last.

### Docblocks & comments

- Every method, property and constant has a docblock with a one-line description ending in
  proper punctuation (a period). `{@inheritdoc}` is fine as-is.
- Use `/** … */` block comments — not `//` line comments — for inline explanations.

### Events

- CRUD mutations dispatch symmetric `*.before` / `*.after` events; keep `destroy` /
  `massDestroy` (and shop/admin siblings) consistent with one another.

### Migrations

- One `create_*` migration per table (no separate `add_*_column` migrations for package
  tables — fold new columns into the create). Column additions to **core** tables stay as
  `add_columns_to_<core table>` and run last. Keep a logical, dependency-safe, domain-grouped
  order with `b2b_`-prefixed filenames and table names.
- Give FKs explicit short index names when the auto name
  (`<db-prefix><table>_<col>_foreign`) would exceed MySQL's 64-char limit, and pass the
  explicit `table:` to `foreignId()->constrained()` (it infers the wrong table after the
  `b2b_` rename).

### Seeders

- Clean with `delete()` (which respects `ON DELETE CASCADE`), **not** `truncate()`, so no
  `FOREIGN_KEY_CHECKS` toggling is needed. Order seeders parent-first; each seeder cleans then
  inserts and is idempotent. The suite is enabled by default on install
  (`CoreConfigTableSeeder` sets `b2b.general.settings.active`).

### Routes

- Each route file is **self-contained** — it declares its own `Route::group([...])` with the
  middleware + prefix it owns. `admin-routes.php` wraps its groups in
  `['admin', NoCacheMiddleware]` + prefix `config('app.admin_url').'/b2b'`; `shop-routes.php`
  wraps the account groups in `['theme','locale','currency','customer','customer_bouncer',
  NoCacheMiddleware]` + prefix `customer/account`. `web.php` only `require`s the two files
  (no wrapping group of its own).
- Order the route groups to match the **admin / account menu order**, so routes, menu and ACL
  read in the same sequence.
- `/** … */` block comments, one per group.

### Blade & views

- **Tags with 2+ attributes are multiline** (match core): `<tag` alone on the first line, each
  attribute on its own line indented +4, the closing `>` / `/>` on its own line. 0–1 attribute
  stays inline. Preserve Vue / `@` / `:` / `x-slot` bindings exactly — change only whitespace.
- **Comments:** short label/heading comments are Title Case (`<!-- Action Buttons -->`);
  full-sentence comments are capitalised and end with a period. Applies to `<!-- -->`,
  `{{-- --}}`, and the `/** … */` JS/CSS blocks.
- **Folder layout mirrors routes/features** (`admin/<feature>/…`,
  `shop/customers/account/<feature>/…`); a feature's partials live under `partials/`.
- A **shop** view uses `x-shop::*` components, an **admin** view `x-admin::*` — never mix them.
- Verify a view compiles by **linting the compiled output** (see *Gotchas*), not just `view:cache`.

### Translations & lang files

- **All 22 Bagisto locales** exist (`ar, bn, ca, de, en, es, fa, fr, he, hi_IN, id, it, ja,
  nl, pl, pt_BR, ro, ru, sin, tr, uk, zh_CN`). `en/app.php` is the canonical structure; every
  other locale must have the **identical key set and order** — only values translated.
  Preserve `:placeholder` tokens and brand terms (B2B Suite, Bagisto, SKU, Google Analytics ID,
  social networks) across all locales.
- **File structure (systematic, like core):** top-level `admin → shop → emails → seeders →
  commands`. Within `admin` / `shop`: `acl → layouts → <menu-order features> → configuration`
  (configuration last; shop has none). Within a feature: RESTful groups (`index → create →
  edit → view`) then flash/validation messages. **Leaf keys sorted alphabetically.**
- **No dead keys.** A key is live only if `b2b::app.<path>` — or any parent prefix, for dynamic
  `'b2b::app.….'.$var` access — appears in `src/` or `publishables/`; audit before removing.
- After any change run `php artisan bagisto:translations:check` (must report all synced).

### Dead code

- No redundant overrides that only call `parent::…`, and no unused methods / relationships —
  remove them (verify with a repo-wide grep first).

### General

- **Translations:** see *Translations & lang files* above — every change must keep all 22
  locales in sync and pass `php artisan bagisto:translations:check`. Mind the array nesting
  (e.g. shop sign-in keys live at `app.shop.sign-in.*`, a direct child of `shop`).
- **Code style:** `vendor/bin/pint` (run from the application root).
- After changing providers/config/routes: `php artisan optimize:clear`.
