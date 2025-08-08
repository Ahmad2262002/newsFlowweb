import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            // Ensure jQuery is properly aliased
            '$': 'jquery',
            'jQuery': 'jquery',
        }
    },
    optimizeDeps: {
        include: [
            'jquery',
            'alpinejs',
            'moment',
            'daterangepicker'
        ]
    }
});