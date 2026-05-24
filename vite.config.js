import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import { resolve } from "path";

export default defineConfig({
    plugins: [tailwindcss()],
    build: {
        outDir: "assets/dist",
        emptyOutDir: true,
        lib: {
            entry: resolve(__dirname, "resources/js/plugin.js"),
            name: "PluginBasicAuth",
            formats: ["iife"],
            fileName: () => "plugin.js",
        },
        rollupOptions: {
            output: {
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name && assetInfo.name.endsWith(".css")) {
                        return "plugin.css";
                    }
                    return "[name].[ext]";
                },
            },
        },
    },
});