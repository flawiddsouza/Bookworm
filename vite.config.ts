import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import path from 'path'

export default defineConfig({
    resolve: {
        alias: {
          '@': path.resolve(__dirname, 'resources'),
        }
    },
    plugins: [
        vue(),
        laravel([
            'resources/scripts/main.js',
            'resources/css/app.css',
        ]),
    ],
})
