<template>
    <ManageContainer>
        <template #right-tabs>
            <Tabs
                :tabs="viewModeOptions"
                v-model="viewMode"
            />
        </template>
        <DataTable v-if="viewMode === 'table'" :fields="fields" route="/json/manage-books" item-actions-width="18%" :bus="bus" :fieldHtmlMutations="statusBadgeMutations" :fieldClass="{ user_status: 'col-status-highlight' }">
            <template #actions>
                <button @click="showModal = true">+ Add Book</button>
            </template>
            <template #item-actions="{ item }">
                <button @click="viewBook(item.id)">View</button>
                <button class="ml-0_5em" @click="startEdit(item)">Edit</button>
                <button class="ml-0_5em" @click="deleteItem(item)">Delete</button>
                <button class="ml-0_5em" @click="cloneBook(item)" style="display: inline-flex; vertical-align: bottom;">
                    <CopyIcon />
                </button>
                <button class="ml-0_5em add-list" v-if="!item.user_status" @click="startAddToList(item)">+ My List</button>
            </template>
        </DataTable>
        <GridView v-else route="/json/manage-books" :bus="bus">
            <template #actions>
                <button @click="showModal = true">+ Add Book</button>
            </template>
            <template #item-actions="{ item }">
                <button @click="viewBook(item.id)">View</button>
                <button class="ml-0_5em" @click="startEdit(item)">Edit</button>
                <button class="ml-0_5em" @click="deleteItem(item)">Delete</button>
                <button class="ml-0_5em" @click="cloneBook(item)" style="display: inline-flex; vertical-align: bottom;">
                    <CopyIcon />
                </button>
                <button class="ml-0_5em add-list" v-if="!item.user_status" @click="startAddToList(item)">+ My List</button>
            </template>
        </GridView>
        <Modal v-model:showModal="showModal">
            <template #title>{{ modalLabel }} Book</template>
            <form id="book-form" @submit.prevent="addBook">
                <div class="form-group">
                    <label>Book Name</label>
                    <input type="text" required v-model="book.name" v-focus>
                </div>
                <div class="form-group">
                    <label>Book Type</label>
                    <select required v-model="book.book_type_id">
                        <option v-for="bookType in bookTypes" :key="bookType.id" :value="bookType.id">{{ bookType.name }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cover Image URL</label>
                    <input type="text" v-model="book.cover_image_url">
                </div>
                <div class="mt-1em">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 3.5rem">Index</th>
                                <th>Series</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(series, index) in seriesList">
                                <td>
                                    <input type="number" step="0.1" class="w-100p b-0 o-0" v-model="series.index" required>
                                </td>
                                <td>
                                    <Multiselect
                                        v-model="series.series_id"
                                        placeholder="Choose a series"
                                        :filterResults="false"
                                        :searchable="true"
                                        :options="selectSeries"
                                        :required="true"
                                        @search-change="fetchSeries"
                                    />
                                </td>
                                <td class="w-1px">
                                    <button type="button" @click="seriesList.splice(index, 1)">X</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" @click="seriesList.push({})" class="mt-1em">Add Series +</button>
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
            </form>
            <template #footer>
                <button type="button" class="secondary" @click="showModal = false">Cancel</button>
                <button type="submit" form="book-form">Save</button>
            </template>
        </Modal>

        <!-- Quick Add Author Modal -->
        <Modal v-model:showModal="showAddAuthorModal" :z-index="4">
            <template #title>Add Author</template>
            <form id="add-author-form" @submit.prevent="addNewAuthor">
                <div class="form-group">
                    <label>Author Name</label>
                    <input type="text" required v-model="newAuthor.name" v-focus>
                </div>
            </form>
            <template #footer>
                <button type="button" class="secondary" @click="showAddAuthorModal = false">Cancel</button>
                <button type="submit" form="add-author-form">Add Author</button>
            </template>
        </Modal>

        <!-- Add to My List Modal -->
        <Modal v-model:showModal="showAddToListModal">
            <template #title>Add to My List</template>
            <form id="add-to-list-form" @submit.prevent="addToList">
                <div class="form-group">
                    <label>{{ addToListBook.display_name }}</label>
                    <select v-model="addToListStatus" required>
                        <option value="TO_READ">To Read</option>
                        <option value="CURRENTLY_READING">Currently Reading</option>
                        <option value="READ">Read</option>
                        <option value="ABANDONED">Abandoned</option>
                    </select>
                </div>
            </form>
            <template #footer>
                <button type="button" class="secondary" @click="showAddToListModal = false">Cancel</button>
                <button type="submit" form="add-to-list-form">Add</button>
            </template>
        </Modal>
    </ManageContainer>
</template>

<script>
import ManageContainer from './ManageContainer.vue'
import DataTable from '@/scripts/components/DataTable.vue'
import GridView from '@/scripts/components/GridView.vue'
import mitt from 'mitt'
import Modal from '@/scripts/components/Modal.vue'
import Tabs from '@/scripts/components/Tabs.vue'
import CopyIcon from '@/scripts/components/CopyIcon.vue'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

export default {
    components: {
        ManageContainer,
        DataTable,
        GridView,
        Modal,
        Tabs,
        CopyIcon,
        Multiselect
    },
    data() {
        return {
            fields: [
                { fieldName: 'Book', field: 'display_name', width: '30%' },
                { fieldName: 'Author', field: 'author', width: '28%' },
                { fieldName: 'Type', field: 'book_type', width: '12%' },
                { fieldName: 'Your Status', field: 'user_status', width: '12%' }
            ],
            bus: mitt(),
            viewModeOptions: [
                { value: 'table', label: 'Table' },
                { value: 'grid', label: 'Grid' }
            ],
            viewMode: 'table',
            showModal: false,
            book: {},
            authors: [{}],
            seriesList: [],
            selectAuthors: [],
            bookTypes: [],
            selectSeries: [],
            showAddAuthorModal: false,
            newAuthor: {},
            currentAuthorIndex: null,
            statusBadgeMutations: {
                user_status: (val) => {
                    const map = {
                        'TO_READ':           ['to-read',           'To Read'],
                        'CURRENTLY_READING': ['currently-reading', 'Currently Reading'],
                        'READ':              ['read',              'Read'],
                        'ABANDONED':         ['abandoned',         'Abandoned']
                    }
                    if (!val || !map[val]) return '<span class="status-badge status-none">—</span>'
                    const [cls, label] = map[val]
                    return `<span class="status-badge status-${cls}">${label}</span>`
                }
            },
            showAddToListModal: false,
            addToListBook: {},
            addToListStatus: 'TO_READ'
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
                    this.seriesList = []
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
        },
        showAddToListModal() {
            if(!this.showAddToListModal) {
                this.addToListBook = {}
                this.addToListStatus = 'TO_READ'
            }
        },
        viewMode() {
            localStorage.setItem('Bookworm-ManageBooks-ViewMode', this.viewMode)
        }
    },
    methods: {
        addBook() {
            let loader = this.$loading.show()

            const postData = {
                name: this.book.name,
                book_type_id: this.book.book_type_id,
                cover_image_url: this.book.cover_image_url,
                series: this.seriesList,
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
                    this.seriesList = []
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
            axios.get(`/json/manage-books/${item.id}/authors-and-series`).then(response => {
                this.authors = response.data.authors
                this.seriesList = response.data.series
                this.selectAuthors = this.authors.map(author => {
                    return {
                        value: author.author_id,
                        label: author.name
                    }
                })
                this.selectSeries = this.seriesList.map(series => {
                    return {
                        value: series.series_id,
                        label: series.name
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
        },
        cloneBook(item) {
            const clonedBook = JSON.parse(JSON.stringify(item))
            delete clonedBook.id

            this.book = clonedBook

            let loader = this.$loading.show()
            axios.get(`/json/manage-books/${item.id}/authors-and-series`).then(response => {
                this.authors = response.data.authors.map(author => ({ ...author, id: undefined }))
                this.seriesList = response.data.series.map(series => ({ ...series, id: undefined }))

                this.selectAuthors = this.authors.map(author => {
                    return {
                        value: author.author_id,
                        label: author.name
                    }
                })
                this.selectSeries = this.seriesList.map(series => {
                    return {
                        value: series.series_id,
                        label: series.name
                    }
                })
                loader.hide()
                this.showModal = true
            }).catch(() => {
                loader.hide()
                this.$snotify.error('Failed to clone book')
            })
        },
        startAddToList(item) {
            this.addToListBook = item
            this.addToListStatus = 'TO_READ'
            this.showAddToListModal = true
        },
        addToList() {
            let loader = this.$loading.show()
            axios.post(`/json/books/${this.addToListBook.id}/add-to-list`, {
                status: this.addToListStatus
            }).then(() => {
                this.bus.emit('refreshDataTable')
                this.showAddToListModal = false
                loader.hide()
                this.$snotify.success('Added to My List')
            }).catch(() => {
                loader.hide()
                this.$snotify.error('Failed to add to list')
            })
        },
    },
    created() {
        this.fetchBookTypes()

        let viewMode = localStorage.getItem('Bookworm-ManageBooks-ViewMode')
        if(viewMode) {
            this.viewMode = viewMode
        }
    }
}
</script>

<style scoped>
:deep(.datatable table) {
    table-layout: fixed;
}

:deep(.col-status-highlight) {
    background: #fffde7;
}
</style>
