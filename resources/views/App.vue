<template>
    <div class="d-f flex-jc-sb p-1em bb">
        <div>
            <a href="/">Home</a>
        </div>
        <div class="ml-1em">
            <form action="/logout" method="POST" ref="logoutForm">
                <input type="hidden" name="_token" :value="csrfToken">
            </form>
            <a href="#" @click.prevent="$refs.logoutForm.submit()">Logout</a>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Boomkworm',
    computed: {
        csrfToken() {
            return csrfToken
        }
    }
}
</script>
