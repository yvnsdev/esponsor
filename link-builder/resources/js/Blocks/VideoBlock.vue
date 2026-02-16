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

// Extract YouTube video ID from URL
const videoId = computed(() => {
    const url = props.block.props.videoUrl || '';
    
    // Match various YouTube URL formats
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
        /youtube\.com\/watch\?.*v=([^&\n?#]+)/
    ];
    
    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match && match[1]) {
            return match[1];
        }
    }
    
    return null;
});

// YouTube embed URL with parameters
const embedUrl = computed(() => {
    if (!videoId.value) return null;
    
    const baseUrl = `https://www.youtube.com/embed/${videoId.value}`;
    const params = new URLSearchParams();
    
    // Add parameters based on props
    if (props.block.props.autoplay) {
        params.append('autoplay', '1');
    }
    
    if (!props.block.props.showControls) {
        params.append('controls', '0');
    }
    
    params.append('rel', '0'); // Don't show related videos
    params.append('modestbranding', '1'); // Minimal YouTube branding
    
    return `${baseUrl}?${params.toString()}`;
});

// Container styles based on aspect ratio
const containerStyles = computed(() => {
    const ratio = props.block.props.aspectRatio || '16:9';
    const width = `${props.block.props.width || 100}%`;
    
    // Calculate padding-bottom for aspect ratio
    const paddingBottom = {
        '16:9': '56.25%',
        '4:3': '75%',
        '1:1': '100%'
    }[ratio] || '56.25%';
    
    return {
        width,
        paddingBottom,
        position: 'relative',
        height: '0',
        maxWidth: '100%'
    };
});

const iframeStyles = {
    position: 'absolute',
    top: '0',
    left: '0',
    width: '100%',
    height: '100%',
    border: 'none',
    borderRadius: '8px'
};
</script>

<template>
    <div class="video-block">
        <!-- Video Title -->
        <h3 
            v-if="block.props.title" 
            class="video-title"
            :style="{ color: isDark ? '#f1f5f9' : '#1f2937' }"
        >
            {{ block.props.title }}
        </h3>
        
        <!-- YouTube Video Embed -->
        <div 
            v-if="embedUrl"
            class="video-container"
            :style="containerStyles"
        >
            <iframe
                :src="embedUrl"
                :title="block.props.title || 'YouTube Video'"
                :style="iframeStyles"
                frameborder="0"
                allowfullscreen
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                loading="lazy"
            ></iframe>
        </div>
        
        <!-- Fallback when no valid video URL -->
        <div 
            v-else 
            class="video-placeholder"
            :style="containerStyles"
        >
            <div class="placeholder-content">
                <div class="placeholder-icon">📹</div>
                <p class="placeholder-text">
                    {{ block.props.videoUrl ? 'Invalid YouTube URL' : 'No video URL provided' }}
                </p>
                <p class="placeholder-hint">
                    Please enter a valid YouTube URL
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.video-block {
    margin: 1rem 0;
}

.video-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
    text-align: center;
}

.video-container {
    margin: 0 auto;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.video-placeholder {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border: 2px dashed #9ca3af;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.placeholder-content {
    text-align: center;
    padding: 2rem;
}

.placeholder-icon {
    font-size: 3rem;
    margin-bottom: 0.5rem;
}

.placeholder-text {
    font-size: 1rem;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.placeholder-hint {
    font-size: 0.875rem;
    color: #9ca3af;
}
</style>