<template>
    <div class="modal-overlay" v-if="modelValue" @click.self="close">
        <div class="modal">
            <div class="modal-header">
                <h2>Add Book to My List</h2>
                <button type="button" class="modal-close" @click="close">✕</button>
            </div>
            <div class="modal-body">

                <!-- Search existing -->
                <div class="form-group">
                    <label>Search existing books</label>
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="debouncedSearch"
                        placeholder="Search by title or author..."
                        :class="{ 'has-results': searchResults.length > 0 }"
                        v-focus
                    >
                    <div v-if="searchResults.length > 0" class="search-results">
                        <div v-for="book in searchResults" :key="book.id" class="search-result">
                            <div class="result-info">
                                <span class="result-title">{{ book.name }}</span>
                                <span class="result-author">{{ book.author }} · {{ book.book_type }}</span>
                            </div>
                            <span v-if="book.user_status" :class="['badge', statusBadgeClass(book.user_status)]">{{ statusLabel(book.user_status) }}</span>
                            <template v-else-if="quickAddBookId === book.id">
                                <select v-model="quickAddStatus" class="quick-add-select">
                                    <option value="TO_READ">To Read</option>
                                    <option value="CURRENTLY_READING">Currently Reading</option>
                                    <option value="READ">Read</option>
                                    <option value="ABANDONED">Abandoned</option>
                                </select>
                                <button type="button" class="quick-add-btn" @click="quickAdd(book.id)">Add</button>
                            </template>
                            <span v-else class="badge badge-add" @click="quickAddBookId = book.id; quickAddStatus = 'CURRENTLY_READING'">+ My List</span>
                        </div>
                    </div>
                    <div v-if="searchQuery && !searching && searchResults.length === 0" class="search-no-results">
                        No books found
                    </div>
                </div>

                <div class="divider">or create a new book</div>

                <!-- Create new -->
                <div class="create-section">
                    <div class="section-title">New Book</div>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" v-model="newBook.name" placeholder="Book title">
                    </div>
                    <div class="form-row">
                        <div>
                            <label>Author</label>
                            <div class="input-with-hint">
                                <input
                                    type="text"
                                    v-model="authorQuery"
                                    @input="debouncedFetchAuthors"
                                    @focus="showAuthorDropdown = true"
                                    @blur="hideAuthorDropdown"
                                    :class="{ 'open': showAuthorDropdown && authorQuery }"
                                    placeholder="Author name"
                                >
                                <div v-if="showAuthorDropdown && authorQuery" class="autocomplete-hint">
                                    <div
                                        v-if="!exactAuthorMatch"
                                        class="autocomplete-option autocomplete-new"
                                        @mousedown.prevent="selectNewAuthor"
                                    >✦ Create "{{ authorQuery }}"</div>
                                    <div
                                        v-for="author in authorSuggestions"
                                        :key="author.id"
                                        class="autocomplete-option"
                                        @mousedown.prevent="selectAuthor(author)"
                                        v-html="highlight(author.name)"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label>Type</label>
                            <select v-model="newBook.book_type_id">
                                <option value="" disabled>Select type</option>
                                <option v-for="bt in bookTypes" :key="bt.id" :value="bt.id">{{ bt.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Add to my list as</label>
                        <select v-model="newBook.status">
                            <option value="TO_READ">To Read</option>
                            <option value="CURRENTLY_READING">Currently Reading</option>
                            <option value="READ">Read</option>
                            <option value="ABANDONED">Abandoned</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="secondary" @click="close">Cancel</button>
                <button type="button" @click="createAndAdd">Save</button>
            </div>
        </div>
    </div>
</template>

<script>
import { getBookTypes } from '@/scripts/sharedData.js'

function debounce(fn, delay) {
    let t
    return function(...args) {
        clearTimeout(t)
        t = setTimeout(() => fn.apply(this, args), delay)
    }
}

export default {
    props: { modelValue: Boolean },
    emits: ['update:modelValue', 'book-added'],
    data() {
        return {
            searchQuery: '',
            searchResults: [],
            searching: false,
            quickAddBookId: null,
            quickAddStatus: 'CURRENTLY_READING',
            newBook: { name: '', book_type_id: '', status: 'CURRENTLY_READING' },
            authorQuery: '',
            authorSuggestions: [],
            showAuthorDropdown: false,
            selectedAuthorId: null,
            authorIsNew: false,
            bookTypes: []
        }
    },
    computed: {
        exactAuthorMatch() {
            return this.authorSuggestions.some(
                a => a.name.toLowerCase() === this.authorQuery.toLowerCase()
            )
        }
    },
    watch: {
        modelValue(val) {
            if (!val) this.reset()
            if (val) this.ensureBookTypesLoaded()
        }
    },
    methods: {
        async ensureBookTypesLoaded() {
            if (this.bookTypes.length > 0) {
                return
            }

            try {
                this.bookTypes = await getBookTypes()
            } catch (error) {
                console.error('Failed to fetch book types:', error)
                this.$snotify.error('Failed to load book types')
            }
        },
        highlight(text) {
            if (!this.authorQuery) return text
            const escaped = this.authorQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
            return text.replace(new RegExp(`(${escaped})`, 'gi'), '<mark>$1</mark>')
        },
        close() {
            this.$emit('update:modelValue', false)
        },
        statusLabel(status) {
            const map = { TO_READ: 'To Read', CURRENTLY_READING: 'Currently Reading', READ: 'Read', ABANDONED: 'Abandoned' }
            return map[status] ?? status
        },
        statusBadgeClass(status) {
            const map = { TO_READ: 'badge-to-read', CURRENTLY_READING: 'badge-currently-reading', READ: 'badge-read', ABANDONED: 'badge-abandoned' }
            return map[status] ?? 'badge-read'
        },
        debouncedSearch: debounce(function() {
            this.doSearch()
        }, 300),
        async doSearch() {
            if (!this.searchQuery.trim()) { this.searchResults = []; return }
            this.searching = true
            const res = await axios.get(`/json/search/books?q=${encodeURIComponent(this.searchQuery)}`)
            this.searchResults = res.data
            this.searching = false
        },
        quickAdd(bookId) {
            let loader = this.$loading.show()
            axios.post(`/json/books/${bookId}/add-to-list`, { status: this.quickAddStatus }).then(() => {
                loader.hide()
                this.$snotify.success('Added to My List')
                this.$emit('book-added', this.quickAddStatus)
                this.close()
            }).catch(() => {
                loader.hide()
                this.$snotify.error('Failed to add book')
            })
        },
        debouncedFetchAuthors: debounce(function() {
            this.selectedAuthorId = null
            this.authorIsNew = false
            if (!this.authorQuery) { this.authorSuggestions = []; return }
            axios.get(`/json/search/authors?q=${encodeURIComponent(this.authorQuery)}`).then(res => {
                this.authorSuggestions = res.data
            })
        }, 300),
        selectAuthor(author) {
            this.authorQuery = author.name
            this.selectedAuthorId = author.id
            this.authorIsNew = false
            this.showAuthorDropdown = false
        },
        selectNewAuthor() {
            this.selectedAuthorId = null
            this.authorIsNew = true
            this.showAuthorDropdown = false
        },
        hideAuthorDropdown() {
            setTimeout(() => { this.showAuthorDropdown = false }, 150)
        },
        createAndAdd() {
            if (!this.newBook.name || !this.newBook.book_type_id || !this.authorQuery) {
                this.$snotify.error('Please fill in title, author, and type')
                return
            }
            if (!this.selectedAuthorId && !this.authorIsNew) {
                this.$snotify.error('Select an author from the list or choose to create a new one')
                return
            }

            const authors = this.selectedAuthorId
                ? [{ author_id: this.selectedAuthorId, role: null }]
                : [{ author_id: null, author_name: this.authorQuery, role: null }]

            let loader = this.$loading.show()
            axios.post('/json/manage-books', {
                name: this.newBook.name,
                book_type_id: this.newBook.book_type_id,
                cover_image_url: null,
                series: [],
                authors
            }).then(res => {
                return axios.post(`/json/books/${res.data.id}/add-to-list`, { status: this.newBook.status })
            }).then(() => {
                loader.hide()
                this.$snotify.success('Book added to My List')
                this.$emit('book-added', this.newBook.status)
                this.close()
            }).catch(() => {
                loader.hide()
                this.$snotify.error('Failed to add book')
            })
        },
        reset() {
            this.searchQuery = ''
            this.searchResults = []
            this.searching = false
            this.quickAddBookId = null
            this.quickAddStatus = 'CURRENTLY_READING'
            this.newBook = { name: '', book_type_id: '', status: 'CURRENTLY_READING' }
            this.authorQuery = ''
            this.authorSuggestions = []
            this.showAuthorDropdown = false
            this.selectedAuthorId = null
            this.authorIsNew = false
        }
    }
}
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
}

.modal {
    background: white;
    border-radius: 6px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18);
    width: 480px;
    max-width: 95vw;
    overflow: hidden;
}

