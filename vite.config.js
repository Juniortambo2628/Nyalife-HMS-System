import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    server: {
        port: 5174,
        strictPort: true,
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
        }),
        react(),
    ],
    test: {
        globals: true,
        environment: 'jsdom',
        setupFiles: ['./resources/js/__tests__/setup.js'],
        include: ['resources/js/**/*.{test,spec}.{js,jsx}'],
        exclude: ['node_modules', 'vendor', 'public', 'e2e'],
        pool: 'threads',
        testTimeout: 10000,
        hookTimeout: 10000,
    },
});
