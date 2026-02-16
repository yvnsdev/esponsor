<script setup>
import { computed } from 'vue';
import { 
    Link, Star, Heart, ShoppingBag, Play, Image as ImageIcon, Music, 
    Mail, Phone, MapPin, Calendar, FileText, Download,
    ExternalLink, Gift, Search, Home, User, Settings,
    Facebook, Instagram, Twitter, Linkedin, Youtube, Github
} from 'lucide-vue-next';

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

const isDark = computed(() => props.theme === 'dark');

// Inline styles per-link (prefer per-link props, then block-level, then site-level)
const getLinkInlineStyles = (link) => {
    const styles = {};

    const linkBg = link?.backgroundColor || link?.bgColor || props.block?.props?.bgColor || props.site?.link_bg_color || null;
    const linkText = link?.textColor || props.block?.props?.textColor || props.site?.link_text_color || null;

    if (linkBg) {
        styles.backgroundColor = linkBg;
        styles.borderColor = linkBg;
    }

    if (linkText) {
        styles.color = linkText;
    }

    return Object.keys(styles).length > 0 ? styles : null;
};

// Map icon names to lucide components
const iconMap = {
    link: Link,
    star: Star,
    heart: Heart,
    shop: ShoppingBag,
    play: Play,
    image: ImageIcon,
    music: Music,
    mail: Mail,
    phone: Phone,
    map: MapPin,
    calendar: Calendar,
    file: FileText,
    download: Download,
    external: ExternalLink,
    gift: Gift,
    search: Search,
    home: Home,
    user: User,
    settings: Settings,
    facebook: Facebook,
    instagram: Instagram,
    twitter: Twitter,
    linkedin: Linkedin,
    youtube: Youtube,
    github: Github,
};

// Get button style classes based on style prop
const getButtonClasses = (style) => {
    const baseClasses = 'link-button';
    const themeClass = isDark.value ? 'dark-theme' : 'light-theme';
    switch (style) {
        case 'primary':
            return `${baseClasses} link-primary ${themeClass}`;
        case 'secondary':
            return `${baseClasses} link-secondary ${themeClass}`;
        case 'ghost':
            return `${baseClasses} link-ghost ${themeClass}`;
        default:
            return `${baseClasses} link-primary ${themeClass}`;
    }
};

// Get icon component for a link
const getIconComponent = (iconName) => {
    return iconMap[iconName] || Link;
};

// Support legacy format (text, backgroundColor) and new format (label, style)
const getLinkLabel = (link) => {
    return link.label || link.text || 'Link';
};
</script>

<template>
    <div class="links-block">
        <!-- Section Title -->
        <h3 
            v-if="block.props.title" 
            class="links-title"
            :style="{ color: isDark ? '#f1f5f9' : '#0B0B0B' }"
        >
            {{ block.props.title }}
        </h3>
        
        <!-- Links List -->
        <div class="links-container">
            <a
                v-for="(link, index) in block.props.links || []"
                :key="index"
                :href="link.url || '#'"
                target="_blank"
                rel="noopener noreferrer"
                :class="getButtonClasses(link.style)"
                :style="getLinkInlineStyles(link)"
            >
                <component 
                    :is="getIconComponent(link.icon)" 
                    class="link-icon"
                />
                <span class="link-label">{{ getLinkLabel(link) }}</span>
            </a>
        </div>
    </div>
</template>

<style scoped>
.links-block {
    margin: 1.5rem 0;
}

.links-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 1.25rem;
    text-align: center;
}

.links-container {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}

.link-button {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9375rem;
    border-radius: 12px;
    border: 2px solid;
    transition: all 0.3s ease;
    cursor: pointer;
}

.link-icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.link-label {
    flex: 1;
    text-align: left;
}

/* Primary Style - eSponsor Brand */
.link-primary {
    background: #22B8A6;
    color: white;
    border-color: #22B8A6;
}

.link-primary:hover {
    background: #1a9585;
    border-color: #1a9585;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(34, 184, 166, 0.3);
}

/* Secondary Style - Outlined */
.link-secondary.light-theme {
    background: white;
    color: #0B0B0B;
    border-color: #d1d5db;
}

.link-secondary.light-theme:hover {
    background: #F5F7F6;
    border-color: #22B8A6;
    color: #22B8A6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.link-secondary.dark-theme {
    background: #334155;
    color: #f1f5f9;
    border-color: #475569;
}

.link-secondary.dark-theme:hover {
    background: #475569;
    border-color: #22B8A6;
    color: #22B8A6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Ghost Style - Minimal */
.link-ghost.light-theme {
    background: transparent;
    color: #0B0B0B;
    border-color: transparent;
}

.link-ghost.light-theme:hover {
    background: #F5F7F6;
    border-color: #e5e7eb;
    color: #22B8A6;
    transform: translateX(4px);
}

.link-ghost.dark-theme {
    background: transparent;
    color: #f1f5f9;
    border-color: transparent;
}

.link-ghost.dark-theme:hover {
    background: #334155;
    border-color: #475569;
    color: #22B8A6;
    transform: translateX(4px);
}
</style>