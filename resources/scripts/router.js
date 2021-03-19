import { createRouter, createWebHashHistory } from 'vue-router'

import Home from '@/scripts/views/Home.vue'
import Books from '@/scripts/views/Books.vue'

const routes = [
    { path: '/', component: Home },
    { path: '/books', component: Books }
]

const router = createRouter({
    history: createWebHashHistory(),
    routes
})

export default router
