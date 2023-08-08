<template>
    <form @submit.prevent="saveBook">
        <h3>{{ book.book }} by {{ book.author}}</h3>
        <div class="mt-1em">
            <label>Status<br>
                <select v-model="book.status" class="w-100p" required>
                    <option value="TO_READ">To Read</option>
                    <option value="CURRENTLY_READING">Currently Reading</option>
                    <option value="READ">Read</option>
                    <option value="ABANDONED">Abandoned</option>
                </select>
            </label>
        </div>
        <div class="mt-1em">
            <label>Started Reading<br>
                <input type="date" v-model="book.started_reading" class="w-100p">
            </label>
        </div>
        <div class="mt-1em">
            <label>Completed Reading<br>
                <input type="date" v-model="book.completed_reading" class="w-100p">
            </label>
        </div>
        <div class="mt-1em">
            <label>Rating<br>
                <select v-model="book.rating" class="w-100p">
                    <option v-for="rating in ratings" :value="rating.rating">{{ rating.description }}</option>
                </select>
            </label>
        </div>
        <div class="mt-1em">
            <label>Private Notes<br>
                <resizable-textarea>
                    <textarea v-model="book.private_notes" class="w-100p"></textarea>
                </resizable-textarea>
            </label>
        </div>
        <div class="mt-1em">
            <label>Public Notes<br>
                <resizable-textarea>
                    <textarea v-model="book.public_notes" class="w-100p"></textarea>
                </resizable-textarea>
            </label>
        </div>
        <div class="mt-1em">
            <label>Reading Medium<br>
                <input type="text" v-model="book.reading_medium" class="w-100p">
            </label>
        </div>
        <div class="mt-1em"></div>
        <button class="mt-1em">Save</button>
    </form>
</template>

<script>
import ResizableTextarea from '@/scripts/components/ResizableTextarea.vue'
import { ratings } from '@/scripts/sharedData'

export default {
    components: {
        ResizableTextarea
    },
    props: {
        bookId: Number
    },
    data() {
        return {
            book: {},
            ratings
        }
    },
    methods: {
        fetchBook() {
            let loader = this.$loading.show()
            axios.get(`/json/books/${this.bookId}`).then(response => {
                this.book = response.data
                // trigger input events for textareas, as this.book = doesn't seem to trigger the event on any of the textareas
                // the input event helps trigger resize on resizable textareas
                this.$nextTick(() => {
                    Array.from(document.querySelectorAll('textarea')).forEach(textarea => {
                        textarea.dispatchEvent(new Event('input'))
                    })
                })
                loader.hide()
            })
        },
        saveBook() {
            let loader = this.$loading.show()
            axios.post(`/json/books/${this.book.id}`, this.book).then(() => {
                loader.hide()
                this.$snotify.success('Book Saved')
            }).catch(response => {
                loader.hide()
                this.$snotify.error(response.data)
            })
        }
    },
    created() {
        this.fetchBook()
    }
}
</script>
