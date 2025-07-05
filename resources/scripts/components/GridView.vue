<template>
    <div class="gridview">
        <div class="d-f flex-jc-sb flex-ai-fs">
            <div>
                <slot name="actions"></slot>
            </div>
            <div>
                <button @click="refreshData"><i class="fas fa-sync-alt" :class="{ 'fa-spin': refreshing }"></i> Refresh</button>
                <input type="search" v-model="filter" @input="filterItems" class="mt-a ml-0_5em" placeholder="Search...">
            </div>
        </div>
        <br>

        <div class="grid-container-wrapper" :style="{ height: tableHeight }">
            <div v-if="!route || loading" class="ta-c">Loading</div>
            <div v-else-if="items.length === 0" class="ta-c">No records found</div>
            <div v-else class="grid-container">
                <div v-for="item in items" :key="item.id" class="grid-card">
                    <div class="card-content">
                        <div v-if="item.cover_image_url" class="card-image">
                            <img :src="item.cover_image_url" :alt="item.display_name || item.book || item.name">
                        </div>
                        <div class="card-details">
                            <h3>{{ item.display_name || item.book || item.name }}</h3>
                            <div v-if="item.author" class="field-row">
                                <span class="field-label">Author:</span>
                                <span class="field-value">{{ item.author }}</span>
                            </div>
                            <div v-if="item.book_type" class="field-row">
                                <span class="field-label">Type:</span>
                                <span class="field-value">{{ item.book_type }}</span>
                            </div>
                            <div v-if="item.started_reading_display" class="field-row">
                                <span class="field-label">Started:</span>
                                <span class="field-value">{{ item.started_reading_display }}</span>
                            </div>
                            <div v-if="item.completed_reading_display" class="field-row">
                                <span class="field-label">Completed:</span>
                                <span class="field-value">{{ item.completed_reading_display }}</span>
                            </div>
                            <div v-if="item.rating_display" class="field-row">
                                <span class="field-label">Rating:</span>
                                <span class="field-value">{{ item.rating_display }}</span>
                            </div>
                            <div v-if="item.reading_medium" class="field-row">
                                <span class="field-label">Medium:</span>
                                <span class="field-value">{{ item.reading_medium }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <slot name="item-actions" :item="item"></slot>
                    </div>
                </div>
            </div>
        </div>

        <br>
        <div class="f-l">
            <template v-if="paginator.total > 0">
                From {{ paginator.from }} to {{ paginator.to }} of {{ paginator.total }} entries (filtered from {{ paginator.unfilteredTotal }} total records)
            </template>
            <template v-else>
                No records were found (filtered from {{ paginator.unfilteredTotal }} total records)
            </template>
        </div>
        <div class="paginator f-r" :class="{ 'disable-all': loading }">
            <button @click="fetchPage(paginator.firstPage)" :disabled="paginator.firstPage === paginator.currentPage">First</button>
            <button @click="fetchPage(paginator.currentPage - 1)" :disabled="paginator.firstPage === paginator.currentPage">&lt;</button>
            <button @click="fetchPage(pageSwitch - 3)" :class="{active: (pageSwitch - 3) === paginator.currentPage}" v-if="paginator.lastPage >= pageSwitch - 3">{{ pageSwitch - 3 }}</button>
            <button @click="fetchPage(pageSwitch - 2)" :class="{active: (pageSwitch - 2) === paginator.currentPage}" v-if="paginator.lastPage >= pageSwitch - 2">{{ pageSwitch -2 }}</button>
            <button @click="fetchPage(pageSwitch - 1)" :class="{active: (pageSwitch - 1) === paginator.currentPage}" v-if="paginator.lastPage >= pageSwitch - 1">{{ pageSwitch - 1 }}</button>
            <button @click="fetchPage(pageSwitch)" :class="{active: pageSwitch === paginator.currentPage}" v-if="paginator.lastPage >= pageSwitch">{{ pageSwitch }}</button>
            <template v-if="paginator.lastPage > 4">
                <span>...</span>
                <button @click="fetchPage(paginator.lastPage - 1)" v-if="(paginator.lastPage - 1) > 0 && paginator.firstPage !== (paginator.lastPage - 1) && (paginator.lastPage - 1) > 4" :class="{active: (paginator.lastPage - 1) === paginator.currentPage}">{{ paginator.lastPage - 1 }}</button>
                <button @click="fetchPage(paginator.lastPage)" v-if="paginator.lastPage !== paginator.firstPage" :class="{active: paginator.lastPage === paginator.currentPage}">{{ paginator.lastPage }}</button>
            </template>
            <button @click="fetchPage(paginator.currentPage + 1)" :disabled="paginator.lastPage === paginator.currentPage">&gt;</button>
            <button @click="fetchPage(paginator.lastPage)" :disabled="paginator.lastPage === paginator.currentPage">Last</button>
        </div>
        <div class="cb"></div>
    </div>
</template>

<script>
export default {
    props: {
        route: String,
        bus: Object,
        tableHeight: String
    },
    data() {
        return {
            filter: '',
            items: [],
            paginator: {
                currentPage: null,
                firstPage: 1,
                lastPage: null,
                from: null,
                to: null,
                total: null,
                unfilteredTotal: null
            },
            pageSwitch: 4,
            loading: false,
            refreshing: false,
            timeout: null
        }
    },
    watch: {
        route() {
            if(this.route) {
                this.fetchPage(1, true, true)
            }
        }
    },
    methods: {
        filterItems() {
            clearTimeout(this.timeout)
            this.timeout = setTimeout(() => {
                this.pageSwitch = 4
                this.fetchPage(1, false, true)
            }, 250)
        },
        fetchPage(page, created=false, bypassCurrentPageCheck=false) {
            if(page === this.paginator.currentPage && !bypassCurrentPageCheck) {
                return
            }

            if(page === this.paginator.firstPage) {
                this.pageSwitch = 4
            }
            if(page === this.paginator.lastPage) {
                let pageSwitch = this.paginator.lastPage - 2
                if(pageSwitch >= 4) {
                    this.pageSwitch = pageSwitch
                }
            }
            if(page === (this.paginator.lastPage - 1)) {
                let pageSwitch = (this.paginator.lastPage - 1) - 1
                if(pageSwitch >= 4) {
                    this.pageSwitch = pageSwitch
                }
            }
            if(this.paginator.lastPage <= 4 && this.pageSwitch < this.paginator.lastPage) {
                this.pageSwitch = 4
            }
            if(!created) {
                if(page == this.pageSwitch) {
                    if(this.paginator.lastPage - 2 !== this.pageSwitch && !(this.pageSwitch + 1 > this.paginator.lastPage)) {
                        if(this.paginator.lastPage > 5) {
                            this.pageSwitch++
                        }
                    }
                }
                if(page < this.paginator.currentPage) {
                    if(this.pageSwitch-1 > 3 && this.pageSwitch-1 < this.paginator.lastPage - 3) {
                        this.pageSwitch--
                    }
                    if(this.paginator.currentPage === this.paginator.lastPage - 4) {
                        if(this.pageSwitch-1 >= 4) {
                            this.pageSwitch--
                        }
                    }
                    if(page === this.pageSwitch - 3 && page > 1) {
                        this.pageSwitch--
                    }
                }
            }
            this.paginator.currentPage = page
            this.loading = true

            const baseRoute = this.route.includes('?') ? this.route + '&' : this.route + '?'

            axios.get(`${baseRoute}page=${page}&filter=${encodeURIComponent(this.filter)}`).then(response => {
                const data = response.data
                if(!data.paginator) {
                    console.error('GridView: Route does not implement laravel pagination')
                    return
                }

                this.items = data.paginator.data
                this.paginator.currentPage = data.paginator.current_page
                this.paginator.lastPage = data.paginator.last_page
                this.paginator.from = data.paginator.from
                this.paginator.to = data.paginator.to
                this.paginator.total = data.paginator.total
                this.paginator.unfilteredTotal = data.unfiltered_total
                this.loading = false
                this.refreshing = false
            })
        },
        refreshData() {
            this.refreshing = true
            this.fetchPage(this.paginator.currentPage, false, true)
        }
    },
    created() {
        if(this.route) {
            this.fetchPage(1, true)
        }
        if(this.bus) {
            this.bus.on('refreshDataTable', () => {
                this.refreshData()
            })
        }
    }
}
</script>

<style scoped>
.gridview {
    font-size: 13px;
}

.grid-container-wrapper {
    height: calc(100vh - 18rem);
    overflow-y: auto;
    margin-bottom: 1rem;
}

.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
    padding-bottom: 1rem;
}

.grid-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.grid-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.card-content {
    padding: 1rem;
    flex: 1;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.card-image {
    flex-shrink: 0;
    width: 80px;
}

.card-image img {
    width: 100%;
    max-height: 120px;
    object-fit: cover;
    border-radius: 4px;
}

.card-details {
    flex: 1;
}

.card-details h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1em;
    color: #333;
    line-height: 1.3;
}

.field-row {
    margin-bottom: 0.25rem;
    font-size: 0.9em;
}

.field-label {
    font-weight: 600;
    color: #666;
}

.field-value {
    color: #333;
    margin-left: 0.25rem;
}

.card-actions {
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 0.5rem;
}

.card-actions button {
    font-size: 0.85em;
    padding: 0.25rem 0.5rem;
}

.paginator button {
    margin-right: 0.3em;
}

.paginator button:last-child {
    margin-right: 0;
}

.paginator button:not(.active) {
    outline: 0;
}

.disable-all {
    pointer-events: none;
    opacity: 0.6;
}

.gridview input[type="search"] {
    padding: 5.6px 10px;
    border: 1px solid darkgrey;
    border-radius: 3px;
}

.gridview input[type="search"]:focus {
    outline: 0;
}
</style>
