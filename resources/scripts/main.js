/*
|--------------------------------------------------------------------------
| Main entry point
|--------------------------------------------------------------------------
| Files in the "resources/scripts" directory are considered entrypoints
| by default.
|
| -> https://vitejs.dev
| -> https://github.com/innocenzi/laravel-vite
*/

import { createApp } from 'vue'
import App from '@/scripts/views/App.vue'
import Router from '@/scripts/router.js'

const app = createApp(App)
app.use(Router)
app.mount('#app')
