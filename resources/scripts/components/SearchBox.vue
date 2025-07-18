<template>
    <div class="search-box">
        <input type="search" v-model="search" :placeholder="placeholder" onkeyup="this.setAttribute('value', this.value)" onsearch="this.setAttribute('value', this.value)">
        <div>
            <div v-if="status">{{ status }}</div>
            <div v-for="result in results" class="search-result" :key="result.id">
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
    methods: {
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

.search-box > div {
    display: none;
    position: absolute;
    top: 25px;
    background-color: white;
    width: 100%;
    padding: 0.5em;
    border-left: 1px solid lightgrey;
    border-right: 1px solid lightgrey;
    border-bottom: 1px solid lightgrey;
    max-height: 15em;
    z-index: 1;
    overflow-y: auto;
    font-size: 0.9em;
}

.search-box > div:hover {
    display: block;
}

.search-box > div .search-result {
    padding: 0.3em 0.5em;
    cursor: pointer;
    text-decoration: none;
    display: block;
    color: black;
}

.search-box > div .search-result:hover {
    background-color: antiquewhite;
}

.search-box > input:focus:not([value=""]) ~ div {
    display: block;
}

.search-box > input {
    width: 30em;
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
