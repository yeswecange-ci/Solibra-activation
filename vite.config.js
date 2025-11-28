import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],

    // Configuration pour la production
    build: {
        manifest: true,
        outDir: 'public/build',
        // Désactiver le sous-dossier .vite pour compatibilité Laravel
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
    },

    // Forcer le manifest à la racine du build
    experimental: {
        renderBuiltUrl(filename, { hostType }) {
            if (hostType === 'js') {
                return { runtime: `window.__publicPath + ${JSON.stringify(filename)}` }
            }
        }
    }
});
