<template>
    <div>
        <nav class="d-f flex-jc-sb flex-ai-c p-1em bb navbar-responsive">
            <div class="d-f flex-ai-c">
                <div class="d-f flex-ai-c">
                    <!-- From: https://www.flaticon.com/free-icon/book_1231138
                        Credits:
                        <div>Icons made by <a href="https://www.flaticon.com/authors/srip" title="srip">srip</a> from <a href="https://www.flaticon.com/" title="Flaticon">www.flaticon.com</a></div>
                    -->
                    <svg height="25px" viewBox="0 -40 448 448" width="40px" xmlns="http://www.w3.org/2000/svg">
                        <path d="m408 319.929688v-319.929688l-12.207031 1.023438c-48.957031 4.046874-96.976563 15.75-142.304688 34.6875l-21.488281 8.921874v318.664063l15.648438-6.503906c46.90625-19.515625 96.570312-31.589844 147.199218-35.792969zm0 0"/><path d="m448 47.296875h-24v280c.015625 4.171875-3.183594 7.65625-7.34375 8l-20.488281 1.679687c-5.914063.488282-11.8125 1.09375-17.6875 1.816407-1.90625.230469-3.800781.535156-5.695313.800781-3.960937.527344-7.914062 1.0625-11.855468 1.6875-2.296876.367188-4.578126.796875-6.867188 1.199219-3.527344.617187-7.0625 1.230469-10.582031 1.925781-2.402344.480469-4.800781 1.019531-7.25 1.539062-3.351563.710938-6.703125 1.4375-10.03125 2.230469-2.496094.59375-4.984375 1.222657-7.464844 1.855469-3.238281.800781-6.460937 1.664062-9.679687 2.5625-2.503907.6875-5.007813 1.414062-7.503907 2.148438-3.199219.945312-6.351562 1.90625-9.511719 2.914062-2.472656.800781-4.949218 1.601562-7.414062 2.398438-3.164062 1.066406-6.3125 2.167968-9.449219 3.304687-2.398437.871094-4.800781 1.746094-7.253906 2.664063-1.097656.417968-2.1875.863281-3.28125 1.289062h183.359375zm0 0"/><path d="m52.207031 1.023438-12.207031-1.023438v319.953125l14.199219 1.207031c50.597656 4.230469 100.21875 16.378906 147.046875 36l14.753906 6.136719v-318.664063l-21.464844-8.914062c-45.332031-18.941406-93.359375-30.648438-142.328125-34.695312zm0 0"/><path d="m0 47.296875v320h183.488281c-.984375-.386719-1.96875-.800781-2.960937-1.167969-2.289063-.871094-4.597656-1.703125-6.902344-2.542968-3.25-1.179688-6.496094-2.328126-9.769531-3.425782-2.367188-.800781-4.742188-1.578125-7.121094-2.335937-3.246094-1.066407-6.503906-2.070313-9.765625-3.007813-2.402344-.703125-4.800781-1.410156-7.199219-2.082031-3.289062-.910156-6.585937-1.773437-9.890625-2.621094-2.398437-.617187-4.800781-1.234375-7.253906-1.808593-3.351562-.800782-6.722656-1.535157-10.089844-2.257813-2.398437-.519531-4.800781-1.046875-7.199218-1.527344-3.488282-.6875-6.992188-1.304687-10.496094-1.917969-2.296875-.402343-4.59375-.800781-6.894532-1.210937-3.867187-.613281-7.746093-1.132813-11.625-1.65625-1.960937-.261719-3.90625-.574219-5.863281-.796875-5.867187-.710938-11.734375-1.316406-17.601562-1.816406l-21.535157-1.824219c-4.152343-.355469-7.335937-3.835937-7.320312-8v-280zm0 0"/>
                    </svg>
                    <router-link to="/" style="color: black; font-size: 1.1em" class="td-n">Bookworm</router-link>
                </div>

                <button class="navbar-toggle" @click="toggleNavbar" :class="{ 'navbar-toggle-active': showNavbar }">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="navbar-menu" :class="{ 'navbar-menu-active': showNavbar }">
                    <router-link to="/" class="ml-1em" active-class="td-n">Home</router-link>
                    <router-link to="/manage/books" class="ml-1em" active-class="td-n">Manage Books</router-link>
                    <router-link to="/import" class="ml-1em" active-class="td-n">Import</router-link>
                </div>

                <div class="ml-1em navbar-search">
                    <SearchBox placeholder="Find books..." url="/json/search/books">
                        <template #default="{ result }">
                            <div @click="loadBook(result.id)">{{ result.name }} by {{ result.author }}</div>
                        </template>
                    </SearchBox>
                </div>
            </div>
            <div class="ml-1em navbar-logout">
                <form action="/logout" method="POST" ref="logoutForm">
                    <input type="hidden" name="_token" :value="csrfToken">
                </form>
                <a href="#" @click.prevent="$refs.logoutForm.submit()">Logout</a>
            </div>
        </nav>
        <div class="p-1em">
            <router-view></router-view>
        </div>
        <Modal v-model:showModal="showModal" v-if="showModal">
            <Book :book-id="selectedBookId"></Book>
        </Modal>
    </div>
