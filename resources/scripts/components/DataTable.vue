<template>
    <div class="datatable">
        <div class="d-f flex-jc-sb flex-ai-fs dt-controls">
            <div>
                <slot name="actions"></slot>
            </div>
            <div class="dt-controls-right">
                <button @click="refreshDataTable"><i class="fas fa-sync-alt" :class="{ 'fa-spin': refreshingDataTable }"></i> Refresh</button>
                <input type="search" v-model="filter" @input="filterItems" class="mt-a ml-0_5em" placeholder="Search...">
            </div>
        </div>
        <br>
        <div class="table-container" :style="{ height: tableHeight }">
            <table ref="tableBody">
                <thead>
                    <tr>
                        <th v-if="checkboxes" style="width: 45px; min-width: 45px">
                            <input type="checkbox" v-if="items.length > 0" @change="selectAllCheckboxes" ref="selectAllCheckboxesInput">
                        </th>
                        <th v-for="field in fields" :class="[{ active: sortField === field.field }, fieldClass ? fieldClass[field.field] : null]" :style="{ width: field.width ?? false }" @click="sortColumn(field.field)">
                            {{ field.fieldName }}
                            <span class="arrow" :class="sortOrder === 'asc' ? 'asc' : 'desc'"></span>
                        </th>
                        <th v-if="itemActionsSlotExists" :style="{ width: itemActionsWidth, minWidth: itemActionsWidth }">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!route || loading">
                        <td colspan="100%" class="ta-c">Loading</td>
                    </tr>
                    <template v-else>
                        <tr v-for="(item, index) in items">
                            <td class="ta-c" v-if="checkboxes" style="width: 45px; min-width: 45px">
                                <input type="checkbox" :value="item[checkboxField]" v-model="selectedCheckboxesVirtual">
                            </td>
                            <template v-for="field in fields">
                                <template v-if="fieldMutations && fieldMutations[field.field]">
                                    <td :class="fieldClass ? fieldClass[field.field] : null" :style="{ textAlign: fieldAlign ? fieldAlign[field.field] : false }">{{ fieldMutations[field.field](item[field.field]) }}</td>
                                </template>
                                <template v-else-if="fieldHtmlMutations && fieldHtmlMutations[field.field]">
                                    <td :class="fieldClass ? fieldClass[field.field] : null" :style="{ textAlign: fieldAlign ? fieldAlign[field.field] : false }" v-html="fieldHtmlMutations[field.field](item[field.field])"></td>
                                </template>
                                <template v-else>
                                    <template v-if="fieldImage && fieldImage[field.field]">
                                        <td v-if="item[field.field]" :class="fieldClass ? fieldClass[field.field] : null" :style="{ textAlign: fieldAlign ? fieldAlign[field.field] : false }">
                                            <img :style="{ width: fieldImage[field.field].width }" :src="fieldImage[field.field].path + '/' + item[field.field]">
                                        </td>
                                        <td v-else :class="fieldClass ? fieldClass[field.field] : null"></td>
                                    </template>
                                    <template v-else>
                                        <td v-if="fieldSubtitutions && fieldSubtitutions[field.field]" :class="fieldClass ? fieldClass[field.field] : null" :style="{ textAlign: fieldAlign ? fieldAlign[field.field] : false }">{{ fieldSubtitutions[field.field][item[field.field]] }}</td>
                                        <template v-else>
                                            <td v-if="fieldHtml && fieldHtml.includes(field.field)" :class="fieldClass ? fieldClass[field.field] : null" :style="{
                                                textAlign: fieldAlign ? fieldAlign[field.field] : false,
                                                width: field.width ?? false,
                                                maxWidth: field.width ?? false,
                                                minWidth: field.width ?? false,
                                                whiteSpace: field.whiteSpace ?? false
                                            }" v-html="item[field.field]"></td>
                                            <td v-else :class="fieldClass ? fieldClass[field.field] : null" :style="{
                                                textAlign: fieldAlign ? fieldAlign[field.field] : false,
                                                width: field.width ?? false,
                                                maxWidth: field.width ?? false,
                                                minWidth: field.width ?? false,
                                                whiteSpace: field.whiteSpace ?? false
                                            }">{{ item[field.field] }}</td>
                                        </template>
                                    </template>
                                </template>
                            </template>
                            <td class="ta-c actions-cell" v-if="itemActionsSlotExists && index === 0" :style="{ width: itemActionsWidth, minWidth: itemActionsWidth }">
                                <slot name="item-actions" :item="item"></slot>
                            </td>
                            <td class="ta-c actions-cell" v-if="itemActionsSlotExists && index !== 0">
                                <slot name="item-actions" :item="item"></slot>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="100%" class="ta-c">No records found</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <br>
        <div class="f-l">
            <template v-if="paginator.total > 0">
                From {{ paginator.from }} to {{ paginator.to }} of {{ paginator.total }} entries (filtered from {{ paginator.unfilteredTotal }} total records) {{ selectedItemsCountText }}
            </template>
            <template v-else>
                No records were found (filtered from {{ paginator.unfilteredTotal }} total records)
            </template>
        </div>
        <div class="paginator f-r" :class="{ 'disable-all': loading }">
            <button class="paginator-first" @click="fetchItemsForPage(paginator.firstPage)" :disabled="paginator.firstPage === paginator.currentPage">First</button>
            <button @click="fetchItemsForPage(paginator.currentPage - 1)" :disabled="paginator.firstPage === paginator.currentPage">&lt;</button>
            <button class="paginator-number" @click="fetchItemsForPage(pageSwitch - 3)" :class="{active: (pageSwitch - 3) === paginator.currentPage}" v-if="paginator.lastPage >= pageSwitch - 3">{{ pageSwitch - 3 }}</button>
            <button class="paginator-number" @click="fetchItemsForPage(pageSwitch - 2)" :class="{active: (pageSwitch - 2) === paginator.currentPage}" v-if="paginator.lastPage >= pageSwitch - 2">{{ pageSwitch - 2 }}</button>
            <button class="paginator-number" @click="fetchItemsForPage(pageSwitch - 1)" :class="{active: (pageSwitch - 1) === paginator.currentPage}" v-if="paginator.lastPage >= pageSwitch - 1">{{ pageSwitch - 1 }}</button>
            <button class="paginator-number" @click="fetchItemsForPage(pageSwitch)" :class="{active: pageSwitch === paginator.currentPage}" v-if="paginator.lastPage >= pageSwitch">{{ pageSwitch }}</button>
            <template v-if="paginator.lastPage > 4">
                <span class="paginator-ellipsis">...</span>
                <button class="paginator-number" @click="fetchItemsForPage(paginator.lastPage - 1)" v-if="(paginator.lastPage - 1) > 0 && paginator.firstPage !== (paginator.lastPage - 1) && (paginator.lastPage - 1) > 4" :class="{active: (paginator.lastPage - 1) === paginator.currentPage}">{{ paginator.lastPage - 1 }}</button>
                <button class="paginator-number" @click="fetchItemsForPage(paginator.lastPage)" v-if="paginator.lastPage !== paginator.firstPage" :class="{active: paginator.lastPage === paginator.currentPage}">{{ paginator.lastPage }}</button>
            </template>
            <span class="paginator-info">Page {{ paginator.currentPage }} of {{ paginator.lastPage }}</span>
            <button @click="fetchItemsForPage(paginator.currentPage + 1)" :disabled="paginator.lastPage === paginator.currentPage">&gt;</button>
            <button class="paginator-last" @click="fetchItemsForPage(paginator.lastPage)" :disabled="paginator.lastPage === paginator.currentPage">Last</button>
        </div>
    </div>
