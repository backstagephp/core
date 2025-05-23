import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path'

export default defineConfig({
    root: path.resolve(__dirname, '.'),
    build: {
        outDir: path.resolve(__dirname, 'resources/dist'),
    },
    plugins: [
        laravel({
            input: [
                path.resolve(__dirname, 'resources/css/backstage.css'),
                path.resolve(__dirname, 'resources/js/backstage.js'),
            ],
            refresh: true,
        }),
    ],
});
