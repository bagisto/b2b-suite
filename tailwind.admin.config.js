const path = require("path");
const { adminDir, packageDir } = require("./paths.cjs");

/**
 * B2B Suite — admin Tailwind config.
 *
 * Scans only this package's admin views; the core admin theme generates its own
 * utilities in its own bundle.
 */

/**
 * The core admin theme's config. `theme`, `plugins`, `safelist` and `darkMode`
 * are reused verbatim so a token resolves to the same value in a B2B view as in a
 * core view.
 */
const admin = require(path.join(adminDir, "tailwind.config.js"));

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        path.join(packageDir, "src/Resources/views/admin/**/*.blade.php"),
        path.join(packageDir, "src/Resources/views/components/**/*.blade.php"),
        path.join(packageDir, "publishables/resources/vendor/admin/**/*.blade.php"),
    ],

    /**
     * The core bundle already emits the reset; emitting it again would re-apply
     * base element styles over it.
     */
    corePlugins: {
        preflight: false,
    },

    theme: admin.theme,
    plugins: admin.plugins,
    safelist: admin.safelist,
    darkMode: admin.darkMode,
};
