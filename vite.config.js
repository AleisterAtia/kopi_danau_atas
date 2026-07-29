import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/echo.js', 'resources/js/push-notifications.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // 5173 (Vite default) falls inside a Windows reserved port range
        // (Hyper-V/WSL2), which causes EACCES on bind. 5500 is outside it.
        port: 5500,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
