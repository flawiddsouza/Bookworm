<template>
    <ManageContainer>
        <DataTable :fields="fields" route="/json/manage-book-types" item-actions-width="10em" :bus="bus">
            <template #actions>
                <button @click="showModal = true">+ Add Book Type</button>
                <button class="ml-0_5em" @click="openReorderModal">Reorder</button>
            </template>
            <template #item-actions="{ item }">
                <button>View</button>
                <button class="ml-0_5em" @click="startEdit(item)">Edit</button>
                <button class="ml-0_5em" @click="deleteItem(item)">Delete</button>
            </template>
        </DataTable>
        <Modal v-model:showModal="showModal">
            <template #title>{{ modalLabel }} Book Type</template>
            <form @submit.prevent="addBookType">
                <div>
                    <label>Book Type<br>
                        <input type="text" required v-model="bookType.name" v-focus class="w-100p">
                    </label>
                </div>
                <div class="mt-1em">
                    <label>Sort Order<br>
                        <input type="number" v-model="bookType.sort_order" class="w-100p">
                    </label>
                </div>
                <button class="mt-1em">Save</button>
            </form>
        </Modal>
        <Modal v-model:showModal="showReorderModal">
            <template #title>Reorder Book Types</template>
            <DragDropList
                v-model:items="reorderList"
                item-type="book-type"
                @reorder="onReorder">
                <template #item="{ item }">
                    <span class="drag-handle">☰</span> {{ item.name }}
                </template>
                <template #empty>Loading...</template>
            </DragDropList>
            <button class="mt-1em" @click="saveReorder">Save Order</button>
        </Modal>
    </ManageContainer>
</template>

<script>
import ManageContainer from './ManageContainer.vue'
import DataTable from '@/scripts/components/DataTable.vue'
import mitt from 'mitt'
import Modal from '@/scripts/components/Modal.vue'
import DragDropList from '@/scripts/components/DragDropList.vue'

export default {
    components: {
        ManageContainer,
        DataTable,
        Modal,
        DragDropList
    },
    data() {
        return {
            fields: [
                {
                    fieldName: 'Book Type',
                    field: 'name'
                },
                {
                    fieldName: 'Sort Order',
                    field: 'sort_order'
                }
            ],
            bus: mitt(),
            showModal: false,
            bookType: {},
            showReorderModal: false,
            reorderList: []
        }
    },
    computed: {
        modalLabel() {
            if('id' in this.bookType) {
                return 'Edit'
            } else {
                return 'Add'
            }
        }
    },
    watch: {
        showModal() {
            if(!this.showModal) {
                if('id' in this.bookType) {
                    this.bookType = {}
                }
            }
        }
    },
    methods: {
        addBookType() {
            let loader = this.$loading.show()
            if('id' in this.bookType) {
                axios.put(`/json/manage-book-types/${this.bookType.id}`, this.bookType).then(() => {
                    this.bus.emit('refreshDataTable')
                    this.showModal = false
                    loader.hide()
                    this.$snotify.success('Book Type Updated')
                }).catch(response => {
                    loader.hide()
                    this.$snotify.error(response.data)
                })
            } else {
                axios.post('/json/manage-book-types', this.bookType).then(() => {
                    this.bus.emit('refreshDataTable')
                    this.bookType = {}
                    loader.hide()
                    this.$snotify.success('Book Type Added')
                }).catch(response => {
                    loader.hide()
                    this.$snotify.error(response.data)
                })
            }
        },
        startEdit(item) {
            this.bookType = JSON.parse(JSON.stringify(item))
            this.showModal = true
        },
        deleteItem(item) {
            if(!confirm('Are you sure?')) {
                return
            }
            let loader = this.$loading.show()
            axios.delete(`/json/manage-book-types/${item.id}`).then(() => {
                this.bus.emit('refreshDataTable')
                this.showModal = false
                loader.hide()
                this.$snotify.success('Book Type Deleted')
            }).catch(response => {
                loader.hide()
                this.$snotify.error(response.data)
            })
        },
        openReorderModal() {
            this.showReorderModal = true
            this.fetchBookTypesForReorder()
        },
        fetchBookTypesForReorder() {
            axios.get('/json/manage-book-types').then(res => {
                this.reorderList = res.data.paginator.data.sort((a, b) => a.sort_order - b.sort_order)
            })
        },
        onReorder(event) {},
        saveReorder() {
            let loader = this.$loading.show()
            const reordered = this.reorderList.map((item, i) => ({ id: item.id, sort_order: i + 1 }))
            axios.post('/json/manage-book-types/reorder', { order: reordered }).then(() => {
                this.bus.emit('refreshDataTable')
                this.showReorderModal = false
                loader.hide()
                this.$snotify.success('Order Saved')
            }).catch(response => {
                loader.hide()
                this.$snotify.error(response.data)
            })
        }
    }
}
</script>
