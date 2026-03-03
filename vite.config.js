import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/custom-employee-dashboard.css', 'resources/css/custom-hr-dashboard.css'],
            refresh: true,
        }),
    ],
});
