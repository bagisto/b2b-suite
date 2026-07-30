<?php

/**
 * Viters for `@bagistoVite([...], '<namespace>')`, merged into
 * `bagisto-vite.viters` by the service provider.
 *
 * Two bundles rather than one because the core Admin and Shop themes have
 * different Tailwind presets, so their utilities cannot be generated in a single
 * pass. `build_directory` must match `paths.cjs`; Laravel resolves `hot_file`
 * relative to `public/`.
 */
return [
    'b2b-suite-admin' => [
        'hot_file' => 'b2b-suite-admin-vite.hot',
        'build_directory' => 'themes/b2b-suite/admin/build',
        'package_assets_directory' => 'src/Resources/assets',
    ],

    'b2b-suite-shop' => [
        'hot_file' => 'b2b-suite-shop-vite.hot',
        'build_directory' => 'themes/b2b-suite/shop/build',
        'package_assets_directory' => 'src/Resources/assets',
    ],
];
