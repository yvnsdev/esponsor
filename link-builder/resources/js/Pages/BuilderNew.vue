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
            <div class="border-b border-slate-200 bg-white">
                <div class="flex items-center justify-between px-8 py-4">
                    <div class="flex items-center space-x-6">
                        <div>
                            <h1 class="text-xl font-semibold text-slate-900">
                                Page Builder
                            </h1>
                            <div class="flex items-center space-x-4 mt-1">
                                <span class="text-sm text-slate-600">{{ site.name }}</span>
                                <span class="w-1 h-1 bg-slate-400 rounded-full"></span>
                                <span class="text-xs text-slate-500">{{ blocksCount }} blocks</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    Draft
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <!-- Save Status -->
                        <div v-if="isLoading" class="flex items-center text-slate-500 text-sm">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving...
                        </div>
                        
                        <!-- Mobile Toggle -->
                        <button
                            @click="toggleMobileView"
                            class="lg:hidden inline-flex items-center px-3 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                        >
                            <svg v-if="mobileViewMode === 'editor'" class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ mobileViewMode === 'editor' ? 'Preview' : 'Editor' }}
                        </button>
                        
                        <!-- View Public -->
                        <a
                            :href="`/@${site.slug}`"
                            target="_blank"
                            class="hidden sm:inline-flex items-center px-3 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                            title="View public page"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            View Live
                        </a>
                        
                        <!-- Publish Button -->
                        <button
                            @click="handlePublishPage"
                            :disabled="!canPublish || isPublishing"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:bg-slate-400 disabled:cursor-not-allowed transition-colors"
                            :title="canPublish ? 'Publish your page' : 'Enable at least one block to publish'"
                        >
                            <svg v-if="isPublishing" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            {{ isPublishing ? 'Publishing...' : 'Publish' }}
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Notification Messages -->
        <div class="relative z-10">
            <!-- Success message -->
            <div v-if="publishSuccess" class="mx-8 mt-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ publishSuccess }}
                </div>
                <button @click="clearPublishMessages" class="text-emerald-600 hover:text-emerald-800 p-1 rounded hover:bg-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Error messages -->
            <div v-if="publishError" class="mx-8 mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    {{ publishError }}
                </div>
                <button @click="clearPublishMessages" class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div v-if="saveError" class="mx-8 mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                {{ saveError }}
            </div>
        </div>

        <!-- Main Layout -->
        <div class="flex-1 flex overflow-hidden bg-slate-50">
            <!-- Desktop: 3-Panel Layout -->
            <div class="hidden lg:flex flex-1">
                <!-- Left Sidebar: Blocks -->
                <div class="w-72 bg-white border-r border-slate-200 overflow-y-auto">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wide">Blocks</h2>
                            <button
                                @click="showAddBlockModal = true"
                                class="inline-flex items-center p-1.5 border border-slate-300 rounded-md text-slate-600 hover:text-slate-800 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                                title="Add block"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Empty state -->
                        <div v-if="blocks.length === 0" class="text-center py-12">
                            <div class="w-12 h-12 mx-auto mb-4 text-slate-400">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <h3 class="text-sm font-medium text-slate-900 mb-1">No blocks yet</h3>
                            <p class="text-sm text-slate-500 mb-4">Add your first block to get started</p>
                            <button
                                @click="showAddBlockModal = true"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Add Block
                            </button>
                        </div>

                        <!-- Blocks list -->
                        <div v-else class="space-y-2">
                            <div
                                v-for="(block, index) in blocks"
                                :key="block.id"
                                @click="selectBlock(block.id)"
                                class="group relative p-3 border rounded-lg cursor-pointer transition-all duration-150"
                                :class="{ 
                                    'border-slate-200 bg-slate-50 hover:border-slate-300': !selectedBlockId || selectedBlockId !== block.id,
                                    'border-indigo-300 bg-indigo-50 ring-1 ring-indigo-300': selectedBlockId === block.id,
                                    'opacity-60': !block.enabled
                                }"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center space-x-3 min-w-0 flex-1">
                                        <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-sm">
                                            {{ getBlockIcon(block.type) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-slate-900 truncate">{{ getBlockLabel(block) }}</p>
                                            <p class="text-xs text-slate-500 truncate">{{ getBlockPreview(block) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            @click.stop="toggleBlockEnabled(block.id)"
                                            class="p-1 text-slate-400 hover:text-slate-600 rounded"
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
                                            @click.stop="removeBlock(block.id)"
                                            class="p-1 text-slate-400 hover:text-red-500 rounded"
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

                <!-- Middle Panel: Editor -->
                <div class="flex-1 bg-white border-r border-slate-200 overflow-y-auto">
                    <div class="h-full">
                        <div v-if="selectedBlock && selectedBlockSchema" class="h-full flex flex-col">
                            <div class="p-6 border-b border-slate-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900">
                                            {{ getBlockLabel(selectedBlock) }}
                                        </h2>
                                        <p class="text-sm text-slate-600 mt-1">Configure block properties</p>
                                    </div>
                                    <button
                                        @click="clearSelection"
                                        class="p-1.5 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-100"
                                    >
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex-1 p-6 overflow-y-auto">
                                <SchemaForm
                                    :fields="selectedBlockSchema.fields"
                                    :modelValue="selectedBlock.props"
                                    @update:modelValue="updateBlockProps(selectedBlock.id, $event)"
                                />
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-else class="h-full flex items-center justify-center">
                            <div class="text-center max-w-sm">
                                <div class="w-16 h-16 mx-auto mb-4 text-slate-300">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-slate-900 mb-2">Block Editor</h3>
                                <p class="text-slate-600">Select a block from the sidebar to edit its properties and customize its appearance.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Preview -->
                <div class="flex-1 bg-slate-100 overflow-y-auto">
                    <div class="p-6">
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wide">Live Preview</h2>
                                <div class="text-xs text-slate-500">
                                    {{ enabledBlocksCount }}/{{ blocksCount }} blocks enabled
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <LivePreview :site="site" :blocks="blocks" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Layout: Tabbed Views -->
            <div class="lg:hidden flex-1 flex flex-col">
                <!-- Mobile Tabs -->
                <div class="border-b border-slate-200 bg-white">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button
                            @click="mobileViewMode = 'editor'"
                            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
                            :class="mobileViewMode === 'editor' 
                                ? 'border-indigo-500 text-indigo-600' 
                                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        >
                            <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editor
                        </button>
                        <button
                            @click="mobileViewMode = 'preview'"
                            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
                            :class="mobileViewMode === 'preview' 
                                ? 'border-indigo-500 text-indigo-600' 
                                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        >
                            <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Preview
                        </button>
                    </nav>
                </div>

                <!-- Mobile Content -->
                <div class="flex-1 overflow-y-auto bg-slate-50">
                    <!-- Editor View -->
                    <div v-if="mobileViewMode === 'editor'" class="p-4 space-y-6">
                        <!-- Blocks List -->
                        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-slate-900">Blocks</h3>
                                    <button
                                        @click="showAddBlockModal = true"
                                        class="inline-flex items-center p-1.5 border border-slate-300 rounded-md text-slate-600 hover:text-slate-800 hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="p-4">
                                <!-- Empty state -->
                                <div v-if="blocks.length === 0" class="text-center py-8">
                                    <div class="w-12 h-12 mx-auto mb-4 text-slate-400">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-medium text-slate-900 mb-1">No blocks yet</h3>
                                    <p class="text-sm text-slate-500 mb-4">Add your first block to get started</p>
                                </div>

                                <!-- Blocks list -->
                                <div v-else class="space-y-2">
                                    <div
                                        v-for="block in blocks"
                                        :key="block.id"
                                        @click="selectBlock(block.id)"
                                        class="p-3 border rounded-lg cursor-pointer transition-all"
                                        :class="{ 
                                            'border-slate-200 hover:border-slate-300': selectedBlockId !== block.id,
                                            'border-indigo-300 bg-indigo-50 ring-1 ring-indigo-300': selectedBlockId === block.id,
                                            'opacity-60': !block.enabled
                                        }"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3 min-w-0 flex-1">
                                                <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-sm">
                                                    {{ getBlockIcon(block.type) }}
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium text-slate-900 truncate">{{ getBlockLabel(block) }}</p>
                                                    <p class="text-xs text-slate-500 truncate">{{ getBlockPreview(block) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Block Editor -->
                        <div v-if="selectedBlock && selectedBlockSchema" class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-slate-900">
                                        Edit {{ getBlockLabel(selectedBlock) }}
                                    </h3>
                                    <button
                                        @click="clearSelection"
                                        class="p-1 text-slate-400 hover:text-slate-600 rounded"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="p-4">
                                <SchemaForm
                                    :fields="selectedBlockSchema.fields"
                                    :modelValue="selectedBlock.props"
                                    @update:modelValue="updateBlockProps(selectedBlock.id, $event)"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Preview View -->
                    <div v-else class="p-4">
                        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                                <h3 class="text-sm font-medium text-slate-900">Live Preview</h3>
                                <p class="text-xs text-slate-500 mt-1">{{ enabledBlocksCount }}/{{ blocksCount }} blocks enabled</p>
                            </div>
                            <LivePreview :site="site" :blocks="blocks" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Block Modal -->
        <div v-if="showAddBlockModal" class="fixed inset-0 bg-slate-600 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
            <div class="relative bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Add New Block</h3>
                            <p class="text-sm text-slate-600 mt-1">Choose a block type to add to your page</p>
                        </div>
                        <button 
                            @click="showAddBlockModal = false" 
                            class="p-1.5 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-100"
                        >
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
                            class="flex items-center p-4 border border-slate-200 rounded-lg hover:border-indigo-300 hover:bg-indigo-50 cursor-pointer transition-all group"
                        >
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-slate-100 group-hover:bg-indigo-100 mr-4">
                                <span class="text-lg">{{ getBlockIcon(blockType.type) }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-slate-900 group-hover:text-indigo-900">{{ blockType.label }}</div>
                                <div class="text-sm text-slate-600 group-hover:text-indigo-700">{{ blockType.description }}</div>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 pt-4 border-t border-slate-200">
                        <button
                            @click="showAddBlockModal = false"
                            class="px-4 py-2 text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>