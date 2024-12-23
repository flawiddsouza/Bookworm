import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
    resolve: {
        alias: {
          '@': 'resources',
        }
    },
    plugins: [
        vue(),
        laravel([
            'resources/scripts/main.js',
        ]),
    ],
})