</template>

<script>
function debounce(func, wait, immediate) {
    var timeout
    return function() {
        var context = this, args = arguments
        var later = function() {
            timeout = null
            if (!immediate) func.apply(context, args)
        }
        var callNow = immediate && !timeout
        clearTimeout(timeout)
        timeout = setTimeout(later, wait)
        if (callNow) func.apply(context, args)
    }
}


export default {
    props: {
        route: String,
        fields: Array,
        itemActionsWidth: String,
        bus: Object,
        tableHeight: String,
        fieldSubtitutions: Object,
        fieldImage: Object,
        fieldAlign: Object,
        fieldMutations: Object,
        fieldHtmlMutations: Object,
        fieldClass: Object,
        fieldHtml: Array,
        limit: Number,
        checkboxes: Boolean,
        checkboxField: String,
        selectedCheckboxes: Array,
        hideItemActionsColumn: Boolean
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
            sortField: '',
            previousSortField: '',
            sortOrder: 'asc',
            loading: false,
            refreshingDataTable: false,
            selectedCheckboxesVirtual: [],
            currentDataFreshnessTimestamp: null
        }
    },
    watch: {
        route() {
            if(this.route) {
                // reset sort
                this.sortField = ''
                this.previousSortField = ''
                this.sortOrder = 'asc'
                this.fetchItemsForPage(1, true, true)
            }
        },
        selectedCheckboxesVirtual() {
            this.$emit('update:selectedCheckboxes', this.selectedCheckboxesVirtual)
        }
    },
    computed: {
        itemActionsSlotExists() {
            return this.$slots['item-actions'] && this.hideItemActionsColumn === false
        },
        selectedItemsCountText() {
            if(this.selectedCheckboxes && this.selectedCheckboxes.length > 0) {
                return `(${this.selectedCheckboxes.length} selected)`
            } else {
                return ''
            }
        }
    },
    methods: {
        debounce(func, wait, immediate) {
            var timeout
            return function() {
                var context = this, args = arguments
                var later = function() {
                    timeout = null
                    if (!immediate) func.apply(context, args)
                }
                var callNow = immediate && !timeout
                clearTimeout(timeout)
                timeout = setTimeout(later, wait)
                if (callNow) func.apply(context, args)
            }
        },
        debouncedFetch: debounce(function() {
            this.fetchItemsForPage(1, false, true)
        }, 250),
        filterItems() {
            this.pageSwitch = 4
            this.debouncedFetch()
        },
        sortColumn(field) {
            this.sortField = field
            if(this.previousSortField !== this.sortField) {
                this.sortOrder = 'asc'
            } else {
                if(this.sortOrder === 'asc') {
                    this.sortOrder = 'desc'
                } else {
                    this.sortOrder = 'asc'
                }
            }
            this.previousSortField = this.sortField
            this.fetchItemsForPage(1, false, true)
        },
        fetchItemsForPage(page, created=false, bypassCurrentPageCheck=false) {
            if(page === this.paginator.currentPage && !bypassCurrentPageCheck) { // dont fetch when requesting same page as the current page
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
            var route = this.route.split('?')
            if(route.length > 1) {
                route = this.route + '&'
            } else {
                route = this.route + '?'
            }

            if(this.checkboxes) { // reset checkboxes
                this.selectedCheckboxesVirtual = []
                this.$emit('update:selectedCheckboxes', [])
                if(this.$refs.selectAllCheckboxesInput) {
                    this.$refs.selectAllCheckboxesInput.checked = false
                }
            }

            let currentDataFreshnessTimestamp = new Date().getTime()

            axios.get(`${route}page=${page}&filter=${encodeURIComponent(this.filter)}&sort_by=${this.sortField}&sort_order=${this.sortOrder}&limit=${this.limit ? this.limit : ''}`).then(response => {
                response = response.data
                if(!response.paginator) {
                    console.error('%cDataTable', 'font-weight: bold', 'Given route does not implement laravel pagination')
                    return
                }

                if(this.currentDataFreshnessTimestamp && this.currentDataFreshnessTimestamp > currentDataFreshnessTimestamp) {
                    return
                } else {
                    this.currentDataFreshnessTimestamp = currentDataFreshnessTimestamp
                }

                this.items = response.paginator.data
                this.paginator.currentPage = response.paginator.current_page
                this.paginator.lastPage = response.paginator.last_page
                this.paginator.from = response.paginator.from
                this.paginator.to = response.paginator.to
                this.paginator.total = response.paginator.total
                this.paginator.unfilteredTotal = response.unfiltered_total
                this.loading = false
                this.refreshingDataTable = false
                this.$emit('loaded')
            })
        },
        refreshDataTable() {
            this.refreshingDataTable = true
            this.fetchItemsForPage(this.paginator.currentPage, false, true)
        },
        selectAllCheckboxes(e) {
            this.selectedCheckboxesVirtual = []
            if(e.target.checked) {
                this.items.forEach(item => {
                    this.selectedCheckboxesVirtual.push(item[this.checkboxField])
                })
            }
        }
    },
    created() {
        if(this.route) {
            this.fetchItemsForPage(1, true)
        }
        if(this.bus) {
            this.bus.on('refreshDataTable', () => {
                this.refreshDataTable()
            })
        }
        if(this.checkboxes) {
            this.$emit('update:selectedCheckboxes', [])
        }
    }
}
</script>

