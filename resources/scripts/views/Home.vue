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
            <Tabs
                :tabs="viewModeOptions"
                v-model="viewMode"
            />
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
                <div>
                    <label>Status<br>
                        <select v-model="book.status" class="w-100p" required>
                            <option value="TO_READ">To Read</option>
                            <option value="CURRENTLY_READING">Currently Reading</option>
                            <option value="READ">Read</option>
                            <option value="ABANDONED">Abandoned</option>
                        </select>
                    </label>
                </div>
                <div class="mt-1em">
                    <label>Started Reading<br>
                        <input type="date" v-model="book.started_reading" class="w-100p">
                    </label>
                </div>
                <div class="mt-1em">
                    <label>Completed Reading<br>
                        <input type="date" v-model="book.completed_reading" class="w-100p">
                    </label>
                </div>
                <div class="mt-1em">
                    <label>Rating<br>
                        <select v-model="book.rating" class="w-100p">
                            <option v-for="rating in ratings" :value="rating.rating">{{ rating.description }}</option>
                        </select>
                    </label>
                </div>
                <div class="mt-1em">
                    <label>Notes<br>
                        <resizable-textarea>
                            <textarea v-model="book.notes" class="w-100p"></textarea>
                        </resizable-textarea>
                    </label>
                </div>
                <div class="mt-1em">
                    <label>Reading Medium<br>
                        <input type="text" v-model="book.reading_medium" class="w-100p">
                    </label>
                </div>
                <div class="mt-1em"></div>
                <button class="mt-1em">Save</button>
            </form>
        </Modal>
    </div>
</template>

<script>
import DataTable from '@/scripts/components/DataTable.vue'
import GridView from '@/scripts/components/GridView.vue'
import mitt from 'mitt'
import Modal from '@/scripts/components/Modal.vue'
import ResizableTextarea from '@/scripts/components/ResizableTextarea.vue'
import Tabs from '@/scripts/components/Tabs.vue'
import { ratings } from '@/scripts/sharedData.js'

export default {
    components: {
        DataTable,
        GridView,
        Modal,
        ResizableTextarea,
        Tabs
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
            this.showModal = true
        },
        updateBook() {
            let loader = this.$loading.show()
            axios.put(`/json/user/books/${this.book.id}`, this.book).then(() => {
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
        fetchBookTypes() {
            axios.get('/json/book-types').then(response => {
                this.bookTypeFilters = [
                    { name: 'All', filter: 'ALL' },
                    ...response.data.map(bookType => ({
                        name: bookType.name,
                        filter: bookType.id.toString()
                    }))
                ]
            }).catch(error => {
                console.error('Failed to fetch book types:', error)
                this.bookTypeFilters = [{ name: 'All', filter: 'ALL' }]
            })
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
