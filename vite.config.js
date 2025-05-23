// vite.config.js
import { defineConfig } from "vite";
import path from "path";
import { glob } from "glob";
import lightningcss from "vite-plugin-lightningcss";
import { generateWebfontPlugin } from "./build/vite-plugin-webfont"; // Import du plugin


export default defineConfig({

  base: process.env.NODE_ENV === "development" ? "/" : "./",
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "src/assets"),
      "node_modules": path.resolve(__dirname, "node_modules"),
    },
  },
  css: {
    devSourcemap: true,
    preprocessorOptions: {
      scss: {
        silenceDeprecations: ["import", "mixed-decls", "color-functions", "global-builtin", "legacy-js-api"],
      },
    },
  },
  assetsInclude: ["**/*.svg", "**/*.png", "**/*.jpg", "**/*.woff", "**/*.woff2"],
  plugins: [
    {
      handleHotUpdate({ file, server }) {
        if (file.endsWith(".php")) {
          server.ws.send({ type: "full-reload", path: "*" });
        }
      },
    },
    lightningcss({ minify: true }),
    generateWebfontPlugin(),
  ],
  build: {
    cssCodeSplit: true,
    cssMinify: "lightningcss",
    manifest: true,
    outDir: path.resolve(__dirname, "dist/assets"),
    assetsInlineLimit: 0,
    rollupOptions: {
      input: {
        "js/main": path.resolve(__dirname, "src/assets/js/main.js"),
        ...glob
        .sync(path.resolve(__dirname, "src/assets/scss/[!_]*.scss"))
        .reduce((entries, filename) => {
          const [, name] = filename.match(/([^/]+)\.scss$/);
          return { ...entries, [name]: filename };
        }, {}),
      },
      output: {
        entryFileNames: "[name]-[hash].js",
        chunkFileNames: "[name]-[hash].js",
        assetFileNames: (assetInfo) => {
          const ext = path.extname(assetInfo.name).slice(1);
          if (["woff", "woff2", "ttf"].includes(ext)) return `fonts/[name]-[hash].[ext]`;
          if (["gif", "jpg", "jpeg", "png"].includes(ext)) return `img/[name]-[hash].[ext]`;
          if (["svg"].includes(ext)) return `img/svg/[name]-[hash].[ext]`;
          return `[ext]/[name]-[hash].[ext]`;
        },
      },
    },
  },

  server: {
    cors: { origin: "*" },
    host: true,
    strictPort: true,
    port: 5173,
    origin: "http://localhost:5173",
  },
});
