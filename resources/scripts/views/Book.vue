<template>
    <form @submit.prevent="saveBook">
        <h3>{{ book.book }} by {{ book.author}}</h3>
        <div class="mt-1em">
            <label>Status<br>
                <select v-model="book.status" class="w-100p" required>
                    <option value="TO_READ">To Read</option>
                    <option value="CURRENTLY_READING">Currently Reading</option>
                    <option value="READ">Read</option>
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
                <input type="number" step="0.5" v-model="book.rating" class="w-100p">
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

export default {
    components: {
        ResizableTextarea
    },
    props: {
        bookId: Number
    },
    data() {
        return {
            book: {}
        }
    },
    methods: {
        fetchBook() {
            axios.get(`/json/books/${this.bookId}`).then(response => {
                this.book = response.data
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
