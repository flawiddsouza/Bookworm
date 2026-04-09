<template>
    <div class="modal-overlay" v-if="showModal" :style="{ zIndex }" @click.self="$emit('update:showModal', false)">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title"><slot name="title"></slot></div>
                <button type="button" class="modal-close" @click="$emit('update:showModal', false)">✕</button>
            </div>
            <div class="modal-body">
                <slot></slot>
            </div>
            <div class="modal-footer" v-if="$slots.footer">
                <slot name="footer"></slot>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        showModal: {
            type: Boolean,
            default: false
        },
        zIndex: {
            type: Number,
            default: 3
        }
    },
    emits: ['update:showModal']
}
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
}

.modal {
    background: white;
    border-radius: 6px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18);
    min-width: 23rem;
    max-width: min(60%, 95vw);
    max-height: 90%;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-header {
    padding: 1em 1.2em;
    border-bottom: 1px solid lightgrey;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}

.modal-title {
    font-size: 1em;
    font-weight: 700;
    margin-right: 1em;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.2em;
    cursor: pointer;
    color: #666;
    padding: 0;
    line-height: 1;
}

.modal-body {
    padding: 1.2em;
    overflow-y: auto;
    flex: 1;
}

.modal-footer {
    padding: 1em 1.2em;
    border-top: 1px solid lightgrey;
    display: flex;
    justify-content: flex-end;
    gap: 0.5em;
    flex-shrink: 0;
}
</style>
