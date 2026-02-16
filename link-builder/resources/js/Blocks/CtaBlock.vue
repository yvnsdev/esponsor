<script setup>
import { computed } from 'vue';
import { Megaphone } from 'lucide-vue-next';

const props = defineProps({
    block: {
        type: Object,
        required: true
    },
    site: {
        type: Object,
        default: () => ({})
    }
});

// Icon color is block-level (fallback to style defaults)
const iconColor = computed(() => {
    const blockColor = props.block?.props?.iconColor || null;
    if (blockColor) return blockColor;
    // default based on style variant
    const style = props.block?.props?.style || 'light';
    return style === 'dark' ? '#ffffff' : '#22B8A6';
});

// CTA text color (block-level fallback to site-level)
const ctaTextColor = computed(() => {
    return props.block?.props?.ctaTextColor || props.site?.cta_text_color || null;
});

// Utility: darken a hex color by percent
const darkenHex = (hex, percent = 8) => {
    if (!hex) return null;
    const h = hex.replace('#', '');
    const full = h.length === 3 ? h.split('').map(c => c+c).join('') : h;
    const r = Math.max(0, Math.min(255, Math.round(parseInt(full.substring(0,2),16) * (100 - percent) / 100)));
    const g = Math.max(0, Math.min(255, Math.round(parseInt(full.substring(2,4),16) * (100 - percent) / 100)));
    const b = Math.max(0, Math.min(255, Math.round(parseInt(full.substring(4,6),16) * (100 - percent) / 100)));
    return `#${r.toString(16).padStart(2,'0')}${g.toString(16).padStart(2,'0')}${b.toString(16).padStart(2,'0')}`;
};

// Computed CSS variables for button (bg, border, hover)
const computedButtonStyle = computed(() => {
    const vars = {};
    const bg = props.block?.props?.buttonColor || props.site?.cta_button_color || null;
    const border = props.block?.props?.buttonBorderColor || bg || null;
    const hover = props.block?.props?.buttonBorderColor || (bg ? darkenHex(bg, 10) : null);

    if (bg) vars['--cta-button-bg'] = bg;
    if (border) vars['--cta-button-border'] = border;
    if (hover) vars['--cta-button-hover'] = hover;

    return Object.keys(vars).length ? vars : null;
});
</script>

<template>
    <div class="cta-block" :class="`cta-${block.props.style || 'light'}`">
        <div class="cta-icon" :style="{ color: iconColor }">
            <Megaphone class="w-6 h-6" />
        </div>
        
        <div class="cta-content">
            <h3 class="cta-title" :style="ctaTextColor ? { color: ctaTextColor } : {}">
                {{ block.props.title || 'Important Announcement' }}
            </h3>
            <p class="cta-text" :style="ctaTextColor ? { color: ctaTextColor } : {}">
                {{ block.props.text || 'Check out our latest updates!' }}
            </p>
        </div>
        
        <a 
            v-if="block.props.buttonUrl"
            :href="block.props.buttonUrl" 
            class="cta-button"
            :style="computedButtonStyle"
            target="_blank"
            rel="noopener noreferrer"
        >
            {{ block.props.buttonLabel || 'Learn More' }}
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>
</template>

<style scoped>
.cta-block {
    margin: 1.5rem 0;
    padding: 1.5rem;
    border-radius: 16px;
    border: 2px solid;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    transition: all 0.3s ease;
}

.cta-light {
    background: white;
    border-color: #e5e7eb;
}

.cta-light:hover {
    border-color: #22B8A6;
    box-shadow: 0 4px 12px rgba(34, 184, 166, 0.1);
}

.cta-light .cta-icon {
    color: #22B8A6;
    background: rgba(34, 184, 166, 0.1);
}

.cta-light .cta-title {
    color: #0B0B0B;
}

.cta-light .cta-text {
    color: #6b7280;
}

.cta-light .cta-button {
    background: var(--cta-button-bg, #22B8A6);
    color: var(--cta-button-text-color, white);
    border-color: var(--cta-button-border, #22B8A6);
}

.cta-light .cta-button:hover {
    background: var(--cta-button-hover, #1a9585);
}

.cta-dark {
    background: linear-gradient(135deg, #22B8A6 0%, #1a9585 100%);
    border-color: #22B8A6;
}

.cta-dark .cta-icon {
    color: white;
    background: rgba(255, 255, 255, 0.2);
}

.cta-dark .cta-title {
    color: white;
}

.cta-dark .cta-text {
    color: rgba(255, 255, 255, 0.9);
}

.cta-dark .cta-button {
    background: var(--cta-button-bg, white);
    color: var(--cta-button-text-color, #22B8A6);
    border-color: var(--cta-button-border, #22B8A6);
}

.cta-dark .cta-button:hover {
    background: var(--cta-button-hover, #f5f7f6);
}

.cta-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cta-content {
    flex: 1;
}

.cta-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.cta-text {
    font-size: 0.9375rem;
    line-height: 1.6;
    margin: 0;
}

.cta-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9375rem;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}
</style>
