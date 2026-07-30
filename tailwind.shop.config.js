const path = require("path");
const { shopDir, packageDir } = require("./paths.cjs");

/**
 * B2B Suite — storefront Tailwind config.
 *
 * Scans only this package's storefront views; the core shop theme generates its
 * own utilities in its own bundle.
 */

/**
 * The core shop theme's config. `theme`, `plugins`, `safelist` and `darkMode` are
 * reused verbatim so a token resolves to the same value in a B2B view as in a core
 * view.
 */
const shop = require(path.join(shopDir, "tailwind.config.js"));

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        path.join(packageDir, "src/Resources/views/shop/**/*.blade.php"),
        path.join(packageDir, "src/Resources/views/components/**/*.blade.php"),
        path.join(packageDir, "publishables/resources/vendor/shop/**/*.blade.php"),
    ],

    /**
     * The core bundle already emits the reset; emitting it again would re-apply
     * base element styles over it.
     */
    corePlugins: {
        preflight: false,
    },

    theme: shop.theme,
    plugins: shop.plugins,
    safelist: shop.safelist,
    darkMode: shop.darkMode,
};
