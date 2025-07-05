<template>
    <div>
        <div class="d-f flex-jc-sb flex-ai-c mb-1em">
            <div class="tabs">
                <router-link :to="`/manage/${link.slug}`" custom v-slot="{ navigate, isActive }" v-for="link in links">
                    <div :class="{ 'tab-active': isActive }" @click="navigate">{{ link.label }}</div>
                </router-link>
            </div>
            <slot name="right-tabs"></slot>
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
