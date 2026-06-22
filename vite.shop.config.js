import path from "path";
import { fileURLToPath } from "url";
import tailwindcss from "tailwindcss";
import autoprefixer from "autoprefixer";

/**
 * B2B Suite — shop build.
 *
 * Reuses the Shop theme's own Vite config (Vue plugin, inputs, output dir) and
 * its Tailwind theme, and only overrides PostCSS so Tailwind ALSO scans B2B's
 * storefront views. The result is the regular shop bundle with B2B classes folded
 * in — a single Tailwind pass (no second stylesheet, correct layer order).
 *
 * Run from this package via `vite --root <shop> --config <this file>` (the
 * package.json `build:shop` script does this) — works whether the package is in
 * `packages/bagisto/b2b-suite` (source) or `vendor/bagisto/b2b-suite` (installed).
 *
 * Nothing in the Shop package is modified — its config is imported, not edited.
 */
import shopConfigFactory from "../../../packages/Webkul/Shop/vite.config.js";

const moduleDir = path.dirname(fileURLToPath(import.meta.url));

export default (env) => {
    const config = shopConfigFactory(env);

    /**
     * Tailwind: shop theme preset + B2B storefront views (config loaded via
     * Tailwind's own resolver, which handles the CommonJS config under `type: module`).
     */
    config.css = {
        postcss: {
            plugins: [
                tailwindcss(path.join(moduleDir, "tailwind.shop.config.js")),
                autoprefixer(),
            ],
        },
    };

    return config;
};
