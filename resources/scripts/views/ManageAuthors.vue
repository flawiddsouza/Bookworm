<template>
    <ManageContainer>
        <DataTable :fields="fields" route="/json/manage-authors" item-actions-width="10em" :bus="bus">
            <template #actions>
                <button @click="showModal = true">+ Add Author</button>
            </template>
            <template #item-actions="{ item }">
                <button>View</button>
                <button class="ml-0_5em" @click="startEdit(item)">Edit</button>
                <button class="ml-0_5em" @click="deleteItem(item)">Delete</button>
            </template>
        </DataTable>
        <Modal v-model:showModal="showModal">
            <template #title>{{ modalLabel }} Author</template>
            <form @submit.prevent="addAuthor">
                <div>
                    <label>Author Name<br>
                        <input type="text" required v-model="author.name" v-focus class="w-100p">
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
                    fieldName: 'Author',
                    field: 'name'
                }
            ],
            bus: mitt(),
            showModal: false,
            author: {}
        }
    },
    computed: {
        modalLabel() {
            if('id' in this.author) {
                return 'Edit'
            } else {
                return 'Add'
            }
        }
    },
    watch: {
        showModal() {
            if(!this.showModal) {
                if('id' in this.author) {
                    this.author = {}
                }
            }
        }
    },
    methods: {
        addAuthor() {
            let loader = this.$loading.show()
            if('id' in this.author) {
                axios.put(`/json/manage-authors/${this.author.id}`, this.author).then(() => {
                    this.bus.emit('refreshDataTable')
                    this.showModal = false
                    loader.hide()
                    this.$snotify.success('Author Updated')
                }).catch(response => {
                    loader.hide()
                    this.$snotify.error(response.data)
                })
            } else {
                axios.post('/json/manage-authors', this.author).then(() => {
                    this.bus.emit('refreshDataTable')
                    this.author = {}
                    loader.hide()
                    this.$snotify.success('Author Added')
                }).catch(response => {
                    loader.hide()
                    this.$snotify.error(response.data)
                })
            }
        },
        startEdit(item) {
            this.author = JSON.parse(JSON.stringify(item))
            this.showModal = true
        },
        deleteItem(item) {
            if(!confirm('Are you sure?')) {
                return
            }
            let loader = this.$loading.show()
            axios.delete(`/json/manage-authors/${item.id}`).then(() => {
                this.bus.emit('refreshDataTable')
                this.showModal = false
                loader.hide()
                this.$snotify.success('Author Deleted')
            }).catch(response => {
                loader.hide()
                this.$snotify.error(response.data)
            })
        }
    }
}
</script>
