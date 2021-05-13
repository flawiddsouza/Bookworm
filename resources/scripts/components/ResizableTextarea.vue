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
            const scrollParent = getScrollParent(event.target.parentNode)
            const scrollTop = scrollParent ? scrollParent.scrollTop : null
            const scrollLeft = scrollParent ? scrollParent.scrollLeft : null

            event.target.style.height = 'auto'
            event.target.style.height = (event.target.scrollHeight) + 'px'

            if(scrollParent) {
                scrollParent.scrollTo(scrollLeft, scrollTop)
            }
        },
    },
    mounted() {
        this.$nextTick(() => {
            this.$el.setAttribute('style', 'height:' + (this.$el.scrollHeight) + 'px;overflow-y:hidden;')
        })

        this.$el.addEventListener('input', this.resizeTextarea)
    },
    beforeUnmount() {
        this.$el.removeEventListener('input', this.resizeTextarea)
    },
    render() {
        return this.$slots.default()[0]
    }
}
</script>
