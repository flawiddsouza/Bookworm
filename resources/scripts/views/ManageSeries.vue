<template>
    <ManageContainer>
        <DataTable :fields="fields" route="/json/manage-series" item-actions-width="10em" :bus="bus">
            <template #actions>
                <button @click="showModal = true">+ Add Series</button>
            </template>
            <template #item-actions="{ item }">
                <button>View</button>
                <button class="ml-0_5em" @click="startEdit(item)">Edit</button>
                <button class="ml-0_5em" @click="deleteItem(item)">Delete</button>
            </template>
        </DataTable>
        <Modal v-model:showModal="showModal">
            <template #title>{{ modalLabel }} Series</template>
            <form @submit.prevent="addSeries">
                <div>
                    <label>Series Name<br>
                        <input type="text" required v-model="series.name" v-focus class="w-100p">
                    </label>
                </div>
                <div class="mt-1em">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Author</th>
                                <th>Role</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(author, index) in authors">
                                <td>
                                    <Multiselect
                                        v-model="author.author_id"
                                        placeholder="Choose an author"
                                        :filterResults="false"
                                        :searchable="true"
                                        :options="selectAuthors"
                                        :required="true"
                                        @search-change="fetchAuthors"
                                        style="width: 18rem;"
                                    />
                                </td>
                                <td>
                                    <input type="text" class="w-100p b-0 o-0" v-model="author.role">
                                </td>
                                <td class="w-1px">
                                    <button type="button" @click="authors.splice(index, 1)">X</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" @click="authors.push({})" class="mt-1em">Add Author +</button>
                </div>
                <div class="mt-1em">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 3.5rem">Index</th>
                                <th>Book</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(book, index) in books">
                                <td>
                                    <input type="number" step="0.1" class="w-100p b-0 o-0" v-model="book.index" required v-focus>
                                </td>
                                <td>
                                    <Multiselect
                                        v-model="book.book_id"
                                        placeholder="Choose a book"
                                        :filterResults="false"
                                        :searchable="true"
                                        :options="selectBooks"
                                        :required="true"
                                        @search-change="fetchBooks"
                                    />
                                </td>
                                <td class="w-1px">
                                    <button type="button" @click="books.splice(index, 1)">X</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" @click="books.push({})" class="mt-1em">Add Book +</button>
                </div>
                <div class="mt-1em"></div>
                <button class="mt-1em">Save</button>
            </form>
        </Modal>
    </ManageContainer>
</template>

<script>
import ManageContainer from './ManageContainer.vue'
import DataTable from '@/scripts/components/DataTable.vue'
import mitt from 'mitt'
import Modal from '@/scripts/components/Modal.vue'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

export default {
    components: {
        ManageContainer,
        DataTable,
        Modal,
        Multiselect
    },
    data() {
        return {
            fields: [
                {
                    fieldName: 'Series',
                    field: 'name'
                },
                {
                    fieldName: 'Author',
                    field: 'author'
                }
            ],
            bus: mitt(),
            showModal: false,
            series: {},
            authors: [],
            books: [],
            selectAuthors: [],
            selectBooks: []
        }
    },
    computed: {
        modalLabel() {
            if('id' in this.series) {
                return 'Edit'
            } else {
                return 'Add'
            }
        }
    },
    watch: {
        showModal() {
            if(!this.showModal) {
                if('id' in this.series) {
                    this.series = {}
                    this.authors = []
                    this.books = []
                }
            }
        }
    },
    methods: {
        addSeries() {
            let loader = this.$loading.show()

            const postData = {
                name: this.series.name,
                authors: this.authors,
                books: this.books
            }

            if('id' in this.series) {
                axios.put(`/json/manage-series/${this.series.id}`, postData).then(() => {
                    this.bus.emit('refreshDataTable')
                    this.showModal = false
                    loader.hide()
                    this.$snotify.success('Series Updated')
                }).catch(response => {
                    loader.hide()
                    this.$snotify.error(response.data)
                })
            } else {
                axios.post('/json/manage-series', postData).then(() => {
                    this.bus.emit('refreshDataTable')
                    this.series = {}
                    this.authors = []
                    this.books = []
                    loader.hide()
                    this.$snotify.success('Series Added')
                }).catch(response => {
                    loader.hide()
                    this.$snotify.error(response.data)
                })
            }
        },
        startEdit(item) {
            this.series = JSON.parse(JSON.stringify(item))
            let loader = this.$loading.show()
            axios.get(`/json/manage-series/${item.id}/authors-and-books`).then(response => {
                this.authors = response.data.authors
                this.books = response.data.books
                this.selectAuthors = this.authors.map(author => {
                    return {
                        value: author.author_id,
                        label: author.name
                    }
                })
                this.selectBooks = this.books.map(book => {
                    return {
                        value: book.book_id,
                        label: book.name + ' by ' + book.author
                    }
                })
                loader.hide()
                this.showModal = true
            })
        },
        deleteItem(item) {
            if(!confirm('Are you sure?')) {
                return
            }
            let loader = this.$loading.show()
            axios.delete(`/json/manage-series/${item.id}`).then(() => {
                this.bus.emit('refreshDataTable')
                this.showModal = false
                loader.hide()
                this.$snotify.success('Series Deleted')
            }).catch(response => {
                loader.hide()
                this.$snotify.error(response.data)
            })
        },
        async fetchAuthors(query) {
            let response = await axios.get(`/json/search/authors?q=${query}`)
            let authors = response.data
            this.selectAuthors = authors.map(author => {
                return {
                    value: author.id,
                    label: author.name
                }
            })
        },
        async fetchBooks(query) {
            let response = await axios.get(`/json/search/books?q=${query}`)
            let books = response.data
            this.selectBooks = books.map(book => {
                return {
                    value: book.id,
                    label: book.name + ' by ' + book.author
                }
            })
        }
    }
}
</script>
