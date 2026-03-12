import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    
    server: {
        // When running inside Docker, we bind to 0.0.0.0 but must expose a browser-reachable URL.
        // Set VITE_DEV_SERVER_URL=http://localhost:5173 (via docker-compose) so Laravel's @vite uses it.
        origin: process.env.VITE_DEV_SERVER_URL,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
