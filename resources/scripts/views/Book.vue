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

                    <div class="form-group">
                        <label class="form-label">Notes Type</label>
                        <select :value="book.notes_type" @change="handleNotesTypeChange" class="form-select">
                            <option :value="1">Plain Text</option>
                            <option :value="2">Date Marked</option>
                        </select>
                    </div>
                </div>

                <div class="notes-section">
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <div v-if="book.notes_type === 1"
                            class="form-textarea"
                            contenteditable="plaintext-only"
                            spellcheck="false"
                            ref="plainNotes"
                            @input="onPlainNotesInput"
                            style="min-height: 250px; outline: none; white-space: pre-wrap; word-break: break-word;">
                        </div>
                        <div v-if="book.notes_type === 2" style="display: flex; flex-direction: column; row-gap: 1rem;">
                            <div v-for="(note, index) in book.notes" :key="index" class="note-entry">
                                <div class="note-header">
                                    <input
                                        v-if="editingNoteIndex === index"
                                        type="date"
                                        v-model="editingNote.date"
                                        class="form-input note-date-input"
                                    >
                                    <label v-else>{{ formatDate(note.date) }}</label>
                                    <div class="note-actions">
                                        <button
                                            v-if="editingNoteIndex === index"
                                            @click="saveNote(index)"
                                            type="button"
                                            class="btn btn-save"
                                        >
                                            Save
                                        </button>
                                        <button
                                            v-if="editingNoteIndex === index"
                                            @click="cancelEdit()"
                                            type="button"
                                            class="btn btn-cancel"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            v-if="editingNoteIndex !== index"
                                            @click="editNote(index)"
                                            type="button"
                                            class="btn btn-edit"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            v-if="editingNoteIndex !== index && (!note.date && !note.text)"
                                            @click="deleteNote(index)"
                                            type="button"
                                            class="btn btn-delete"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                                <div
                                    v-if="editingNoteIndex === index"
                                    class="form-textarea note-textarea"
                                    contenteditable="plaintext-only"
                                    spellcheck="false"
                                    @input="editingNote.text = $event.target.innerText"
                                    ref="noteTextarea"
                                    style="min-height: 100px; outline: none; white-space: pre-wrap; word-break: break-word;"
                                ></div>
                                <div v-else style="white-space: pre-wrap; word-break: break-word;" class="note-content">{{ note.text }}</div>
                            </div>
                            <div class="add-note-section">
                                <button @click="addNote()" type="button" class="btn btn-add-note">
                                    + Add Note
                                </button>
                            </div>
                        </div>
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
import dayjs from 'dayjs'

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
            autosaveDebounced: null,
            editingNoteIndex: null,
            editingNote: {
                date: '',
                text: ''
            }
        }
    },
    methods: {
        onPlainNotesInput(e) {
            this.book.notes = e.target.innerText
        },
        fetchBook() {
            let loader = this.$loading.show()
            axios.get(`/json/books/${this.bookId}`).then(response => {
                this.book = response.data
                if (this.book.book) {
                    setDocumentTitle(this.book.book)
                }
                loader.hide()

                if (this.book.notes_type === 1) {
                    this.$nextTick(() => {
                        if (this.$refs.plainNotes) {
                            this.$refs.plainNotes.innerText = this.book.notes || ''
                        }

                        this.autoScrollPageToBottom()
                    })
                } else if (this.book.notes_type === 2) {
                    this.autoScrollPageToBottom()
                }
            })
        },
        autosaveBook() {
            axios.post(`/json/books/${this.book.id}`, this.book).then(() => {
                this.$snotify.success('Book Autosaved')
            }).catch(response => {
                this.$snotify.error(response.data)
            })
        },
        addNote() {
            const today = dayjs().format('YYYY-MM-DD')
            this.book.notes.push({
                date: today,
                text: ''
            })
            this.editNote(this.book.notes.length - 1)
        },
        editNote(index) {
            this.editingNoteIndex = index
            this.editingNote = {
                date: this.book.notes[index].date,
                text: this.book.notes[index].text
            }
            this.$nextTick(() => {
                const textarea = this.$refs.noteTextarea[0]
                if (textarea) {
                    textarea.innerText = this.editingNote.text
                }
            })
        },
        saveNote(index) {
            this.book.notes[index] = {
                date: this.editingNote.date,
                text: this.editingNote.text
            }
            this.cancelEdit()
        },
        cancelEdit() {
            this.editingNoteIndex = null
            this.editingNote = {
                date: '',
                text: ''
            }
        },
        deleteNote(index) {
            if (confirm('Are you sure you want to delete this note?')) {
                this.book.notes.splice(index, 1)
            }
        },
        handleNotesTypeChange(event) {
            const newType = parseInt(event.target.value)
            const oldType = this.book.notes_type

            if (this.book.notes && this.book.notes.length > 0) {
                if (!confirm('Changing the notes type will clear existing notes. Are you sure?')) {
                    event.target.value = oldType
                    return
                }

                if (newType === 1) {
                    this.book.notes = ''
                } else if (newType === 2) {
                    this.book.notes = []
                }
            }

            this.book.notes_type = newType
        },
        formatDate(date) {
            return dayjs(date).format('DD-MMM-YY')
        },
        autoScrollPageToBottom() {
            if (this.book.status === 'CURRENTLY_READING') {
                this.$nextTick(() => {
                    document.documentElement.scrollTo({
                        top: document.documentElement.scrollHeight,
                        behavior: 'smooth'
                    })
                })
            }
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

/* Note entry styles */
.note-entry {
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: #f9fafb;
}

.note-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.note-date-input {
    border: 0;
    background: 0;
    padding: 0;
    font: inherit;
}

.note-actions {
    display: flex;
    gap: 0.5rem;
}

.btn {
    padding: 0.375rem 0.75rem;
    border: 1px solid transparent;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-edit {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.btn-edit:hover {
    background: #2563eb;
    border-color: #2563eb;
}

.btn-delete {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}

.btn-delete:hover {
    background: #dc2626;
    border-color: #dc2626;
}

.btn-save {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.btn-save:hover {
    background: #059669;
    border-color: #059669;
}

.btn-cancel {
    background: #6b7280;
    color: white;
    border-color: #6b7280;
}

.btn-cancel:hover {
    background: #4b5563;
    border-color: #4b5563;
}

.btn-add-note {
    background: #8b5cf6;
    color: white;
    border-color: #8b5cf6;
    width: 100%;
    padding: 0.75rem;
    font-size: 1rem;
}

.btn-add-note:hover {
    background: #7c3aed;
    border-color: #7c3aed;
}

.note-content {
    background: white;
    padding: 1rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    min-height: 50px;
    line-height: 1.6;
}

.note-textarea {
    min-height: 100px;
}

.add-note-section {
    margin-top: 1rem;
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
