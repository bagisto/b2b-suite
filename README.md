<p align="center">
    <img src="https://bagisto.com/wp-content/themes/bagisto/images/logo.png" />
    <h2 align="center">Bagisto B2B Suite</h2>
</p>


<p align="center">
    <img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/bagisto/b2b-suite"> 
    <img alt="License" src="https://img.shields.io/github/license/bagisto/b2b-suite">
    <img alt="Latest Version" src="https://img.shields.io/packagist/v/bagisto/b2b-suite">
    <img alt="Stable Version" src="https://img.shields.io/packagist/v/bagisto/b2b-suite/stable">
</p>

---

### 1. Introduction:

The **Bagisto B2B Suite** is a comprehensive package designed to extend the Bagisto eCommerce platform with advanced Business-to-Business (B2B) capabilities. It introduces powerful **company management features, role-based permissions**, and seamless **company-customer relationships**, enabling businesses to operate in a more structured and professional B2B environment.

**Features include:**

* **Company Management** – Create, edit, view, and manage companies.
* **Company-Customer Relations** – Assign and manage customers under companies.
* **Admin Interface** – Integrated admin panel with DataGrid support.
* **Permission Management** – Role-based access control for company users.
* **Multi-language Support** – Fully localization-ready.
* **Database Migrations** – Automated database setup for smooth installation.
* **Seeders** – Sample data for quick setup and testing.
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

The B2B Suite package is open-sourced software licensed under the **MIT License**.

---

### 5. Support

For issues, questions, or contributions, please contact the **[Webkul Team](https://webkul.com/contacts/)**.
