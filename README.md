<p align="center">
    <img src="https://bagisto.com/wp-content/themes/bagisto/images/logo.png" />
    <h2 align="center">Open Source B2B Ecommerce Platform</h2>
</p>


<p align="center">
    <img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/bagisto/b2b-suite"> 
    <a href="https://packagist.org/packages/bagisto/b2b-suite"><img src="https://poser.pugx.org/bagisto/b2b-suite/license.svg" alt="License"></a>
</p>

---

### 1. Introduction:

The **Bagisto B2B Ecommerce** is a comprehensive package designed to extend the Bagisto eCommerce platform with advanced Business-to-Business (B2B) capabilities. It introduces powerful **company management features, role-based permissions**, and seamless **company-customer relationships**, enabling businesses to operate in a more structured and professional B2B environment.

**Features include:**

# Bagisto B2B Ecommerce

The **Bagisto B2B Ecommerce** enhances your Bagisto store with advanced Business-to-Business (B2B) features. It enables company-based purchasing, multi-user access, quote negotiation, and procurement management — empowering businesses to handle B2B workflows efficiently within a single platform.

![Bagisto B2B Ecommerce Image](https://github.com/bagisto/temp-media/blob/master/b2b-ecommerce-main-banner.webp)


## Key Features

* **Company Registration** – Companies can easily register from the storefront and manage their organization profile.
* **Company User Management** – Companies can create users under their account and manage access control.
* **Role-Based Permissions** – Assign roles and define permissions for users within the company to restrict access as needed.
* **Purchase Orders (Company End)** – Companies can create and manage purchase orders directly from their dashboard.
* **Requisition Lists** – Companies can add products to requisition lists for bulk purchase and quick ordering.
* **Quick Order** – Add multiple products to the cart quickly using SKUs or CSV upload.
* **Request for Quote (RFQ)** – Companies can submit quote requests directly from the storefront.
* **Frontend Quotation Handling** – Companies can create quotations, negotiate terms, and approve or decline quotes from their dashboard.
* **Quotation Management** – Admin and company users can negotiate, approve, or reject quotes seamlessly.
* **Companies Management (Admin End)** – Admin can create companies and manage from backend.
* **Purchase Orders (Admin End)** – Admins can view and manage all company purchase orders centrally.

![Bagisto B2B Ecommerce Features Image](https://github.com/bagisto/temp-media/blob/master/b2b-ecommerce-feature-list.webp)

## Admin Configuration Options

* **Enable B2B Ecommerce** – Toggle the B2B Ecommerce module on or off globally.
* **Allow Customer to Approve Quote** – Enable or restrict customers from finalizing quote approvals.
* **Procurement Method** – Choose the preferred procurement workflow (e.g., PO-based or direct).
* **Quotation Default Expiration Period** – Define how long a quotation remains valid by default.
* **Quotation Email Template Options** – Manage email templates for quotation communication.
* **Purchase Order Prefix** – Customize the prefix for purchase order numbers.
* **Manage Number of Requisition Lists** – Limit how many requisition lists a company can maintain.

![Bagisto B2B Ecommerce Features Image](https://github.com/bagisto/temp-media/blob/master/b2b-ecommerce-admin-feature-list.webp)

---


### 2. Requirements:

* **Bagisto**: v2.3.x

---

### 3. Installation:

#### Step 1: Install via Composer

```bash
composer require bagisto/b2b-suite:dev-master
```

### 2. Register the Service Provider

In `bootstrap/providers.php`:

> **Note:** Autoloading via Composer’s package auto-discovery is **not possible** for this provider. The registry order matters—`B2BSuiteServiceProvider` must be listed **after** the Shop package or at the end of the providers array. Auto-discovery would load it too early, which can cause issues.

```php
'providers' => [
    Webkul\B2BSuite\Providers\B2BSuiteServiceProvider::class,
],
```

#### Step 3: Run the installation command

```bash
php artisan b2b-suite:install
```

> That’s it! The package is now installed and ready to use in your Bagisto project.

---

### 4. License

The B2B Ecommerce package is open-sourced software licensed under the **MIT License**.

---

### 5. Support

For issues, questions, or contributions, please contact the **[Webkul Team](https://webkul.com/contacts/)**.
