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

import '../css/app.css'
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

// Track if we're currently refreshing the token to avoid duplicate refresh attempts
let isRefreshing = false
let failedQueue = []

const processQueue = (error, token = null) => {
    failedQueue.forEach(prom => {
        if (error) {
            prom.reject(error)
        } else {
            prom.resolve(token)
        }
    })
    failedQueue = []
}

axios.interceptors.response.use(response => {
    return response
}, error => {
    const originalRequest = error.config

    // If we get a 401 or 419 error, try to refresh the CSRF token and retry
    if ((error.response.status === 401 || error.response.status === 419) && !originalRequest._retry) {
        if (isRefreshing) {
            // If we're already refreshing, queue this request
            return new Promise((resolve, reject) => {
                failedQueue.push({ resolve, reject })
            }).then(() => {
                return axios(originalRequest)
            }).catch(err => {
                return Promise.reject(err)
            })
        }

        originalRequest._retry = true
        isRefreshing = true

        // Fetch a fresh CSRF token by making a simple GET request
        // Laravel will automatically set a new XSRF-TOKEN cookie
        return axios.get('/')
            .then(() => {
                isRefreshing = false
                processQueue(null, null)
                // Retry the original request with the fresh token
                return axios(originalRequest)
            })
            .catch(refreshErr => {
                isRefreshing = false
                processQueue(refreshErr, null)

                // If refreshing fails, user is likely logged out - redirect to login
                window.location.href = '/login'
                return Promise.reject(refreshErr)
            })
    }

    return Promise.reject(error.response)
})
