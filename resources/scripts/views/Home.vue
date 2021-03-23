<template>
    <div>
        <div class="tabs mb-1em">
            <div @click="activeTab = tab.filter" :class="{ 'tab-active': activeTab === tab.filter }" v-for="tab in tabs">{{ tab.name }}</div>
        </div>
        <div>
            <DataTable :fields="fields" :route="`/json/user/books?status=${activeTab}`" item-actions-width="10em" :bus="bus">
                <template #item-actions="{ item }">
                    <button>View</button>
                    <button class="ml-0_5em" @click="startEdit(item)">Edit</button>
                    <button class="ml-0_5em" @click="deleteItem(item)">Delete</button>
                </template>
            </DataTable>
        </div>
    </div>
</template>

<script>
import DataTable from '@/scripts/components/DataTable.vue'
import mitt from 'mitt'

export default {
    components: {
        DataTable
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
            bus: mitt()
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
                        field: 'started_reading'
                    },
                    {
                        fieldName: 'Completed Reading',
                        field: 'completed_reading'
                    }
                ])
            }

            if(this.activeTab === 'CURRENTLY_READING') {
                fields.push({
                    fieldName: 'Started Reading',
                    field: 'started_reading'
                })
            }

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
            //
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