</template>

<script>
import SearchBox from '@/scripts/components/SearchBox.vue'
import Modal from '@/scripts/components/Modal.vue'
import Book from '@/scripts/views/Book.vue'

export default {
    components: {
        SearchBox,
        Modal,
        Book
    },
    data() {
        return {
            selectedBookId: null,
            showModal: false,
            showNavbar: false
        }
    },
    computed: {
        csrfToken() {
            return csrfToken
        }
    },
    methods: {
        loadBook(bookId) {
            this.selectedBookId = bookId
            this.showModal = true
        },
        toggleNavbar() {
            this.showNavbar = !this.showNavbar
        }
    }
}
</script>

<style scoped>
/* Mobile hamburger menu - only visible on mobile */
.navbar-toggle {
    display: none;
    flex-direction: column;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5em;
    gap: 3px;
}

.navbar-toggle span {
    display: block;
    width: 25px;
    height: 3px;
    background-color: #333;
    transition: 0.3s;
}

.navbar-toggle-active span:nth-child(1) {
    transform: rotate(45deg) translate(5px, 5px);
}

.navbar-toggle-active span:nth-child(2) {
    opacity: 0;
}

.navbar-toggle-active span:nth-child(3) {
    transform: rotate(-45deg) translate(7px, -6px);
}

/* Mobile responsive behavior */
@media (max-width: 768px) {
    .navbar-responsive {
        flex-wrap: wrap;
        position: relative;
    }

    .navbar-responsive > div:first-child {
        flex-direction: column;
        align-items: flex-start;
        width: 100%;
    }

    .navbar-toggle {
        display: flex;
        margin-left: 1em;
        align-self: flex-end;
        margin-top: -2em;
    }

    .navbar-menu {
        display: none;
        width: 100%;
        flex-direction: column;
        align-items: flex-start !important;
        margin-top: 1em;
        margin-left: 0 !important;
        padding-top: 1em;
        border-top: 1px solid #eee;
        gap: 0.75em;
    }

    .navbar-menu-active {
        display: flex;
    }

    .navbar-search {
        display: none;
        width: 100%;
        margin-left: 0 !important;
        margin-top: 1em;
        padding-top: 1em;
        border-top: 1px solid #eee;
    }

    .navbar-menu-active ~ .navbar-search {
        display: block;
    }

    .navbar-logout {
        display: none;
    }

    .navbar-menu-active ~ .navbar-logout {
        display: block;
        margin-left: 0 !important;
        width: 100%;
        margin-top: 1em;
        padding-top: 1em;
        border-top: 1px solid #eee;
    }
}/* Tablet adjustments */
@media (max-width: 1024px) and (min-width: 769px) {
    .navbar-responsive {
        padding: 0.75em;
    }
}

/* Small mobile adjustments */
@media (max-width: 480px) {
    .navbar-responsive {
        padding: 0.5em;
    }

    .navbar-responsive svg {
        width: 30px;
        height: 20px;
    }

    .navbar-responsive a[style*="font-size: 1.1em"] {
        font-size: 1em !important;
    }
}
</style>
