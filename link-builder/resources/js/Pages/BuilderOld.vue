<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SchemaForm from '@/Components/SchemaForm.vue';
import LivePreview from '@/Components/LivePreview.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useBlockManager } from '@/Composables/useBlockManager.js';

// Define props
const props = defineProps({
    site: {
        type: Object,
        default: () => ({})
    },
    pageDraft: {
        type: Object,
        default: () => ({})
    },
    blockCatalog: {
        type: Array,
        default: () => []
    },
    blockSchemas: {
        type: Object,
        default: () => ({})
    }
});

// Block management
const {
    blocks,
    isLoading,
    saveError,
    blocksCount,
    enabledBlocksCount,
    availableBlockTypes,
    selectedBlockId,
    selectedBlock,
    selectedBlockSchema,
    canPublish,
    isPublishing,
    publishError,
    publishSuccess,
    addBlock,
    removeBlock,
    duplicateBlock,
    moveBlockUp,
    moveBlockDown,
    toggleBlockEnabled,
    selectBlock,
    clearSelection,
    updateBlockProps,
    getBlockLabel,
    publishPage,
    clearPublishMessages
} = useBlockManager(props.pageDraft.blocks || [], props.blockSchemas);

// UI state
const showAddBlockModal = ref(false);
const selectedBlockType = ref(null);
const mobileViewMode = ref('editor'); // 'editor' or 'preview'

// Add block and close modal
const handleAddBlock = (type) => {
    addBlock(type);
    showAddBlockModal.value = false;
    selectedBlockType.value = null;
};

// Toggle mobile view mode
const toggleMobileView = () => {
    mobileViewMode.value = mobileViewMode.value === 'editor' ? 'preview' : 'editor';
};

// Handle page publishing
const handlePublishPage = async () => {
    const success = await publishPage();
    if (success) {
        // Auto-clear success message after 5 seconds
        setTimeout(() => {
            clearPublishMessages();
        }, 5000);
    }
};

// Get public page URL
const getPublicPageUrl = () => {
    return `/@${props.site.slug}`;
};

// Block icons mapping
const getBlockIcon = (type) => {
    const icons = {
        text: '📝',
        links: '🔗',
        image: '🖼️',
        video: '📹'
    };
    return icons[type] || '⬜';
};

// Block preview text
const getBlockPreview = (block) => {
    switch (block.type) {
        case 'text':
            return block.props.content?.substring(0, 50) + (block.props.content?.length > 50 ? '...' : '');
        case 'links':
            const linkCount = block.props.links?.length || 0;
            return `${linkCount} link${linkCount !== 1 ? 's' : ''}`;
        case 'image':
            return block.props.altText || 'Image';
        case 'video':
            return block.props.title || 'Video';
        default:
            return 'Custom block';
    }
};
</script>

