<template>
    <div>
        <div class="tabs mb-1em">
            <div @click="activeTab = tab.filter" :class="{ 'tab-active': activeTab === tab.filter }" v-for="tab in tabs">{{ tab.name }}</div>
        </div>
        <div>
            <DataTable :fields="fields" :field-html="fieldHtml" :route="`/json/user/books?status=${activeTab}`" item-actions-width="15em" :bus="bus">
                <template #item-actions="{ item }">
                    <button>View</button>
                    <button class="ml-0_5em" @click="startEdit(item)">Edit</button>
                    <button class="ml-0_5em" @click="deleteItem(item)">Delete</button>
                </template>
            </DataTable>
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
                        <input type="number" step="0.5" v-model="book.rating" class="w-100p">
                    </label>
                </div>
                <div class="mt-1em">
                    <label>Private Notes<br>
                        <resizable-textarea>
                            <textarea v-model="book.private_notes" class="w-100p"></textarea>
                        </resizable-textarea>
                    </label>
                </div>
                <div class="mt-1em">
                    <label>Public Notes<br>
                        <resizable-textarea>
                            <textarea v-model="book.public_notes" class="w-100p"></textarea>
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
import mitt from 'mitt'
import Modal from '@/scripts/components/Modal.vue'
import ResizableTextarea from '@/scripts/components/ResizableTextarea.vue'

export default {
    components: {
        DataTable,
        Modal,
        ResizableTextarea
    },
    data() {
        return {
            tabs: [
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
                }
            ],
            activeTab: 'CURRENTLY_READING',
            bus: mitt(),
            fieldHtml: ['private_notes', 'public_notes'],
            showModal: false,
            book: {}
        }
    },
    computed: {
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

            if(this.activeTab === 'READ') {
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

            // fields.push(...[
            //     {
            //         fieldName: 'Private Notes',
            //         field: 'private_notes',
            //         width: '30em',
            //         whiteSpace: 'pre-line'
            //     },
            //     {
            //         fieldName: 'Public Notes',
            //         field: 'public_notes',
            //         width: '30em',
            //         whiteSpace: 'pre-line'
            //     }
            // ])

            return fields
        }
    },
    watch: {
        activeTab() {
            localStorage.setItem('Bookworm-Home-ActiveTab', this.activeTab)
        }
    },
    methods: {
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
        }
    },
    created() {
        let activeTab = localStorage.getItem('Bookworm-Home-ActiveTab')

        if(activeTab) {
            this.activeTab = activeTab
        }
    }
}
</script>
