import { fileURLToPath } from "url";
import path from "path";
import { cpSync, rmSync, mkdirSync, existsSync } from "fs";

/**
 * Copies the freshly built admin & shop theme bundles (theme + B2B views) from
 * the app's `public/themes/.../build` into this package's `publishables/public`,
 * so they ship with the package and `vendor:publish` can drop them into an
 * install's public folder (no Node/Tailwind build needed at install time).
 *
 * Run via `npm run publishables` (which builds first, then runs this).
 */
const here = path.dirname(fileURLToPath(import.meta.url));
const pkgRoot = path.resolve(here, "..");
const appPublic = path.resolve(pkgRoot, "../../../public");

const builds = [
    "themes/admin/default/build",
    "themes/shop/default/build",
];

for (const build of builds) {
    const src = path.join(appPublic, build);
    const dest = path.join(pkgRoot, "publishables/public", build);

    if (! existsSync(src)) {
        console.error(`✗ missing build: ${src}\n  run the theme build first (npm run build).`);
        process.exit(1);
    }

    rmSync(dest, { recursive: true, force: true });
    mkdirSync(dest, { recursive: true });
    cpSync(src, dest, { recursive: true });

    console.log(`✓ ${build} → publishables/public/${build}`);
}
