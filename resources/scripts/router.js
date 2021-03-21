import { createRouter, createWebHashHistory } from 'vue-router'

import Home from '@/scripts/views/Home.vue'
import ManageBooks from '@/scripts/views/ManageBooks.vue'
import ManageAuthors from '@/scripts/views/ManageAuthors.vue'
import ManageSeries from '@/scripts/views/ManageSeries.vue'
import ManageBookTypes from '@/scripts/views/ManageBookTypes.vue'

const routes = [
    { path: '/', component: Home },
    { path: '/manage/books', component: ManageBooks },
    { path: '/manage/authors', component: ManageAuthors },
    { path: '/manage/series', component: ManageSeries },
    { path: '/manage/book-types', component: ManageBookTypes },
]

const router = createRouter({
    history: createWebHashHistory(),
    routes
})

export default router
