const path = require("path");

/**
 * Shop theme Tailwind config + B2B storefront views.
 *
 * Resolves the core Shop package at `<app-root>/packages/Webkul/Shop` — which
 * holds whether this package lives in `packages/bagisto/b2b-suite` (source) or
 * `vendor/bagisto/b2b-suite` (installed), since both are 3 levels under the app
 * root. All `content` paths are ABSOLUTE so the build works from any cwd.
 */
const shopDir = path.resolve(__dirname, "../../../packages/Webkul/Shop");
const shop = require(path.join(shopDir, "tailwind.config.js"));

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        path.join(shopDir, "src/Resources/**/*.blade.php"),
        path.join(shopDir, "src/Resources/**/*.js"),

        path.join(__dirname, "src/Resources/views/shop/**/*.blade.php"),
        path.join(__dirname, "src/Resources/views/components/**/*.blade.php"),
        path.join(__dirname, "publishables/resources/vendor/shop/**/*.blade.php"),
    ],

    theme: shop.theme,
    plugins: shop.plugins,
    safelist: shop.safelist,
    darkMode: shop.darkMode,
};
