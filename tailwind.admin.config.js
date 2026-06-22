const path = require("path");

/**
 * Admin theme Tailwind config + B2B admin views.
 *
 * Resolves the core Admin package at `<app-root>/packages/Webkul/Admin` — which
 * holds whether this package lives in `packages/bagisto/b2b-suite` (source) or
 * `vendor/bagisto/b2b-suite` (installed), since both are 3 levels under the app
 * root. All `content` paths are ABSOLUTE so the build works from any cwd.
 */
const adminDir = path.resolve(__dirname, "../../../packages/Webkul/Admin");
const admin = require(path.join(adminDir, "tailwind.config.js"));

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        path.join(adminDir, "src/Resources/**/*.blade.php"),
        path.join(adminDir, "src/Resources/**/*.js"),

        path.join(__dirname, "src/Resources/views/admin/**/*.blade.php"),
        path.join(__dirname, "src/Resources/views/components/**/*.blade.php"),
        path.join(__dirname, "publishables/resources/vendor/admin/**/*.blade.php"),
    ],

    theme: admin.theme,
    plugins: admin.plugins,
    safelist: admin.safelist,
    darkMode: admin.darkMode,
};
