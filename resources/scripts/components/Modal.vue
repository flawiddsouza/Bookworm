<template>
    <div class="modal-container" v-if="showModal" @click="hideModal">
        <div class="modal-inner-container">
            <button class="modal__close" @click="$emit('update:showModal', false)">X</button>
            <div class="modal__title"><slot name="title"></slot></div>
            <div class="modal__content">
                <slot></slot>
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
        }
    },
    methods: {
        hideModal(e) {
            // document.body.contains(e.target) is needed when the clicked element is no longer in the DOM
            // if you don't add it, the orphaned e.target element will close the modal, as its "closest" will
            // not yield the .modal-inner-container class element or any other elements for that matter
            // because it has been removed by the user
            if(!e.target.closest('.modal-inner-container') && document.body.contains(e.target)) {
                this.$emit('update:showModal', false)
            }
        }
    }
}
</script>

<style scoped>
.modal-container {
    position: fixed;
    top: 0;
    left: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    width: 100vw;
    background: #00000026;
}

.modal-inner-container {
    position: relative;
    background: white;
    padding: 1.5rem;
    border-radius: 2px;
    box-shadow: 0 19px 38px rgb(0 0 0 / 30%), 0 15px 12px rgb(0 0 0 / 22%);
    min-width: 23rem;
    max-width: 60%;
    max-height: 90%;
    overflow-y: auto;
}

.modal__close {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
}

.modal__title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-right: 2rem;
    margin-bottom: 1rem;
}
</style>
