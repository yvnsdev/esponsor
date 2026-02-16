<script setup>
import { computed } from 'vue';
import TextBlock from '@/Blocks/TextBlock.vue';
import LinksBlock from '@/Blocks/LinksBlock.vue';
import ImageBlock from '@/Blocks/ImageBlock.vue';
import VideoBlock from '@/Blocks/VideoBlock.vue';
import SocialIconsBlock from '@/Blocks/SocialIconsBlock.vue';
import CtaBlock from '@/Blocks/CtaBlock.vue';

const props = defineProps({
    block: {
        type: Object,
        required: true
    },
    theme: {
        type: String,
        default: 'light'
    },
    site: {
        type: Object,
        default: () => ({})
    }
});

// Component mapping for each block type
const blockComponents = {
    'text': TextBlock,
    'links': LinksBlock,
    'image': ImageBlock,
    'video': VideoBlock,
    'social-icons': SocialIconsBlock,
    'cta': CtaBlock
};

// Get the appropriate component for the block type
const component = computed(() => {
    return blockComponents[props.block.type] || null;
});
</script>

<template>
    <component 
        v-if="component" 
        :is="component" 
        :block="block"
        :theme="theme"
        :site="site"
        class="block-item"
    />
    <div 
        v-else 
        class="block-error"
    >
        <p class="text-sm text-red-600">
            Unknown block type: <strong>{{ block.type }}</strong>
        </p>
    </div>
</template>

<style scoped>
.block-item {
    /* Blocks handle their own spacing */
}

.block-error {
    padding: 1rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    text-align: center;
}
</style>
