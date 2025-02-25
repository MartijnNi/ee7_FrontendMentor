import { defineConfig } from 'vite';
import path from 'path';
import fs from 'fs';
import pug from '@vituum/vite-plugin-pug';
import VitePluginBrowserSync from 'vite-plugin-browser-sync';
import { faviconsPlugin } from '@darkobits/vite-plugin-favicons';
import tailwindcss from '@tailwindcss/vite'


// Read global variables from JSON file
const globals = JSON.parse(fs.readFileSync(path.resolve(__dirname, 'resources/globals.json'), 'utf8'));

// Determine the mode and configuration
const isProxyMode = process.env.PROXY_MODE === 'true';
const isProdMode = process.env.PROD_MODE === 'true';

// Include Pug pages
const PUGPAGES = fs.readdirSync(path.resolve(__dirname, 'resources')).filter(fileName => fileName.endsWith('.pug') && fileName !== '_components.pug');


export default defineConfig(({ mode }) => {
  console.log(`Running in ${mode} mode with PROD_MODE=${isProdMode}`);

  // Define input files
  const inputFilePath = isProxyMode ? path.resolve(__dirname, 'resources/scripts.js') : PUGPAGES.map(page => `resources/${page}.html`);
  const proxyURL = process.env.DDEV_PRIMARY_URL;
  
  // Proxy mode specific plugin configuration
  const proxyModePlugins = isProxyMode
    ? [
        VitePluginBrowserSync({
          dev: { enable: false },
          preview: { enable: true },
          buildWatch: {
            enable: true,
            bs: {
              strictPort: true,
              host: process.env.DDEV_HOSTNAME,
              port: 3000,
              proxy: "localhost",
              ui: false,
              files: [
                'resources/**/*.{js,scss,css}',
                'website/system/user/templates/**/*.html',
              ],
              open: false,
              reloadDelay: 1000,
            },
          },
        }),
        tailwindcss()
      ]
    : [
        pug({ globals }),
        faviconsPlugin({
          inject: true,
          cache: true,
          icons: {
            favicons: { source: 'resources/images/meta/favicon.svg' },
            android: { source: 'resources/images/meta/favicon.svg' },
            appleStartup: { source: 'resources/images/meta/favicon.svg' },
          },
        }),  
        tailwindcss()
      ];

  return {
    css: {
      devSourcemap: true,
      preprocessorOptions: {
        scss: {
          silenceDeprecations: ['legacy-js-api'],
        },
      },
    },
    root: 'resources',
    resolve: {
      alias: {
        '/images': path.resolve(__dirname, 'resources/images'),
        '/fonts': path.resolve(__dirname, 'resources/fonts'),
        '@components': path.resolve(__dirname, 'resources/components'),
        '/resources': path.resolve(__dirname, 'resources'),
      },
    },
    optimizeDeps: {
      include: ['resources/**/*.pug'],
    },
    plugins: [...proxyModePlugins],
    build: {
      manifest: "manifest.json",
      assetsInlineLimit: 0,
      // Set output directory based on PROD_MODE
      outDir: isProdMode
        ? path.resolve(__dirname, 'website/public_html/')
        : path.resolve(__dirname, 'dist'),
      emptyOutDir: isProdMode
        ? false // Don't empty out the output directory on production
        : true,
      rollupOptions: {
        input: inputFilePath,
        output: {
          assetFileNames: (assetInfo) => {
            const name = assetInfo.name || '';
            if (assetInfo.name.endsWith('.css')) {
              return `assets/css/styles.css`;
            }
            if (assetInfo.name.endsWith('.js')) {
              return `assets/js/${name}`;
            }
            if (/\.(svg|png|jpe?g|webp)$/.test(assetInfo.name)) {
              return `assets/images/${name}`;
            }
            if (/\.(woff|woff2|ttf|eot)$/.test(assetInfo.name)) {
              return `assets/fonts/${name}`;
            }
            return `${name}`;
          },
          chunkFileNames: 'assets/js/scripts.js',
          entryFileNames: 'assets/js/scripts.js',
        },
        plugins: isProdMode || isProxyMode
          ? [
              {
                name: 'exclude-html-files',
                generateBundle(options, bundle) {
                  for (const [key, value] of Object.entries(bundle)) {
                    if (key.endsWith('.html')) {
                      delete bundle[key];
                    }
                  }
                },
              },
            ]
          : [],
      },
      watch: isProxyMode ? {} : undefined, // Enable watch mode in proxy mode
    },
    server: !process.env.DDEV_PRIMARY_URL ? {} :{
      // respond to all network requests:
      host: "0.0.0.0",
      port: 5173,
      strictPort: true,
      // Defines the origin of the generated asset URLs during development
      origin: `${process.env.DDEV_PRIMARY_URL.replace(/:\d+$/, "")}:5173`,
      // Configure CORS for the dev server (security)
      cors: {
        origin: /https?:\/\/([A-Za-z0-9\-\.]+)?(\.ddev\.site)(?::\d+)?$/,
      },
    },
  };
});
