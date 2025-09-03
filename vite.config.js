import { fileURLToPath } from "url";
import { dirname, resolve } from "path";
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

export default defineConfig({
    plugins: [vue()],
    root: resolve(__dirname, "resources/spa"),
    base: "/spa/",
    build: {
        outDir: resolve(__dirname, "public/spa"),
        emptyOutDir: true, // clears only /public/spa
    },
    resolve: {
        alias: {
            "@": resolve(__dirname, "resources/spa/src"),
        },
    },
});
