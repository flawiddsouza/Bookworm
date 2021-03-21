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

const app = createApp(App)

app.directive('focus', {
    mounted(element) {
        element.focus()
    }
})

import Router from '@/scripts/router.js'
app.use(Router)

import snotify from './plugins/snotify'
app.use(snotify)

import loading from './plugins/loading'
app.use(loading)

app.mount('#app')

import axios from 'axios'

window.axios = axios

axios.interceptors.response.use(response => {
    return response
}, error => {
    if(error.response.status === 401) {
        window.location.reload()
    }
    if(error.response.status === 419) { // CSRF token mismatch
        window.location.reload()
    }
    return Promise.reject(error.response)
})
