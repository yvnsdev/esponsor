<script setup>
import { computed } from 'vue';

const props = defineProps({
    block: {
        type: Object,
        required: true
    },
    theme: {
        type: String,
        default: 'light'
    }
});

const isDark = computed(() => props.theme === 'dark');
</script>

<template>
    <div class="text-block-wrapper">
        <div 
            class="text-block"
            :style="{
                textAlign: block.props.textAlign || 'left'
            }"
        >
            <h3 v-if="block.props.title" 
                class="text-block-title"
                :style="{ color: isDark ? '#f1f5f9' : '#0B0B0B' }">
                {{ block.props.title }}
            </h3>
            <p class="text-block-body"
               :style="{ color: isDark ? '#cbd5e1' : '#4b5563' }">
                {{ block.props.body || block.props.content || 'Your text content here' }}
            </p>
        </div>
        
        <!-- Divider -->
        <hr 
            v-if="block.props.showDivider" 
            class="text-block-divider"
            :style="{ borderColor: isDark ? '#334155' : '#e5e7eb' }"
        />
    </div>
</template>

<style scoped>
.text-block-wrapper {
    margin: 1.5rem 0;
}

.text-block {
    line-height: 1.7;
    word-wrap: break-word;
}

.text-block-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.text-block-body {
    font-size: 0.9375rem;
    white-space: pre-wrap;
    margin: 0;
    line-height: 1.7;
}

.text-block-divider {
    margin-top: 1.5rem;
    border: none;
    border-top: 2px solid;
    opacity: 0.6;
}
</style>