import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/filament-tailwind.css',
                'resources/js/app.js',
                'resources/js/puzzle-player-page.js',
                'resources/js/challenge-complete.js',
                'resources/css/filament-puzzle-preview.css',
                'resources/js/filament-puzzle-preview.js',
                'resources/js/filament-editorjs.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        hmr: { host: 'localhost' },
    },
});
