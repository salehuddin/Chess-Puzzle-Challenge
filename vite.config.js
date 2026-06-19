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
                'resources/css/filament-puzzle-preview.css',
                'resources/js/filament-puzzle-preview.js',
            ],
            refresh: true,
        }),
    ],
});
