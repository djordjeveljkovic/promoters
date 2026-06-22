import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
    },
    build: {
        // Three.js itself is ~500 kB minified — when it's inlined into the
        // scene chunk, Vite warns that scene-CGSntCiK.js is too large.
        // Splitting it into its own `three` chunk (loaded only when the
        // welcome page is shown) means:
        //   - the main admin/promoter bundle stays tiny (~50 kB),
        //   - the scene chunk shrinks to just our code (~5 kB),
        //   - Three.js gets cached separately so repeat visits to /
        //     don't re-download it.
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    // Anything under node_modules/three is its own chunk.
                    if (id.includes('node_modules/three')) {
                        return 'three';
                    }
                },
            },
        },
        // After splitting, no individual chunk should hit the
        // 500 kB warning, but keep a sensible cap so we notice
        // future regressions.
        chunkSizeWarningLimit: 600,
    },
});