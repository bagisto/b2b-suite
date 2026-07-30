import path from "path";
import { cpSync, rmSync, mkdirSync, existsSync } from "fs";
import paths from "../paths.cjs";

/**
 * Copies the built bundles into `publishables/public`, so they ship with the
 * package and an install needs no Node/Tailwind build.
 *
 * Run via `npm run publishables`, which builds first.
 */
for (const build of paths.buildDirectories) {
    const src = path.join(paths.appPublic, build);
    const dest = path.join(paths.publishablePublicDir, build);

    if (! existsSync(src)) {
        console.error(`✗ missing build: ${src}\n  run the theme build first (npm run build).`);
        process.exit(1);
    }

    rmSync(dest, { recursive: true, force: true });
    mkdirSync(dest, { recursive: true });
    cpSync(src, dest, { recursive: true });

    console.log(`✓ ${build} → publishables/public/${build}`);
}