<style scoped>
.datatable {
    font-size: var(--font-size-base);
}

.datatable .table-container {
    max-height: calc(100vh - 23em);
    overflow-x: auto;
    overflow-y: auto;
    border-bottom: 1px solid var(--color-border);
}

.datatable table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-left: 1px solid var(--color-border);
}

.datatable table th,
.datatable table td {
    overflow: hidden;
    border-right: 1px solid var(--color-border);
    border-bottom: 1px solid var(--color-border);
}

.datatable table th {
    white-space: nowrap;
}

.datatable table td.actions-cell {
    white-space: nowrap;
}

.datatable table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    border-top: 1px solid var(--color-border);
}

.datatable button {
    min-width: 40px;
}

.datatable .arrow {
    display: inline-block;
    vertical-align: middle;
    width: 0;
    height: 0;
    margin-left: 5px;
    opacity: 0.4;
}

.datatable .active .arrow {
    opacity: 1;
}

.datatable .arrow.asc {
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-bottom: 4px solid black;
}

.datatable .arrow.desc {
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 4px solid black;
}

.datatable .paginator button:not(.active):not(:disabled) {
    background: white;
    border-color: #aaa;
    color: #333;
    outline: 0;
}

.datatable .paginator button.active {
    background: var(--color-primary);
    border: 2px solid #000;
    font-weight: 700;
}

