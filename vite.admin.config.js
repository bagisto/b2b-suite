import path from "path";
import { fileURLToPath } from "url";
import tailwindcss from "tailwindcss";
import autoprefixer from "autoprefixer";

/**
 * B2B Suite — admin build.
 *
 * Reuses the Admin theme's own Vite config (Vue plugin, inputs, output dir) and
 * its Tailwind theme, and only overrides PostCSS so Tailwind ALSO scans B2B's
 * admin views. The result is the regular admin bundle with B2B classes folded in
 * — a single Tailwind pass (no second stylesheet, correct layer order).
 *
 * Run from this package via `vite --root <admin> --config <this file>` (the
 * package.json `build:admin` script does this) — works whether the package is in
 * `packages/bagisto/b2b-suite` (source) or `vendor/bagisto/b2b-suite` (installed).
 *
 * Nothing in the Admin package is modified — its config is imported, not edited.
 */
import adminConfigFactory from "../../../packages/Webkul/Admin/vite.config.js";

const moduleDir = path.dirname(fileURLToPath(import.meta.url));

export default (env) => {
    const config = adminConfigFactory(env);

    /**
     * Tailwind: admin theme preset + B2B admin views (config loaded via Tailwind's
     * own resolver, which handles the CommonJS config under this `type: module`).
     */
    config.css = {
        postcss: {
            plugins: [
                tailwindcss(path.join(moduleDir, "tailwind.admin.config.js")),
                autoprefixer(),
            ],
        },
    };

    return config;
};