.modal-header {
    padding: 1em 1.2em;
    border-bottom: 1px solid var(--color-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    font-size: 1em;
    font-weight: 700;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.2em;
    cursor: pointer;
    color: #666;
    padding: 0;
    line-height: 1;
}

.modal-body {
    padding: 1.2em;
}

.modal-footer {
    padding: 1em 1.2em;
    border-top: 1px solid var(--color-border);
    display: flex;
    justify-content: flex-end;
    gap: 0.5em;
}

label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 4px;
    color: #333;
}

input,
select {
    width: 100%;
    padding: 7px 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 13px;
    font-family: inherit;
    box-sizing: border-box;
}

input:focus,
select:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 2px var(--color-primary-focus);
}

.form-group {
    margin-bottom: 1em;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1em;
    margin-bottom: 1em;
}

/* Search input when results are showing */
input.has-results {
    border-radius: 3px 3px 0 0;
    border-bottom-color: var(--color-primary);
}

.search-results {
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 3px 3px;
    max-height: 180px;
    overflow-y: auto;
}

.search-result {
    padding: 0.6em 0.8em;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.search-result:hover {
    background: #fffbf0;
}

.search-result:last-child {
    border-bottom: none;
}

.result-info {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.result-title {
    font-weight: 600;
    font-size: 13px;
}

.result-author {
    font-size: 11px;
    color: #777;
}

.badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
}

.badge-add       { background: #e8f5e9; color: #2e7d32; cursor: pointer; }

.quick-add-select {
    width: auto;
    padding: 2px 4px;
    font-size: 11px;
    border: 1px solid #ccc;
    border-radius: 3px;
}

.quick-add-btn {
    padding: 2px 8px;
    font-size: 11px;
    margin-left: 4px;
}
.badge-to-read   { background: #e3f2fd; color: #1565c0; }
.badge-currently-reading { background: #e8f5e9; color: #2e7d32; }
.badge-read      { background: #f3e5f5; color: #6a1b9a; }
.badge-abandoned { background: #fce4ec; color: #b71c1c; }

.search-no-results {
    font-size: 12px;
    color: #888;
    padding: 0.4em 0;
}

.divider {
    display: flex;
    align-items: center;
    gap: 0.75em;
    margin: 1em 0;
    color: #aaa;
    font-size: 12px;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e0e0e0;
}

.create-section {
    background: #fafafa;
    border: 1px solid #e8e8e8;
    border-radius: 4px;
    padding: 1em;
}

.section-title {
    font-size: 12px;
    font-weight: 700;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.8em;
}

/* Author autocomplete */
.input-with-hint {
    position: relative;
}

input.open {
    border-radius: 3px 3px 0 0;
}

.autocomplete-hint {
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 3px 3px;
    max-height: 160px;
    overflow-y: auto;
}

.autocomplete-option {
    font-size: 12px;
    color: #555;
    padding: 6px 10px;
    cursor: pointer;
}

.autocomplete-option:hover {
    background: #fffbf0;
}

.autocomplete-new {
    color: #2e7d32;
    font-weight: 600;
}

.autocomplete-option :deep(mark) {
    background: #fff3cd;
    color: inherit;
    font-weight: 600;
    padding: 0;
}

button.secondary {
    background: white;
    border-color: #aaa;
    color: #555;
}
</style>
