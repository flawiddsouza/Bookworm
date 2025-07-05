<template>
    <ManageContainer>
        <DataTable :fields="fields" route="/json/manage-books" item-actions-width="10em" :bus="bus">
            <template #actions>
                <button @click="showModal = true">+ Add Book</button>
            </template>
            <template #item-actions="{ item }">
                <button @click="viewBook(item.id)">View</button>
                <button class="ml-0_5em" @click="startEdit(item)">Edit</button>
                <button class="ml-0_5em" @click="deleteItem(item)">Delete</button>
            </template>
        </DataTable>
        <Modal v-model:showModal="showModal">
            <template #title>{{ modalLabel }} Book</template>
            <form @submit.prevent="addBook">
                <div>
                    <label>Book Name<br>
                        <input type="text" required v-model="book.name" v-focus class="w-100p">
                    </label>
                </div>
                <div class="mt-1em">
                    <label>Book Type<br>
                        <select required v-model="book.book_type_id" class="w-100p">
                            <option v-for="bookType in bookTypes" :value="bookType.id">{{ bookType.name }}</option>
                        </select>
                    </label>
                </div>
                <div class="mt-1em">
                    <label>Cover Image URL<br>
                        <input type="text" v-model="book.cover_image_url" class="w-100p">
                    </label>
                </div>
                <div class="mt-1em">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 3.5rem">Index</th>
                                <th>Series</th>
                            </tr>
                        </thead>
                        <tbody>
                            <td>
                                <input type="number" step="0.1" class="w-100p b-0 o-0" v-model="book.series_index" :required="book.series_id ? true : false">
                            </td>
                            <td>
                                <Multiselect
                                    v-model="book.series_id"
                                    placeholder="Choose a series"
                                    :filterResults="false"
                                    :searchable="true"
                                    :options="selectSeries"
                                    @search-change="fetchSeries"
                                />
                            </td>
                        </tbody>
                    </table>
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
                                    <div class="d-f">
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
                                        <button type="button" @click="showAddAuthorModal = true; currentAuthorIndex = index" class="ml-0_5em" title="Add new author" style="min-width: 30px;">+</button>
                                    </div>
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
                <div class="mt-1em"></div>
                <button class="mt-1em">Save</button>
            </form>
        </Modal>

        <!-- Quick Add Author Modal -->
        <Modal v-model:showModal="showAddAuthorModal">
            <template #title>Add Author</template>
            <form @submit.prevent="addNewAuthor">
                <div>
                    <label>Author Name<br>
                        <input type="text" required v-model="newAuthor.name" v-focus class="w-100p">
                    </label>
                </div>
                <div class="mt-1em">
                    <button type="submit">Add Author</button>
                    <button type="button" @click="showAddAuthorModal = false" class="ml-0_5em">Cancel</button>
                </div>
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
                    fieldName: 'Book',
                    field: 'display_name'
                },
                {
                    fieldName: 'Author',
                    field: 'author'
                },
                {
                    fieldName: 'Type',
                    field: 'book_type'
                }
            ],
            bus: mitt(),
            showModal: false,
            book: {},
            authors: [{}],
            selectAuthors: [],
            bookTypes: [],
            selectSeries: [],
            showAddAuthorModal: false,
            newAuthor: {},
            currentAuthorIndex: null
        }
    },
    computed: {
        modalLabel() {
            if('id' in this.book) {
                return 'Edit'
            } else {
                return 'Add'
            }
        }
    },
    watch: {
        showModal() {
            if(!this.showModal) {
                if('id' in this.book) {
                    this.book = {}
                    this.authors = [{}]
                }
                // Reset the add author modal state when the main modal is closed
                this.showAddAuthorModal = false
                this.newAuthor = {}
                this.currentAuthorIndex = null
            }
        },
        showAddAuthorModal() {
            if(!this.showAddAuthorModal) {
                this.newAuthor = {}
                this.currentAuthorIndex = null
            }
        }
    },
    methods: {
        addBook() {
            let loader = this.$loading.show()

            const postData = {
                name: this.book.name,
                book_type_id: this.book.book_type_id,
                cover_image_url: this.book.cover_image_url,
                series_id: this.book.series_id,
                series_index: this.book.series_index,
                authors: this.authors
            }

            if('id' in this.book) {
                axios.put(`/json/manage-books/${this.book.id}`, postData).then(() => {
                    this.bus.emit('refreshDataTable')
                    this.showModal = false
                    loader.hide()
                    this.$snotify.success('Book Updated')
                }).catch(response => {
                    loader.hide()
                    this.$snotify.error(response.data)
                })
            } else {
                axios.post('/json/manage-books', postData).then(() => {
                    this.bus.emit('refreshDataTable')
                    this.book = {}
                    this.authors = [{}]
                    loader.hide()
                    this.$snotify.success('Book Added')
                }).catch(response => {
                    loader.hide()
                    this.$snotify.error(response.data)
                })
            }
        },
        viewBook(bookId) {
            this.$router.push({ path: `/book/${bookId}` })
        },
        startEdit(item) {
            this.book = JSON.parse(JSON.stringify(item))
            let loader = this.$loading.show()
            axios.get(`/json/manage-books/${item.id}/authors`).then(response => {
                this.authors = response.data
                this.selectAuthors = this.authors.map(author => {
                    return {
                        value: author.author_id,
                        label: author.name
                    }
                })
                if(item.series_id !== null) {
                    this.selectSeries = [
                        {
                            value: item.series_id,
                            label: item.series_name
                        }
                    ]
                }
                loader.hide()
                this.showModal = true
            })
        },
        deleteItem(item) {
            if(!confirm('Are you sure?')) {
                return
            }
            let loader = this.$loading.show()
            axios.delete(`/json/manage-books/${item.id}`).then(() => {
                this.bus.emit('refreshDataTable')
                this.showModal = false
                loader.hide()
                this.$snotify.success('Book Deleted')
            }).catch(response => {
                loader.hide()
                this.$snotify.error(response.data)
            })
        },
        async fetchSeries(query) {
            let response = await axios.get(`/json/search/series?q=${query}`)
            let series = response.data
            this.selectSeries = series.map(series => {
                return {
                    value: series.id,
                    label: series.name
                }
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
        fetchBookTypes() {
            axios.get('/json/book-types').then(response => {
                this.bookTypes = response.data
            })
        },
        addNewAuthor() {
            let loader = this.$loading.show()
            axios.post('/json/manage-authors', this.newAuthor).then(response => {
                // Check if response.data exists and has the expected structure
                const newAuthorData = response.data
                if (!newAuthorData || !newAuthorData.id || !newAuthorData.name) {
                    throw new Error('Invalid response format')
                }

                // Add to the select options
                this.selectAuthors.push({
                    value: newAuthorData.id,
                    label: newAuthorData.name
                })

                // Auto-select the new author for the current author field
                if (this.currentAuthorIndex !== null) {
                    this.authors[this.currentAuthorIndex].author_id = newAuthorData.id
                }

                this.showAddAuthorModal = false
                loader.hide()
                this.$snotify.success('Author Added')
            }).catch(response => {
                loader.hide()
                this.$snotify.error(response.data || 'Failed to add author')
            })
        }
    },
    created() {
        this.fetchBookTypes()
    }
}
</script>
