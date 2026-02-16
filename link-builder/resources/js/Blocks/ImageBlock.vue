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

// Computed styles for the image
const imageStyles = computed(() => ({
    width: `${props.block.props.width || 100}%`,
    borderRadius: `${props.block.props.borderRadius || 0}px`,
    maxWidth: '100%',
    height: 'auto',
    display: 'block'
}));

// Computed alignment class
const alignmentClass = computed(() => {
    const alignment = props.block.props.alignment || 'center';
    return {
        left: 'text-left',
        center: 'text-center',
        right: 'text-right'
    }[alignment] || 'text-center';
});
</script>

<template>
    <div 
        class="image-block"
        :class="alignmentClass"
    >
        <!-- With Link -->
        <a 
            v-if="block.props.linkUrl"
            :href="block.props.linkUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="image-link"
        >
            <img
                :src="block.props.imageUrl || 'https://via.placeholder.com/400x300?text=Your+Image'"
                :alt="block.props.altText || 'Image description'"
                :style="imageStyles"
                class="block-image"
                loading="lazy"
            />
        </a>
        
        <!-- Without Link -->
        <img
            v-else
            :src="block.props.imageUrl || 'https://via.placeholder.com/400x300?text=Your+Image'"
            :alt="block.props.altText || 'Image description'"
            :style="imageStyles"
            class="block-image"
            loading="lazy"
        />

        <!-- Caption -->
        <p v-if="block.props.caption" 
           class="image-caption"
           :style="{ color: isDark ? '#94a3b8' : '#64748b' }">
            {{ block.props.caption }}
        </p>
    </div>
</template>

<style scoped>
.image-block {
    margin: 1rem 0;
}

.image-link {
    display: inline-block;
    transition: transform 0.2s ease;
}

.image-link:hover {
    transform: scale(1.02);
}

.block-image {
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.block-image:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.image-caption {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    font-style: italic;
}
</style>