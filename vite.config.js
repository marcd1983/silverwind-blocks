import { defineConfig } from 'vite';

// Builds client/dist with stable filenames (referenced from PHP via
// Requirements). Alpine is not bundled: the Silverwind theme provides
// window.Alpine and starts it after these scripts have registered their
// components. Swiper is split into its own chunk and only downloaded on
// pages that have a slider or carousel.
export default defineConfig({
  publicDir: false,
  base: './',
  build: {
    outDir: 'client/dist',
    emptyOutDir: true,
    modulePreload: false,
    rollupOptions: {
      input: {
        'silverwind-blocks': 'client/src/silverwind-blocks.js',
        'silverwind-blocks-css': 'client/src/silverwind-blocks.css',
      },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
        assetFileNames: (asset) =>
          asset.names?.[0]?.endsWith('.css') ? 'silverwind-blocks[extname]' : 'assets/[name][extname]',
      },
    },
  },
});
