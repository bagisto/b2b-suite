# CHANGELOG for v2.0.0

This changelog consists of the bug & security updates.

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