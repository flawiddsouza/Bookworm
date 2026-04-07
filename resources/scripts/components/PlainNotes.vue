<template>
    <div
        class="plain-notes"
        contenteditable="plaintext-only"
        spellcheck="false"
        @input="$emit('update:modelValue', $event.target.innerText)"
        style="outline: none; white-space: pre-wrap; word-break: break-word;"
    ></div>
</template>

<script>
export default {
    props: {
        modelValue: {
            type: String,
            default: ''
        }
    },
    emits: ['update:modelValue'],
    mounted() {
        this.$el.innerText = this.modelValue || ''
    },
    watch: {
        modelValue(val) {
            if (this.$el.innerText !== val) {
                this.$el.innerText = val || ''
            }
        }
    }
}
</script>

<style scoped>
.plain-notes {
    width: 100%;
    padding: 1rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 1rem;
    background: #ffffff;
    min-height: 250px;
    font-family: inherit;
    line-height: 1.6;
}
</style>
