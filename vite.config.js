import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';



export default defineConfig({
    base: '/jegvarazsbolt.hu/build/',
    build: {
        outDir: '../jegvarazsbolt.hu/build',
        emptyOutDir: true,
        manifest: true,
    },
    plugins: [
        laravel({
            input: [
                'resources/sass/admin.scss',
                'resources/js/admin.js',
                'resources/sass/shop.scss',
                'resources/js/shop.js'
            ],
            refresh: true,
        })
    ],
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler', // or "modern"
                silenceDeprecations: ['mixed-decls', 'color-functions', 'global-builtin', 'import']
            }
        }
    }
});
