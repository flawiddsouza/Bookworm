<template>
    <ManageContainer>
        <DataTable :fields="fields" route="/json/manage-book-types" item-actions-width="10em" :bus="bus">
            <template #actions>
                <button @click="showModal = true">+ Add Book Type</button>
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
    </ManageContainer>
</template>

<script>
import ManageContainer from './ManageContainer.vue'
import DataTable from '@/scripts/components/DataTable.vue'
import mitt from 'mitt'
import Modal from '@/scripts/components/Modal.vue'

export default {
    components: {
        ManageContainer,
        DataTable,
        Modal
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
            bookType: {}
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
        }
    }
}
</script>
