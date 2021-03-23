<template>
    <div>
        <form @submit.prevent="importGoodreadsCSVExport">
            <h3>Import Goodreads CSV Export (<a href="https://www.goodreads.com/review/import" target="_blank">Link</a>)</h3>
            <div class="mt-1em">
                <label>Choose File<br>
                    <input type="file" ref="goodreadsCSVExport" required>
                </label>
            </div>
            <div class="mt-1em">
                <button>Import</button>
            </div>
        </form>
    </div>
</template>

<script>
export default {
    methods: {
        importGoodreadsCSVExport() {
            let loader = this.$loading.show()

            let formData = new FormData()
            formData.append('goodreadsCSVExport', this.$refs.goodreadsCSVExport.files[0])

            const config = {
                headers: { 'content-type': 'multipart/form-data' }
            }

            axios.post('/json/import/goodreads-csv-export', formData, config).then(() => {
                this.$refs.goodreadsCSVExport.value = ''
                loader.hide()
                this.$snotify.success('Imported Sucessfully')
            }).catch(response => {
                this.$refs.goodreadsCSVExport.value = ''
                loader.hide()
                this.$snotify.error(response.data)
            })
        }
    }
}
</script>
