<script>
function getScrollParent(node) {
    if(node === null) {
        return null
    }

    if(node.scrollHeight > node.clientHeight) {
        return node
    } else {
        return getScrollParent(node.parentNode)
    }
}

// Adapted from: https://lorisleiva.com/renderless-resizable-textarea
export default {
    name: 'resizable-textarea',
    methods: {
        resizeTextarea(event) {
            const target = event ? event.target : this.$el
            const scrollParent = getScrollParent(target.parentNode)
            const scrollTop = scrollParent ? scrollParent.scrollTop : null
            const scrollLeft = scrollParent ? scrollParent.scrollLeft : null

            target.style.height = 'auto'
            target.style.height = (target.scrollHeight) + 'px'

            if(scrollParent) {
                const bufferSpace = 100
                scrollParent.scrollTo(scrollLeft, scrollTop + bufferSpace)
            }
        },
        resizeTextareaNoEvent() {
            this.resizeTextarea()
        }
    },
    mounted() {
        this.$nextTick(() => {
            this.$el.setAttribute('style', 'height:' + (this.$el.scrollHeight) + 'px;overflow-y:hidden;')
        })

        this.$el.addEventListener('input', this.resizeTextarea)

        // Watch for value changes and resize accordingly
        const observer = new MutationObserver(() => {
            this.$nextTick(() => {
                this.resizeTextareaNoEvent()
            })
        })

        observer.observe(this.$el, {
            attributes: true,
            attributeFilter: ['value']
        })

        // Store observer for cleanup
        this._observer = observer
    },
    beforeUnmount() {
        this.$el.removeEventListener('input', this.resizeTextarea)
        if (this._observer) {
            this._observer.disconnect()
        }
    },
    updated() {
        // Resize when component updates (e.g., when v-model changes)
        this.$nextTick(() => {
            this.resizeTextareaNoEvent()
        })
    },
    render() {
        return this.$slots.default()[0]
    }
}
</script>
