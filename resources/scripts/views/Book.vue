<template>
    <div class="book-form-container" v-if="loaded">
        <div class="book-header">
            <h1 class="book-title">{{ book.book }}</h1>
            <p class="book-author">by {{ book.author }}</p>
        </div>

        <form class="book-form">
            <div class="form-content">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select v-model="book.status" class="form-select" required>
                            <option value="TO_READ">To Read</option>
                            <option value="CURRENTLY_READING">Currently Reading</option>
                            <option value="READ">Read</option>
                            <option value="ABANDONED">Abandoned</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rating</label>
                        <select v-model="book.rating" class="form-select">
                            <option :value="null">No rating</option>
                            <option v-for="rating in ratings" :value="rating.rating">{{ rating.description }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Started Reading</label>
                        <input type="date" v-model="book.started_reading" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Completed Reading</label>
                        <input type="date" v-model="book.completed_reading" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Reading Medium</label>
                        <input type="text" v-model="book.reading_medium" class="form-input" placeholder="e.g., Hardcover, Kindle, Audiobook">
                    </div>
                </div>

                <div class="notes-section">
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <resizable-textarea>
                            <textarea v-model="book.notes" class="form-textarea" placeholder="Your thoughts about this book..." spellcheck="false"></textarea>
                        </resizable-textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import ResizableTextarea from '@/scripts/components/ResizableTextarea.vue'
import { ratings } from '@/scripts/sharedData.js'
import { setDocumentTitle } from '@/scripts/utils/title.js'

function debounce(fn, delay) {
    let timeoutID;
    return function(...args) {
        clearTimeout(timeoutID);
        timeoutID = setTimeout(() => fn.apply(this, args), delay);
    };
}

export default {
    components: {
        ResizableTextarea
    },
    props: {
        bookId: Number
    },
    data() {
        return {
            loaded: false,
            book: {},
            ratings,
            autosaveDebounced: null
        }
    },
    methods: {
        fetchBook() {
            let loader = this.$loading.show()
            axios.get(`/json/books/${this.bookId}`).then(response => {
                this.book = response.data
                if (this.book.book) {
                    setDocumentTitle(this.book.book)
                }
                loader.hide()
            })
        },
        autosaveBook() {
            axios.post(`/json/books/${this.book.id}`, this.book).then(() => {
                this.$snotify.success('Book Autosaved')
            }).catch(response => {
                this.$snotify.error(response.data)
            })
        }
    },
    watch: {
        book: {
            handler() {
                if (!this.loaded) {
                    this.loaded = true
                    return
                }
                if (!this.autosaveDebounced) {
                    this.autosaveDebounced = debounce(this.autosaveBook, 500)
                }
                this.autosaveDebounced()
            },
            deep: true
        }
    },
    created() {
        this.fetchBook()
    }
}
</script>

<style scoped>
.book-form-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.book-header {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.book-title {
    font-size: 2rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
}

.book-author {
    font-size: 1.1rem;
    color: #6b7280;
    margin: 0;
    font-weight: 400;
}

.book-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-content {
    display: flex;
    gap: 3rem;
    align-items: flex-start;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    flex: 1;
}

.notes-section {
    flex: 3;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group-full {
    grid-column: 1 / -1;
}

.form-label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.form-input,
.form-select {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 1rem;
    background: #ffffff;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-textarea {
    width: 100%;
    padding: 1rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 1rem;
    background: #ffffff;
    min-height: 250px;
    resize: vertical;
    font-family: inherit;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    line-height: 1.6;
}

.form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-textarea::placeholder {
    color: #9ca3af;
}

.form-input::placeholder {
    color: #9ca3af;
}

/* Responsive design */
@media (max-width: 1024px) {
    .form-content {
        flex-direction: column;
    }

    .form-grid,
    .notes-section {
        flex: none;
        width: 100%;
    }

    .form-textarea {
        min-height: 120px;
    }
}

@media (max-width: 768px) {
    .book-form-container {
        padding: 1rem;
        margin: 0 1rem;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .book-title {
        font-size: 1.5rem;
    }
}
</style>
