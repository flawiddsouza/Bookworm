<template>
    <div v-if="type === 'tabs'" class="tabs">
        <div
            v-for="tab in tabs"
            :key="getTabKey(tab)"
            @click="handleTabClick(tab)"
            :class="{ 'tab-active': isActive(tab) }"
        >
            {{ getTabLabel(tab) }}
        </div>
    </div>
    <select
        v-else-if="type === 'select'"
        :value="modelValue"
        @change="handleSelectChange"
        class="tab-select"
    >
        <option
            v-for="tab in tabs"
            :key="getTabKey(tab)"
            :value="getTabValue(tab)"
        >
            {{ getTabLabel(tab) }}
        </option>
    </select>
</template>

<script>
export default {
    name: 'Tabs',
    props: {
        tabs: {
            type: Array,
            required: true
        },
        modelValue: {
            required: true
        },
        type: {
            type: String,
            default: 'select',
            validator: (value) => ['tabs', 'select'].includes(value)
        },
        // For simple arrays of strings
        valueKey: {
            type: String,
            default: null
        },
        labelKey: {
            type: String,
            default: null
        }
    },
    emits: ['update:modelValue'],
    methods: {
        getTabKey(tab) {
            if (typeof tab === 'string') {
                return tab
            }
            return this.valueKey ? tab[this.valueKey] : tab.value || tab.filter || tab.id
        },
        getTabValue(tab) {
            if (typeof tab === 'string') {
                return tab
            }
            return this.valueKey ? tab[this.valueKey] : tab.value || tab.filter || tab.id
        },
        getTabLabel(tab) {
            if (typeof tab === 'string') {
                return tab
            }
            return this.labelKey ? tab[this.labelKey] : tab.label || tab.name || tab.title
        },
        isActive(tab) {
            return this.modelValue === this.getTabValue(tab)
        },
        handleTabClick(tab) {
            const value = this.getTabValue(tab)
            this.$emit('update:modelValue', value)
        },
        handleSelectChange(event) {
            this.$emit('update:modelValue', event.target.value)
        }
    }
}
</script>
