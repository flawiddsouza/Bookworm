<template>
    <div>
        <div class="d-f flex-jc-sb flex-ai-c mb-1em">
            <div>
                <Tabs
                    :tabs="tabs"
                    v-model="activeTab"
                    value-key="filter"
                    label-key="name"
                />
                <Tabs
                    :tabs="bookTypeFilters"
                    v-model="activeBookTypeFilter"
                    value-key="filter"
                    label-key="name"
                    class="ml-1em"
                />
            </div>
            <div class="d-f flex-ai-c" style="gap: 0.5em;">
                <button @click="showAddBookModal = true">+ Add Book</button>
                <Tabs
                    :tabs="viewModeOptions"
                    v-model="viewMode"
                />
            </div>
        </div>
        <div>
            <DataTable v-if="viewMode === 'table'" :fields="fields" :field-html="fieldHtml" :route="routeUrl" item-actions-width="15em" :bus="bus">
                <template #item-actions="{ item }">
                    <button @click="viewBook(item.book_id)">View</button>
                    <button class="ml-0_5em" @click="startEdit(item)">Edit</button>
                    <button class="ml-0_5em" @click="deleteItem(item)">Delete</button>
                </template>
            </DataTable>
            <GridView v-else :route="routeUrl" :bus="bus">
                <template #item-actions="{ item }">
                    <button @click="viewBook(item.book_id)">View</button>
                    <button class="ml-0_5em" @click="startEdit(item)">Edit</button>
                    <button class="ml-0_5em" @click="deleteItem(item)">Delete</button>
                </template>
            </GridView>
        </div>
        <Modal v-model:showModal="showModal">
            <template #title>{{ book.book }} by {{ book.author }}</template>
            <form @submit.prevent="updateBook">
                <div class="form-group">
                    <label>Status</label>
                    <select v-model="book.status" required>
                        <option value="TO_READ">To Read</option>
                        <option value="CURRENTLY_READING">Currently Reading</option>
                        <option value="READ">Read</option>
                        <option value="ABANDONED">Abandoned</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Started Reading</label>
                    <input type="date" v-model="book.started_reading">
                </div>
                <div class="form-group">
                    <label>Completed Reading</label>
                    <input type="date" v-model="book.completed_reading">
                </div>
                <div class="form-group">
                    <label>Rating</label>
                    <select v-model="book.rating">
                        <option v-for="rating in ratings" :value="rating.rating">{{ rating.description }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <PlainNotes v-if="book.notes_type !== 2" v-model="book.notes" />
                    <DateMarkedNotes v-else v-model="book.notes" />
                </div>
                <div class="form-group">
                    <label>Reading Medium</label>
                    <input type="text" v-model="book.reading_medium">
                </div>
                <button>Save</button>
            </form>
        </Modal>
        <AddBookModal v-model="showAddBookModal" @book-added="onBookAdded" />
    </div>
</template>

<script>
import DataTable from '@/scripts/components/DataTable.vue'
import DateMarkedNotes from '@/scripts/components/DateMarkedNotes.vue'
import GridView from '@/scripts/components/GridView.vue'
import mitt from 'mitt'
import Modal from '@/scripts/components/Modal.vue'
import PlainNotes from '@/scripts/components/PlainNotes.vue'
import Tabs from '@/scripts/components/Tabs.vue'
import AddBookModal from '@/scripts/components/AddBookModal.vue'
import { getBookTypes, ratings } from '@/scripts/sharedData.js'

export default {
    components: {
        DataTable,
        DateMarkedNotes,
        GridView,
        Modal,
        PlainNotes,
        Tabs,
        AddBookModal
    },
    data() {
        return {
            tabs: [
                {
                    name: 'Currently Reading + Read',
                    filter: 'CURRENTLY_READING,READ'
                },
                {
                    name: 'Currently Reading',
                    filter: 'CURRENTLY_READING'
                },
                {
                    name: 'Read',
                    filter: 'READ'
                },
                {
                    name: 'To Read',
                    filter: 'TO_READ'
                },
                {
                    name: 'Abandoned',
                    filter: 'ABANDONED'
                }
            ],
            activeTab: 'CURRENTLY_READING,READ',
            bookTypeFilters: [],
            activeBookTypeFilter: 'ALL',
            viewModeOptions: [
                { value: 'table', label: 'Table' },
                { value: 'grid', label: 'Grid' }
            ],
            viewMode: 'table',
            bus: mitt(),
            fieldHtml: ['notes'],
            showModal: false,
            showAddBookModal: false,
            book: {},
            ratings
        }
    },
    computed: {
        routeUrl() {
            let url = `/json/user/books?status=${this.activeTab}`
            if (this.activeBookTypeFilter !== 'ALL') {
                url += `&book_type=${this.activeBookTypeFilter}`
            }
            return url
        },
        fields() {
            let fields = [
                {
                    fieldName: 'Book',
                    field: 'book'
                },
                {
                    fieldName: 'Author',
                    field: 'author'
                },
                {
                    fieldName: 'Type',
                    field: 'book_type'
                }
            ]

            if(this.activeTab === 'READ' || this.activeTab === 'CURRENTLY_READING,READ') {
                fields.push(...[
                    {
                        fieldName: 'Started Reading',
                        field: 'started_reading_display'
                    },
                    {
                        fieldName: 'Completed Reading',
                        field: 'completed_reading_display'
                    },
                    {
                        fieldName: 'Rating',
                        field: 'rating_display'
                    }
                ])
            }

            if(this.activeTab === 'CURRENTLY_READING') {
                fields.push({
                    fieldName: 'Started Reading',
                    field: 'started_reading_display'
                })
            }

            if(this.activeTab === 'CURRENTLY_READING' || this.activeTab === 'READ') {
                fields.push({
                    fieldName: 'Reading Medium',
                    field: 'reading_medium'
                })
            }

            return fields
        }
    },
    watch: {
        activeTab() {
            localStorage.setItem('Bookworm-Home-ActiveTab', this.activeTab)
        },
        activeBookTypeFilter() {
            localStorage.setItem('Bookworm-Home-ActiveBookTypeFilter', this.activeBookTypeFilter)
        },
        viewMode() {
            localStorage.setItem('Bookworm-Home-ViewMode', this.viewMode)
        }
    },
    methods: {
        viewBook(bookId) {
            this.$router.push({ path: `/book/${bookId}` })
        },
        startEdit(item) {
            this.book = JSON.parse(JSON.stringify(item))
            if (this.book.notes_type === 2) {
                try {
                    this.book.notes = JSON.parse(this.book.notes || '[]')
                } catch {
                    this.book.notes = []
                }
            }
            this.showModal = true
        },
        updateBook() {
            let loader = this.$loading.show()
            const payload = { ...this.book }
            if (payload.notes_type === 2) {
                payload.notes = JSON.stringify(payload.notes)
            }
            axios.put(`/json/user/books/${this.book.id}`, payload).then(() => {
                this.bus.emit('refreshDataTable')
                this.showModal = false
                loader.hide()
                this.$snotify.success('Book Updated')
            }).catch(response => {
                loader.hide()
                this.$snotify.error(response.data)
            })
        },
        deleteItem(item) {
            if(!confirm('Are you sure?')) {
                return
            }
            let loader = this.$loading.show()
            axios.delete(`/json/user/books/${item.id}`).then(() => {
                this.bus.emit('refreshDataTable')
                this.showModal = false
                loader.hide()
                this.$snotify.success('Book Removed from Your Library')
            }).catch(response => {
                loader.hide()
                this.$snotify.error(response.data)
            })
        },
        onBookAdded(status) {
            const match = this.tabs.find(t => t.filter === status)
            if (match) this.activeTab = match.filter
            this.bus.emit('refreshDataTable')
        },
        async fetchBookTypes() {
            try {
                const bookTypes = await getBookTypes()
                this.bookTypeFilters = [
                    { name: 'All', filter: 'ALL' },
                    ...bookTypes.map(bookType => ({
                        name: bookType.name,
                        filter: bookType.id.toString()
                    }))
                ]
            } catch (error) {
                console.error('Failed to fetch book types:', error)
                this.bookTypeFilters = [{ name: 'All', filter: 'ALL' }]
            }
        }
    },
    created() {
        let activeTab = localStorage.getItem('Bookworm-Home-ActiveTab')
        let activeBookTypeFilter = localStorage.getItem('Bookworm-Home-ActiveBookTypeFilter')
        let viewMode = localStorage.getItem('Bookworm-Home-ViewMode')

        if(activeTab) {
            this.activeTab = activeTab
        }
        if(activeBookTypeFilter) {
            this.activeBookTypeFilter = activeBookTypeFilter
        }
        if(viewMode) {
            this.viewMode = viewMode
        }

        this.fetchBookTypes()
    }
}
</script>
