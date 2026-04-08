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
    server: {
        // If you have a custom host or want to rely on IPv6 loopback:
        host: 'localhost', // or '::' or '127.0.0.1'
        port: 5173,
        cors: true, // Enable CORS
        // If you need to specify a custom origin for HMR, do:
        // origin: 'http://serbia.myapp.local:8000',
    },
});
