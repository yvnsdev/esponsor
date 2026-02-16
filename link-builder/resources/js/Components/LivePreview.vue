<script setup>
import { computed } from 'vue';
import BlockRenderer from '@/Blocks/BlockRenderer.vue';

const props = defineProps({
    site: {
        type: Object,
        required: true
    },
    blocks: {
        type: Array,
        default: () => []
    },
    mode: {
        type: String,
        default: 'mobile' // 'mobile' or 'desktop'
    }
});

// Filter enabled blocks
const enabledBlocks = computed(() => {
    return props.blocks.filter(block => block.enabled);
});

// Check if dark mode
const isDarkMode = computed(() => {
    return props.site.theme_mode === 'dark';
});

// Preview container classes
const previewClasses = computed(() => {
    const baseClass = props.mode === 'desktop' ? 'preview-desktop' : 'preview-phone';
    const themeClass = isDarkMode.value ? 'dark-theme' : 'light-theme';
    return `${baseClass} ${themeClass}`;
});

// Banner styles
const bannerStyles = computed(() => {
    const styles = {};
    
    if (props.site.banner_image_url) {
        styles.backgroundImage = `url(${props.site.banner_image_url})`;
        styles.backgroundSize = 'cover';
        styles.backgroundPosition = 'center';
    } else if (props.site.banner_color) {
        styles.background = props.site.banner_color;
    }
    
    return styles;
});
</script>

<template>
    <div class="live-preview">
        <!-- Mobile/Desktop Layout Container -->
        <div :class="previewClasses">
            <!-- Public-style Nav (preview) -->
            <div class="preview-topbar" v-if="site.nav_logo_url">
                <div class="preview-topbar-inner">
                    <a :href="`/@${site.slug}`" target="_blank" class="flex items-center gap-3">
                        <img :src="site.nav_logo_url" :alt="site.name" class="preview-nav-logo" />
                        <span class="preview-site-name">{{ site.name }}</span>
                    </a>
                </div>
            </div>

            <!-- Profile Card Header -->
            <div class="profile-header">
                <!-- Header Cover -->
                <div class="header-cover" :style="bannerStyles"></div>
                
                <!-- Profile Content -->
                <div class="profile-content">
                    <!-- Avatar -->
                    <div class="avatar-ring">
                        <img
                            v-if="site.avatar_url"
                            :src="site.avatar_url"
                            :alt="site.name"
                            class="site-avatar"
                        />
                        <div 
                            v-else
                            class="site-avatar-placeholder"
                        >
                            {{ (site.name || 'U').charAt(0).toUpperCase() }}
                        </div>
                    </div>

                    <!-- Site Info -->
                    <h1 class="site-name">{{ site.name || 'Your Site Name' }}</h1>
                    <p 
                        v-if="site.bio" 
                        class="site-bio"
                    >
                        {{ site.bio }}
                    </p>
                </div>
            </div>

            <!-- Blocks Content -->
            <div class="blocks-content">
                <!-- Empty State -->
                <div 
                    v-if="enabledBlocks.length === 0" 
                    class="empty-state"
                >
                    <div class="empty-icon">
                        <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="empty-title">No content yet</h3>
                    <p class="empty-text">
                        Add and enable blocks to see your page preview
                    </p>
                </div>

                <!-- Render Enabled Blocks -->
                <BlockRenderer
                    v-else
                    v-for="block in enabledBlocks"
                    :key="block.id"
                    :block="block"
                    :theme="site.theme_mode || 'light'"
                    :site="site"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.live-preview {
    background: #F5F7F6;
    min-height: 100%;
    padding: 1.5rem;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.preview-phone,
.preview-desktop {
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(11, 11, 11, 0.1);
    overflow: hidden;
    min-height: 600px;
    transition: all 0.3s ease;
}

.preview-phone {
    width: 100%;
    max-width: 375px;
}

.preview-desktop {
    width: 100%;
    max-width: 640px;
}

/* Light Theme */
.light-theme {
    background: white;
}

.light-theme .site-name {
    color: #0B0B0B;
}

.light-theme .site-bio {
    color: #6b7280;
}

.light-theme .empty-title {
    color: #64748b;
}

.light-theme .empty-text {
    color: #94a3b8;
}

.light-theme .avatar-ring {
    background: white;
}

/* Dark Theme */
.dark-theme {
    background: #1e293b;
}

.dark-theme .site-name {
    color: #f1f5f9;
}

.dark-theme .site-bio {
    color: #94a3b8;
}

.dark-theme .empty-title {
    color: #cbd5e1;
}

.dark-theme .empty-text {
    color: #64748b;
}

.dark-theme .empty-icon svg {
    color: #475569;
}

.dark-theme .avatar-ring {
    background: #334155;
}

/* Preview topbar (public nav) */
.preview-topbar {
    background: transparent;
    padding: 0.5rem 1rem;
    border-bottom: 1px solid rgba(0,0,0,0.04);
}
.preview-topbar-inner { display:flex; align-items:center; gap:0.75rem; }
.preview-nav-logo { width:32px; height:32px; object-fit:contain; border-radius:6px; }
.preview-site-name { font-weight:700; color:inherit; }

/* Profile Card Header */
.profile-header {
    position: relative;
    padding-bottom: 0.25rem;
}

.header-cover {
    height: 80px;
    background: linear-gradient(135deg, #22B8A6 0%, #1a9585 100%);
    position: relative;
    overflow: hidden;
}

.header-cover::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.08) 0%, transparent 50%);
}

.profile-content {
    position: relative;
    text-align: center;
    padding: 0 1.25rem;
}

.avatar-ring {
    padding: 4px;
    border-radius: 9999px;
    box-shadow: 0 4px 12px rgba(11, 11, 11, 0.1);
    display: inline-block;
    margin-top: -2.5rem;
    margin-bottom: 0.25rem;
    transition: background 0.3s ease;
}

.site-avatar {
    width: 80px;
    height: 80px;
    border-radius: 9999px;
    object-fit: cover;
    border: 2px solid white;
}

.site-avatar-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 9999px;
    background: linear-gradient(135deg, #22B8A6 0%, #1a9585 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    font-weight: 700;
    border: 2px solid white;
    box-shadow: 0 4px 12px rgba(34, 184, 166, 0.2);
}

.site-name {
    font-size: 1.375rem;
    font-weight: 800;
    margin-bottom: 0.125rem;
    line-height: 1.2;
    transition: color 0.3s ease;
}

.site-bio {
    font-size: 0.875rem;
    line-height: 1.5;
    margin: 0;
    transition: color 0.3s ease;
}

.blocks-content {
    padding: 0.5rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
}

.empty-icon {
    margin-bottom: 1rem;
    display: flex;
    justify-content: center;
}

.empty-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    transition: color 0.3s ease;
}

.empty-text {
    font-size: 0.875rem;
    line-height: 1.5;
    transition: color 0.3s ease;
}

.preview-block {
    margin-bottom: 0.5rem;
}
</style>