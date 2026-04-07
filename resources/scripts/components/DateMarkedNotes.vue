<template>
    <div style="display: flex; flex-direction: column; row-gap: 1rem;">
        <div v-for="(note, index) in notes" :key="index" class="note-entry">
            <div class="note-header">
                <input
                    v-if="editingNoteIndex === index"
                    type="date"
                    v-model="editingNote.date"
                    class="form-input note-date-input"
                >
                <label v-else>{{ formatDate(note.date) }}</label>
                <div class="note-actions">
                    <button v-if="editingNoteIndex === index" @click="saveNote(index)" type="button" class="btn btn-save">Save</button>
                    <button v-if="editingNoteIndex === index" @click="cancelEdit()" type="button" class="btn btn-cancel">Cancel</button>
                    <button v-if="editingNoteIndex !== index" @click="editNote(index)" type="button" class="btn btn-edit">Edit</button>
                    <button v-if="editingNoteIndex !== index && (!note.date && !note.text)" @click="deleteNote(index)" type="button" class="btn btn-delete">Delete</button>
                </div>
            </div>
            <div
                v-if="editingNoteIndex === index"
                class="form-textarea note-textarea"
                contenteditable="plaintext-only"
                spellcheck="false"
                @input="editingNote.text = $event.target.innerText"
                @blur="handleNoteBlur($event, index)"
                @keydown="handleNoteKeydown($event)"
                ref="noteTextarea"
                style="min-height: 100px; outline: none; white-space: pre-wrap; word-break: break-word;"
            ></div>
            <div
                v-else
                style="white-space: pre-wrap; word-break: break-word;"
                class="note-content"
                @click="editNote(index)"
            >
                {{ note.text }}
            </div>
        </div>
        <div class="add-note-section">
            <button @click="addNote()" type="button" class="btn btn-add-note">+ Add Note</button>
        </div>
    </div>
</template>

<script>
import dayjs from 'dayjs'

export default {
    props: {
        modelValue: {
            type: Array,
            required: true
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            notes: JSON.parse(JSON.stringify(this.modelValue)),
            editingNoteIndex: null,
            editingNote: {
                date: '',
                text: ''
            }
        }
    },
    watch: {
        modelValue(val) {
            this.notes = JSON.parse(JSON.stringify(val))
        }
    },
    methods: {
        addNote() {
            const today = dayjs().format('YYYY-MM-DD')
            this.notes.push({ date: today, text: '' })
            this.$emit('update:modelValue', this.notes)
            this.editNote(this.notes.length - 1)
        },
        editNote(index) {
            this.editingNoteIndex = index
            this.editingNote = {
                date: this.notes[index].date,
                text: this.notes[index].text
            }
            this.$nextTick(() => {
                const textarea = this.$refs.noteTextarea[0]
                if (textarea) {
                    textarea.innerText = this.editingNote.text
                    const range = document.createRange()
                    range.selectNodeContents(textarea)
                    range.collapse(false)
                    const sel = window.getSelection()
                    sel.removeAllRanges()
                    sel.addRange(range)
                }
            })
        },
        saveNote(index) {
            this.notes[index] = {
                date: this.editingNote.date,
                text: this.editingNote.text
            }
            this.$emit('update:modelValue', this.notes)
            this.cancelEdit()
        },
        cancelEdit() {
            this.editingNoteIndex = null
            this.editingNote = { date: '', text: '' }
        },
        deleteNote(index) {
            if (confirm('Are you sure you want to delete this note?')) {
                this.notes.splice(index, 1)
                this.$emit('update:modelValue', this.notes)
            }
        },
        handleNoteBlur(e, index) {
            const next = e.relatedTarget
            if (!next || !next.classList) {
                this.saveNote(index)
                return
            }
            if (
                next.classList.contains('note-date-input') ||
                next.classList.contains('btn-save') ||
                next.classList.contains('btn-cancel')
            ) {
                return
            }
            this.saveNote(index)
        },
        handleNoteKeydown(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                e.preventDefault()
                if (this.$refs.noteTextarea && this.$refs.noteTextarea[0]) {
                    this.$refs.noteTextarea[0].blur()
                }
            }
        },
        formatDate(date) {
            return dayjs(date).format('DD-MMM-YY')
        }
    }
}
</script>

<style scoped>
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

.btn-edit:hover { background: #2563eb; border-color: #2563eb; }

.btn-delete {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}

.btn-delete:hover { background: #dc2626; border-color: #dc2626; }

.btn-save {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.btn-save:hover { background: #059669; border-color: #059669; }

.btn-cancel {
    background: #6b7280;
    color: white;
    border-color: #6b7280;
}

.btn-cancel:hover { background: #4b5563; border-color: #4b5563; }

.btn-add-note {
    background: #8b5cf6;
    color: white;
    border-color: #8b5cf6;
    width: 100%;
    padding: 0.75rem;
    font-size: 1rem;
}

.btn-add-note:hover { background: #7c3aed; border-color: #7c3aed; }

.form-input {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 1rem;
    background: #ffffff;
}

.form-textarea {
    width: 100%;
    padding: 1rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 1rem;
    background: #ffffff;
    font-family: inherit;
    line-height: 1.6;
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
</style>