.datatable .paginator button:disabled {
    background: #f0f0f0 !important;
    border-color: #ddd !important;
    color: #bbb !important;
}

.datatable input[type="search"] {
    padding: 5.6px 10px;
    border: 1px solid var(--color-border);
    border-radius: 3px;
}

.datatable input[type="search"]:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 2px var(--color-primary-focus);
}

.datatable > .table-container > table th,
.datatable > .table-container > table td {
    padding: 8px 10px;
    color: #272727;
}

.datatable > .table-container > table th {
    text-align: left;
    background: #fafafa;
    font-weight: 600;
}

.datatable > .table-container > table tbody tr:hover td {
    background: #fffbf0;
}

.datatable > .table-container > table tbody tr:last-child td {
    border-bottom: none;
}

/* Ghost style for row-action buttons inside tbody.
   Must use :deep() because buttons come from parent slot content,
   which carries the parent component's scope attribute, not DataTable's. */
.datatable :deep(tbody button) {
    background: white;
    border: 1px solid #aaa;
    color: #333;
    min-width: unset;
}

.datatable :deep(tbody button:hover) {
    background: #f5f5f5;
    border-color: #888;
}

/* Keep add-list button green even inside tbody */
.datatable :deep(tbody button.add-list) {
    background: #e8f5e9;
    border-color: #66bb6a;
    color: #2e7d32;
}

.datatable :deep(tbody button.add-list:hover) {
    background: #c8e6c9;
}

.paginator button {
    margin-right: 0.3em;
}

.paginator button:last-child {
    margin-right: 0;
}

@media (max-width: 768px) {
    .datatable > .table-container > table {
        width: max-content;
        min-width: 100%;
    }

    .dt-controls {
        flex-wrap: wrap;
        gap: 0.5em;
    }

    .dt-controls-right {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 0.5em;
    }

    .dt-controls-right input[type="search"] {
        flex: 1;
        min-width: 0;
        margin-left: 0;
    }

    .paginator-first,
    .paginator-last,
    .paginator-number,
    .paginator-ellipsis { display: none; }
}
</style>
