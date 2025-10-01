import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import path from "path";

export default defineConfig(({ mode }) => {
    const envDir = "../../../";

    Object.assign(process.env, loadEnv(mode, envDir));

    return {
        build: {
            emptyOutDir: true,
        },

        envDir,

        server: {
            host: process.env.VITE_HOST || "localhost",
            port: process.env.VITE_PORT || 5173,
            cors: true,
        },

        plugins: [
            vue(),

            laravel({
                hotFile: "../../../public/b2b-suite-vite.hot",
                publicDirectory: "../../../public",
                buildDirectory: "themes/b2b-suite/build",
                input: [
                    // "src/Resources/assets/css/admin.css",
                    // "src/Resources/assets/css/shop.css",
                    "src/Resources/assets/js/app.js",
                ],
                refresh: true,
            }),
        ],

        experimental: {
            renderBuiltUrl(filename, { hostId, hostType, type }) {
                if (hostType === "css") {
                    return path.basename(filename);
                }
            },
        },
    };
});
