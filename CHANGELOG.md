# CHANGELOG for v2.0.0

This changelog consists of the bug & security updates.

## **v2.1.0 (6th of August 2026)** - *Release*

- Compatibility with Bagisto v2.4.9.

- Third-level admin menus open again. The suite replaces Bagisto's menu service, and its copy did not expose the current menu key introduced in v2.4.9, so no menu item was ever marked active — affecting Bagisto's own sections such as Settings → Taxes and Marketing → Communications as well as the B2B ones.

- The admin and storefront styles now ship as the suite's own bundle under `public/themes/b2b-suite`, instead of regenerating and replacing Bagisto's theme bundles. Upgrading Bagisto no longer reverts the suite's styling, and the published assets drop from 5.2 MB to under 100 KB.

- Datagrid headers across the suite share one style definition with their loading skeletons, so the placeholder matches the table it stands in for. Grids whose last column holds only row actions no longer show a heading there while loading.

- The asset build no longer assumes a fixed directory depth, so it runs unchanged from a development clone, a `packages/` checkout, or an installed `vendor/` package. Set `BAGISTO_ROOT` when the package lives outside the application tree.

- The PHP requirement now matches Bagisto's own (`>=8.3 <8.5`), instead of advertising support for 8.1 and 8.2, which Bagisto does not install on.

## **v2.0.2 (8th of July 2026)** - *Release*

- Compatibility with Bagisto v2.4.8.

- Added the prebuilt admin and shop theme bundles.

- Company onboarding e-mails are consolidated so a new member receives a single onboarding mail alongside the registration mail, instead of multiple overlapping messages.

## **v2.0.1 (22nd of June 2026)** - *Release*

- The sales order behind a company purchase order can now be opened by any member of that company via the "View Order" link, guarded by the purchase-orders permission; previously only the member who placed it could view it.

- The quote / purchase-order chat block is hidden in full for members who do not have the quote messaging permission, rather than only hiding the message input.

- Demo purchase orders now place sales orders whose line items are linked back to their quote items, so completing the sales order (invoice and shipment) correctly marks the linked purchase order, and its line items, as completed.

## **v2.0.0 (22nd of June 2026)** - *Release*

- Initial release of the Bagisto B2B Suite for Bagisto v2.4.x (Laravel 12, PHP 8.3+).

- Company accounts: storefront registration with admin approval, company users, and role-based permissions.

- Procurement workflows: requisition lists, quick order (SKU / CSV upload), request for quote (RFQ) with buyer–seller negotiation, and purchase orders on both the storefront and admin.

- Company catalogs: per-company product and category visibility with custom and quantity-tier pricing.

- Company credit (Pay By Credit): per-company credit limits with an audited ledger (allocations, limit changes, purchases, reimbursements, refunds and reversals) and admin reimbursement; reducing a limit below the outstanding balance now records a ledger entry and warns the admin.

- The company-credit link is hidden from the storefront account menu when the credit feature is disabled.

- Full right-to-left (RTL) support across the admin and storefront views.

- Translations for all 22 supported locales.