<template>
    <Head title="Page Builder" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Page Builder
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ site.name }} • {{ blocksCount }} blocks ({{ enabledBlocksCount }} enabled)
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Mobile Toggle -->
                    <button
                        @click="toggleMobileView"
                        class="lg:hidden bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        {{ mobileViewMode === 'editor' ? '📱 Preview' : '✏️ Editor' }}
                    </button>
                    
                    <!-- Public Page Link -->
                    <a
                        :href="`/@${site.slug}`"
                        target="_blank"
                        class="hidden sm:inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm font-medium transition-colors"
                        title="View public page"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        View Public
                    </a>
                    
                    <!-- Publish Button -->
                    <button
                        @click="handlePublishPage"
                        :disabled="!canPublish || isPublishing"
                        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center"
                        :title="canPublish ? 'Publish your page' : 'Enable at least one block to publish'"
                    >
                        <svg v-if="isPublishing" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        {{ isPublishing ? 'Publishing...' : 'Publish' }}
                    </button>
                    
                    <div v-if="isLoading" class="flex items-center text-blue-600">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm">Saving...</span>
                    </div>
                    <button
                        @click="showAddBlockModal = true"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        + Add Block
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6 lg:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Success message -->
                <div v-if="publishSuccess" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ publishSuccess }}
                    </div>
                    <button @click="clearPublishMessages" class="text-green-500 hover:text-green-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Publish error message -->
                <div v-if="publishError" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        {{ publishError }}
                    </div>
                    <button @click="clearPublishMessages" class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Save error message -->
                <div v-if="saveError" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    {{ saveError }}
                </div>

                <!-- Desktop Layout: 2 Columns -->
                <div class="hidden lg:grid lg:grid-cols-12 lg:gap-8">
                    <!-- Left Panel: Blocks List + Editor -->
                    <div class="lg:col-span-5 xl:col-span-4 space-y-6">
                        <!-- Site Info Card -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Site Information</h3>
                                <div class="space-y-2 text-sm">
                                    <div><span class="font-medium">Name:</span> {{ site.name }}</div>
                                    <div><span class="font-medium">Slug:</span> <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ site.slug }}</code></div>
                                    <div><span class="font-medium">Status:</span> <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Blocks List -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Page Blocks</h3>
                                    <span class="text-sm text-gray-500">{{ blocksCount }} total</span>
                                </div>
                                
                                <!-- Empty state -->
                                <div v-if="blocks.length === 0" class="text-center py-8 text-gray-500">
                                    <div class="text-4xl mb-2">📝</div>
                                    <p class="text-sm">No blocks yet</p>
                                    <p class="text-xs mt-1">Click "Add Block" to get started</p>
                                </div>

                                <!-- Blocks list -->
                                <div v-else class="space-y-3 max-h-96 overflow-y-auto">
                                    <div
                                        v-for="(block, index) in blocks"
                                        :key="block.id"
                                        @click="selectBlock(block.id)"
                                        class="border border-gray-200 rounded-lg p-3 bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer"
                                        :class="{ 
                                            'opacity-50': !block.enabled,
                                            'ring-2 ring-blue-500 bg-blue-50': selectedBlockId === block.id
                                        }"
                                    >
                                        <!-- Block header -->
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex items-center space-x-2 min-w-0 flex-1">
                                                <span class="text-lg">{{ getBlockIcon(block.type) }}</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="text-sm font-medium text-gray-900">{{ getBlockLabel(block) }}</span>
                                                        <span
                                                            :class="block.enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium"
                                                        >
                                                            {{ block.enabled ? 'On' : 'Off' }}
                                                        </span>
                                                    </div>
                                                    <p class="text-xs text-gray-600 truncate">{{ getBlockPreview(block) }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Block actions -->
                                        <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                            <!-- Movement controls -->
                                            <div class="flex items-center space-x-1">
                                                <button
                                                    @click.stop="moveBlockUp(block.id)"
                                                    :disabled="index === 0"
                                                    class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Move up"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                </button>
                                                <button
                                                    @click.stop="moveBlockDown(block.id)"
                                                    :disabled="index === blocks.length - 1"
                                                    class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    title="Move down"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Action buttons -->
                                            <div class="flex items-center space-x-1">
                                                <button
                                                    @click.stop="toggleBlockEnabled(block.id)"
                                                    class="p-1 text-gray-400 hover:text-gray-600"
                                                    :title="block.enabled ? 'Disable block' : 'Enable block'"
                                                >
                                                    <svg v-if="block.enabled" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L12 12m-3.122-3.122L12 12m0 0l3.122 3.122M12 12l3.122-3.122M15 12a3 3 0 00-3-3"></path>
                                                    </svg>
                                                </button>
                                                <button
                                                    @click.stop="duplicateBlock(block.id)"
                                                    class="p-1 text-gray-400 hover:text-gray-600"
                                                    title="Duplicate block"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                </button>
                                                <button
                                                    @click.stop="removeBlock(block.id)"
                                                    class="p-1 text-red-400 hover:text-red-600"
                                                    title="Delete block"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Block Editor -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <!-- Block Properties Editor -->
                                <div v-if="selectedBlock && selectedBlockSchema" class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-medium text-gray-900">
                                            Edit {{ getBlockLabel(selectedBlock) }}
                                        </h3>
                                        <button
                                            @click="clearSelection"
                                            class="text-gray-400 hover:text-gray-600 p-1 hover:bg-gray-100 rounded"
                                        >
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <div class="border-t border-gray-200 pt-4">
                                        <SchemaForm
                                            :fields="selectedBlockSchema.fields"
                                            :modelValue="selectedBlock.props"
                                            @update:modelValue="updateBlockProps(selectedBlock.id, $event)"
                                        />
                                    </div>
                                </div>

                                <!-- Default state (no block selected) -->
                                <div v-else class="text-center py-8 text-gray-500">
                                    <div class="text-4xl mb-4">✏️</div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Block Editor</h3>
                                    <p class="text-sm">Click on a block to edit its properties</p>
                                    <p class="text-xs mt-2 text-gray-400">Select any block from the list above</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel: Live Preview -->
                    <div class="lg:col-span-7 xl:col-span-8">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-medium text-gray-900">Live Preview</h3>
                                    <div class="text-sm text-gray-500">
                                        {{ enabledBlocksCount }} enabled blocks
                                    </div>
                                </div>
                                <LivePreview :site="site" :blocks="blocks" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Layout: Toggled Views -->
                <div class="lg:hidden space-y-6">
                    <!-- Editor View -->
                    <div v-if="mobileViewMode === 'editor'" class="space-y-6">
                        <!-- Site Info Card -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Site Information</h3>
                                <div class="space-y-2 text-sm">
                                    <div><span class="font-medium">Name:</span> {{ site.name }}</div>
                                    <div><span class="font-medium">Status:</span> <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Blocks List -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Page Blocks</h3>
                                    <span class="text-sm text-gray-500">{{ blocksCount }} total</span>
                                </div>
                                
                                <!-- Empty state -->
                                <div v-if="blocks.length === 0" class="text-center py-8 text-gray-500">
                                    <div class="text-4xl mb-2">📝</div>
                                    <p class="text-sm">No blocks yet</p>
                                    <p class="text-xs mt-1">Click "Add Block" to get started</p>
                                </div>

                                <!-- Blocks list -->
                                <div v-else class="space-y-3">
                                    <div
                                        v-for="(block, index) in blocks"
                                        :key="block.id"
                                        @click="selectBlock(block.id)"
                                        class="border border-gray-200 rounded-lg p-3 bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer"
                                        :class="{ 
                                            'opacity-50': !block.enabled,
                                            'ring-2 ring-blue-500 bg-blue-50': selectedBlockId === block.id
                                        }"
                                    >
                                        <!-- Block content (same as desktop) -->
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex items-center space-x-2 min-w-0 flex-1">
                                                <span class="text-lg">{{ getBlockIcon(block.type) }}</span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="text-sm font-medium text-gray-900">{{ getBlockLabel(block) }}</span>
                                                        <span
                                                            :class="block.enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium"
                                                        >
                                                            {{ block.enabled ? 'On' : 'Off' }}
                                                        </span>
                                                    </div>
                                                    <p class="text-xs text-gray-600 truncate">{{ getBlockPreview(block) }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Block actions -->
                                        <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                            <div class="flex items-center space-x-1">
                                                <button
                                                    @click.stop="toggleBlockEnabled(block.id)"
                                                    class="p-1 text-gray-400 hover:text-gray-600"
                                                    :title="block.enabled ? 'Disable' : 'Enable'"
                                                >
                                                    <svg v-if="block.enabled" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L12 12m-3.122-3.122L12 12m0 0l3.122 3.122M12 12l3.122-3.122M15 12a3 3 0 00-3-3"></path>
                                                    </svg>
                                                </button>
                                                <button
                                                    @click.stop="duplicateBlock(block.id)"
                                                    class="p-1 text-gray-400 hover:text-gray-600"
                                                    title="Duplicate"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                </button>
                                                <button
                                                    @click.stop="removeBlock(block.id)"
                                                    class="p-1 text-red-400 hover:text-red-600"
                                                    title="Delete"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Block Editor (Mobile) -->
                        <div v-if="selectedBlock && selectedBlockSchema" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        Edit {{ getBlockLabel(selectedBlock) }}
                                    </h3>
                                    <button
                                        @click="clearSelection"
                                        class="text-gray-400 hover:text-gray-600 p-1 hover:bg-gray-100 rounded"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <SchemaForm
                                    :fields="selectedBlockSchema.fields"
                                    :modelValue="selectedBlock.props"
                                    @update:modelValue="updateBlockProps(selectedBlock.id, $event)"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Preview View (Mobile) -->
                    <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-medium text-gray-900">Live Preview</h3>
                                <div class="text-sm text-gray-500">
                                    {{ enabledBlocksCount }} enabled blocks
                                </div>
                            </div>
                            <LivePreview :site="site" :blocks="blocks" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Block Modal -->
        <div v-if="showAddBlockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Add New Block</h3>
                        <button @click="showAddBlockModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <div
                            v-for="blockType in availableBlockTypes"
                            :key="blockType.type"
                            @click="handleAddBlock(blockType.type)"
                            class="flex items-center p-3 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 cursor-pointer transition-colors"
                        >
                            <div class="text-2xl mr-3">{{ getBlockIcon(blockType.type) }}</div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ blockType.label }}</div>
                                <div class="text-sm text-gray-600">{{ blockType.description }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button
                            @click="showAddBlockModal = false"
                            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>