<template>
    <div class="search-box">
        <input type="search" v-model="search" :placeholder="placeholder" onkeyup="this.setAttribute('value', this.value)" onsearch="this.setAttribute('value', this.value)">
        <div v-if="search.length > 0">
            <div v-if="status" class="search-status">{{ status }}</div>
            <div v-for="result in results" class="search-result" :key="result.id" @click="selectResult(result)">
                <slot :result="result">
                    {{ result }}
                </slot>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        placeholder: {
            type: String,
            default: 'Search...'
        },
        url: String
    },
    data() {
        return {
            search: '',
            status: '',
            results: [],
        }
    },
    watch: {
        search() {
            if(this.search) {
                this.fetchResults()
            } else {
                this.results = []
            }
        }
    },
    emits: ['select'],
    methods: {
        selectResult(result) {
            this.$emit('select', result.id)
            this.search = ''
        },
        fetchResults() {
            this.results = []
            this.status = 'Loading...'
            axios.get(`${this.url}?q=${this.search}`).then(response => {
                this.results = response.data
                if(this.results.length === 0) {
                    this.status = 'No Results Found'
                } else {
                    this.status = ''
                }
            })
        }
    }
}
</script>

<style scoped>
.search-box {
    position: relative;
}

.search-box > input {
    width: 30em;
    padding: 5px 10px;
    border: 1px solid #d0d0d0;
    border-radius: 3px;
    font-family: inherit;
    font-size: 14px;
    transition: border-color 0.15s, border-radius 0.15s;
}

.search-box > input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 2px var(--color-primary-focus);
}

/* Flatten bottom corners when dropdown is visible */
.search-box > input:focus:not([value=""]) {
    border-radius: 3px 3px 0 0;
    border-bottom-color: var(--color-primary);
    box-shadow: none;
}

.search-box > div {
    display: none;
    position: absolute;
    top: 100%;
    background-color: white;
    width: 100%;
    padding: 0.3em 0;
    border-left: 1px solid var(--color-border);
    border-right: 1px solid var(--color-border);
    border-bottom: 1px solid var(--color-border);
    border-radius: 0 0 3px 3px;
    max-height: 15em;
    z-index: 3;
    overflow-y: auto;
    font-size: 0.9em;
}

.search-box > div:hover {
    display: block;
}

.search-status {
    padding: 0.35em 0.6em;
    color: var(--color-text-muted);
}

.search-box > div .search-result {
    padding: 0.35em 0.6em;
    cursor: pointer;
    text-decoration: none;
    display: block;
    color: var(--color-text);
}

.search-box > div .search-result:hover {
    background-color: #fffbf0;
}

.search-box > input:focus:not([value=""]) ~ div {
    display: block;
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .search-box > input {
        width: 100%;
        max-width: 100%;
        min-width: 200px;
    }

    .search-box > div {
        font-size: 0.9em;
    }
}

@media (max-width: 480px) {
    .search-box > input {
        min-width: 150px;
    }
}
</style>
