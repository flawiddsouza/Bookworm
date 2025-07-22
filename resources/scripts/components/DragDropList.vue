<template>
    <div v-if="items.length">
        <ul class="drag-drop-list">
            <li v-for="(item, idx) in items" :key="item.id || idx"
                :ref="el => setItemRef(el, idx)"
                class="drag-drop-item">
                <slot name="item" :item="item" :index="idx">
                    {{ item.name || item }}
                </slot>
            </li>
        </ul>
    </div>
    <div v-else>
        <slot name="empty">Loading...</slot>
    </div>
</template>

<script>
import { draggable } from '@atlaskit/pragmatic-drag-and-drop/element/adapter'
import { dropTargetForElements } from '@atlaskit/pragmatic-drag-and-drop/element/adapter'
import { reorder } from '@atlaskit/pragmatic-drag-and-drop/reorder'
import { combine } from '@atlaskit/pragmatic-drag-and-drop/combine'

export default {
    name: 'DragDropList',
    props: {
        items: {
            type: Array,
            required: true,
            default: () => []
        },
        itemType: {
            type: String,
            default: 'item'
        }
    },
    emits: ['update:items', 'reorder'],
    data() {
        return {
            itemRefs: [],
            cleanupFunctions: []
        }
    },
    mounted() {
        this.setupDragAndDrop()
    },
    watch: {
        items: {
            handler() {
                this.$nextTick(() => {
                    this.setupDragAndDrop()
                })
            },
            deep: true,
            immediate: false
        }
    },
    beforeUnmount() {
        this.cleanup()
    },
    methods: {
        setItemRef(el, index) {
            if (el) {
                this.itemRefs[index] = el
            } else {
                delete this.itemRefs[index]
            }
        },
        cleanup() {
            this.cleanupFunctions.forEach(cleanup => cleanup())
            this.cleanupFunctions = []
            this.itemRefs = []
        },
        setupDragAndDrop() {
            this.cleanup()

            this.$nextTick(() => {
                const validRefs = this.itemRefs.filter(Boolean)

                if (validRefs.length === 0) {
                    console.warn('DragDropList: No valid element refs found')
                    return
                }

                validRefs.forEach((element, actualIndex) => {
                    const originalIndex = this.itemRefs.indexOf(element)

                    if (!element || originalIndex === -1) return

                    try {
                        const cleanup = combine(
                            draggable({
                                element,
                                getInitialData: () => ({ index: originalIndex, type: this.itemType }),
                                onDragStart: () => {
                                    element.classList.add('dragging')
                                    element.style.opacity = '0.5'
                                },
                                onDrop: () => {
                                    element.classList.remove('dragging')
                                    element.style.opacity = '1'
                                    element.style.transform = 'none'
                                }
                            }),
                            dropTargetForElements({
                                element,
                                canDrop: ({ source }) => source.data.type === this.itemType,
                                getData: () => ({ index: originalIndex, type: this.itemType }),
                                onDragEnter: ({ source }) => {
                                    const sourceIndex = source.data.index
                                    const targetIndex = originalIndex

                                    if (sourceIndex < targetIndex) {
                                        element.classList.add('drop-line-below')
                                    } else if (sourceIndex > targetIndex) {
                                        element.classList.add('drop-line-above')
                                    }
                                },
                                onDragLeave: () => {
                                    element.classList.remove('drop-line-above', 'drop-line-below')
                                },
                                onDrop: ({ source }) => {
                                    element.classList.remove('drop-line-above', 'drop-line-below')

                                    const sourceIndex = source.data.index
                                    const destinationIndex = originalIndex

                                    if (sourceIndex !== destinationIndex) {
                                        const reorderedItems = reorder({
                                            list: this.items,
                                            startIndex: sourceIndex,
                                            finishIndex: destinationIndex
                                        })

                                        this.$emit('update:items', reorderedItems)
                                        this.$emit('reorder', {
                                            items: reorderedItems,
                                            from: sourceIndex,
                                            to: destinationIndex
                                        })
                                    }
                                }
                            })
                        )

                        this.cleanupFunctions.push(cleanup)
                    } catch (error) {
                        console.error('DragDropList setup error:', error)
                    }
                })
            })
        }
    }
}
</script>

<style scoped>
.drag-drop-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.drag-drop-item {
    display: flex;
    align-items: center;
    padding: 0.5em;
    border: 1px solid #ccc;
    margin-bottom: 0.5em;
    background: #fafafa;
    cursor: grab;
    transition: all 0.2s ease;
    border-radius: 4px;
    position: relative;
}

.drag-drop-item:hover {
    background: #f0f0f0;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.drag-drop-item:active {
    cursor: grabbing;
}

.drag-drop-item.dragging {
    cursor: grabbing;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    z-index: 1000;
    background: #fff;
    border-color: #f7c00c;
}

.drag-drop-item.drop-line-above::before {
    content: '';
    position: absolute;
    top: -2px;
    left: 0;
    right: 0;
    height: 3px;
    background: #f7c00c;
    border-radius: 2px;
    box-shadow: 0 0 4px rgba(247, 192, 12, 0.5);
    z-index: 10;
}

.drag-drop-item.drop-line-below::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 3px;
    background: #f7c00c;
    border-radius: 2px;
    box-shadow: 0 0 4px rgba(247, 192, 12, 0.5);
    z-index: 10;
}

.drag-handle {
    display: inline-block;
    margin-right: 0.5em;
    cursor: grab;
    user-select: none;
    color: #666;
    font-size: 1.2em;
    line-height: 1;
}

.drag-handle:active,
.dragging .drag-handle {
    cursor: grabbing;
}
</style>
