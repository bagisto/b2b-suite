const fs = require("fs");
const path = require("path");

/**
 * Paths shared by the Vite configs, the Tailwind configs and `scripts/`.
 *
 * CommonJS because `tailwind.*.config.js` are CommonJS and can only `require`.
 * The Vite configs must load this with `createRequire`, never a static `import`
 * — see the note in `vite.admin.config.js`.
 */
const packageDir = __dirname;

/**
 * The application root — the first ancestor holding both `artisan` and `public`.
 *
 * Resolved by walking up rather than with a fixed number of `../` segments,
 * because this package is built from several depths: `packages/bagisto/b2b-suite`,
 * `vendor/bagisto/b2b-suite`, and a development clone under
 * `available-extensions/<group>/<project>`.
 *
 * Set `BAGISTO_ROOT` when the package lives outside the application tree.
 */
const appRoot = (() => {
    if (process.env.BAGISTO_ROOT) {
        return path.resolve(process.env.BAGISTO_ROOT);
    }

    let dir = packageDir;

    for (;;) {
        if (
            fs.existsSync(path.join(dir, "artisan"))
            && fs.existsSync(path.join(dir, "public"))
        ) {
            return dir;
        }

        const parent = path.dirname(dir);

        if (parent === dir) {
            throw new Error(
                "b2b-suite: could not locate the Laravel application root above "
                + `"${packageDir}". Set BAGISTO_ROOT to the application path.`
            );
        }

        dir = parent;
    }
})();

/**
 * A core theme package, whose Tailwind theme this package reuses.
 */
const corePackage = (name) => {
    const dir = path.join(appRoot, "packages/Webkul", name);

    if (! fs.existsSync(path.join(dir, "vite.config.js"))) {
        throw new Error(
            `b2b-suite: expected the core ${name} package at "${dir}", but it has `
            + "no vite.config.js. Is BAGISTO_ROOT pointing at a Bagisto install?"
        );
    }

    return dir;
};

module.exports = {
    packageDir,
    appRoot,

    get adminDir() {
        return corePackage("Admin");
    },

    get shopDir() {
        return corePackage("Shop");
    },

    appPublic: path.join(appRoot, "public"),

    /**
     * Build directories, relative to the application's `public/`. Must match
     * `build_directory` in `src/Config/bagisto-vite.php`.
     */
    adminBuildDirectory: "themes/b2b-suite/admin/build",

    shopBuildDirectory: "themes/b2b-suite/shop/build",

    get buildDirectories() {
        return [this.adminBuildDirectory, this.shopBuildDirectory];
    },

    /**
     * The artifact shipped with the package and installed by `vendor:publish`.
     */
    publishablePublicDir: path.join(packageDir, "publishables/public"),
};
