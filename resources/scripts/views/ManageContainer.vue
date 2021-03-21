<template>
    <div>
        <div class="tabs mb-1em">
            <router-link :to="`/manage/${link.slug}`" custom v-slot="{ navigate, isActive }" v-for="link in links">
                <div :class="{ 'tab-active': isActive }" @click="navigate">{{ link.label }}</div>
            </router-link>
        </div>
        <slot></slot>
    </div>
</template>

<script>
export default {
    data() {
        return {
            links: [
                {
                    slug: 'books',
                    label: 'Books'
                },
                {
                    slug: 'authors',
                    label: 'Authors'
                },
                {
                    slug: 'series',
                    label: 'Series'
                },
                {
                    slug: 'book-types',
                    label: 'Book Types'
                }
            ]
        }
    }
}
</script>
