<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SchemaForm from '@/Components/SchemaForm.vue';
import LivePreview from '@/Components/LivePreview.vue';
import ProfileSettings from '@/Components/ProfileSettings.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useBlockManager } from '@/Composables/useBlockManager.js';
import { 
    Plus, Eye, Rocket, ExternalLink, Check, AlertCircle,
    GripVertical, Copy, Trash2, Power, PowerOff, X, Loader2,
    Undo2, Redo2, Save, Monitor, Smartphone
} from 'lucide-vue-next';

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

// Get flash messages from Inertia
const page = usePage();
const publishSuccess = computed(() => page.props.flash?.publishSuccess || null);
const publishError = computed(() => page.props.flash?.publishError || null);

// Block management
const {
    blocks,
    isLoading,
    saveError,
    saveStatus,
    hasUnsavedChanges,
    blocksCount,
    enabledBlocksCount,
    availableBlockTypes,
    selectedBlockId,
    selectedBlock,
    selectedBlockSchema,
    canPublish,
    canUndo,
    canRedo,
    undo,
    redo,
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
} = useBlockManager(props.pageDraft.blocks || [], props.blockSchemas, props.blockCatalog);

// UI state
const showAddBlockModal = ref(false);
const mobileView = ref('blocks'); // 'blocks', 'edit', 'preview'
const isPublishing = ref(false);
const previewMode = ref('mobile'); // 'mobile' or 'desktop'

// Save status display
const saveStatusText = computed(() => {
    if (saveStatus.value === 'saving') return 'Saving...';
    if (saveStatus.value === 'error') return 'Error';
    return 'Saved';
});

const saveStatusColor = computed(() => {
    if (saveStatus.value === 'saving') return 'text-[#22B8A6]';
    if (saveStatus.value === 'error') return 'text-red-600';
    return 'text-green-600';
});

// Warn before leaving if there are unsaved changes
const handleBeforeUnload = (e) => {
    if (hasUnsavedChanges.value && saveStatus.value === 'saving') {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
};

onMounted(() => {
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
});

// Add block and close modal
const handleAddBlock = (type) => {
    addBlock(type);
    showAddBlockModal.value = false;
    // Auto-select the new block on mobile
    if (window.innerWidth < 1024) {
        mobileView.value = 'edit';
    }
};

// Handle publish
const handlePublish = async () => {
    if (!canPublish.value || isPublishing.value) return;
    
    isPublishing.value = true;
    
    router.post('/dashboard/builder/publish', {}, {
        onFinish: () => {
            isPublishing.value = false;
        },
        preserveScroll: true,
    });
};

// Icon mapping for block types
const getIconComponent = (iconName) => {
    const iconMap = {
        'type': 'Type',
        'link': 'Link',
        'image': 'Image',
        'video': 'Video',
    };
    return iconMap[iconName] || 'Square';
};

// Get block preview text
const getBlockPreview = (block) => {
    switch (block.type) {
        case 'text':
            const text = block.props.body || block.props.content || '';
            return text.substring(0, 50) + (text.length > 50 ? '...' : '');
        case 'links':
            const linkCount = block.props.links?.length || 0;
            return `${linkCount} link${linkCount !== 1 ? 's' : ''}`;
        case 'image':
            return block.props.altText || 'Image';
        case 'video':
            return block.props.title || 'Video';
        default:
            return 'Block';
    }
};

// Get icon for block type
const getBlockIcon = (type) => {
    const catalog = props.blockCatalog.find(b => b.type === type);
    return catalog?.icon || 'square';
};

// Mobile view switching
const switchMobileView = (view) => {
    mobileView.value = view;
};

// Watch for block selection changes
watch(selectedBlockId, (newId) => {
    if (newId && window.innerWidth < 1024) {
        mobileView.value = 'edit';
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Build - ${site.name || 'Your Site'}`" />

        <!-- Sticky Header -->
        <div class="sticky top-0 z-30 bg-white border-b border-slate-200/70 shadow-sm">
            <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Left: Site Name -->
                    <div class="flex-1 min-w-0 flex items-center gap-3">
                        <div>
                            <h1 class="text-lg font-semibold text-slate-900 truncate flex items-center gap-2">
                                {{ site.name || 'Your Site' }}
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">
                                    Draft
                                </span>
                            </h1>
                            <p class="text-sm text-slate-500">
                                {{ blocksCount }} blocks • {{ enabledBlocksCount }} enabled
                            </p>
                        </div>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center gap-3">
                        <!-- Undo/Redo -->
                        <div class="hidden md:flex items-center gap-1 border border-slate-200 rounded-lg p-1">
                            <button
                                @click="undo"
                                :disabled="!canUndo"
                                :title="'Undo (Ctrl+Z)'"
                                class="p-1.5 rounded hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                            >
                                <Undo2 class="w-4 h-4 text-slate-600" />
                            </button>
                            <button
                                @click="redo"
                                :disabled="!canRedo"
                                :title="'Redo (Ctrl+Y)'"
                                class="p-1.5 rounded hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                            >
                                <Redo2 class="w-4 h-4 text-slate-600" />
                            </button>
                        </div>

                        <!-- Save Status -->
                        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-slate-50 rounded-lg text-sm">
                            <Loader2 v-if="saveStatus === 'saving'" class="w-4 h-4 animate-spin text-[#22B8A6]" />
                            <Check v-else-if="saveStatus === 'saved'" class="w-4 h-4 text-green-600" />
                            <AlertCircle v-else class="w-4 h-4 text-red-600" />
                            <span :class="saveStatusColor">{{ saveStatusText }}</span>
                        </div>

                        <!-- View Public Page -->
                        <a
                            :href="`/@${site.slug}`"
                            target="_blank"
                            class="hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors"
                        >
                            <Eye class="w-4 h-4" />
                            <span>Preview</span>
                            <ExternalLink class="w-3 h-3" />
                        </a>

                        <!-- Publish Button -->
                        <button
                            @click="handlePublish"
                            :disabled="!canPublish || isPublishing"
                            :title="canPublish ? 'Publish your changes' : 'Enable at least one block to publish'"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#22B8A6] rounded-lg hover:bg-[#1a9585] disabled:bg-slate-300 disabled:cursor-not-allowed transition-colors shadow-sm"
                        >
                            <Loader2 v-if="isPublishing" class="w-4 h-4 animate-spin" />
                            <Rocket v-else class="w-4 h-4" />
                            <span>{{ isPublishing ? 'Publishing...' : 'Publish' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <div v-if="publishSuccess || publishError" class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div v-if="publishSuccess" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-start gap-3">
                <Check class="w-5 h-5 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                    <p class="font-medium">{{ publishSuccess }}</p>
                </div>
            </div>
            <div v-if="publishError" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-start gap-3">
                <AlertCircle class="w-5 h-5 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                    <p class="font-medium">{{ publishError }}</p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-slate-50 min-h-[calc(100vh-4rem)]">
            <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
                
                <!-- Desktop 3-Column Layout -->
                <div class="hidden lg:grid lg:grid-cols-12 gap-6">
                    <!-- Left Sidebar: Blocks List (320px) -->
                    <div class="lg:col-span-3 space-y-4">
                        <!-- Profile Settings -->
                        <ProfileSettings :site="site" />
                        
                        <!-- Add Block Button -->
                        <button
                            @click="showAddBlockModal = true"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-[#22B8A6] bg-white border-2 border-dashed border-[#22B8A6]/40 rounded-xl hover:border-[#22B8A6] hover:bg-[#22B8A6]/10 transition-all"
                        >
                            <Plus class="w-4 h-4" />
                            <span>Add Block</span>
                        </button>

                        <!-- Blocks List -->
                        <div class="bg-white border border-slate-200/70 rounded-xl shadow-sm overflow-hidden">
                            <div class="p-4 border-b border-slate-100">
                                <h3 class="text-sm font-semibold text-slate-900">Page Blocks</h3>
                            </div>
                            
                            <div v-if="blocks.length === 0" class="p-8 text-center">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center">
                                    <Plus class="w-8 h-8 text-slate-400" />
                                </div>
                                <p class="text-sm text-slate-600 font-medium">No blocks yet</p>
                                <p class="text-xs text-slate-500 mt-1">Click "Add Block" to get started</p>
                            </div>

                            <div v-else class="divide-y divide-slate-100 max-h-[calc(100vh-20rem)] overflow-y-auto">
                                <div
                                    v-for="(block, index) in blocks"
                                    :key="block.id"
                                    @click="selectBlock(block.id)"
                                    class="p-3 cursor-pointer transition-colors"
                                    :class="[
                                        selectedBlockId === block.id 
                                            ? 'bg-[#22B8A6]/10 border-l-4 border-[#22B8A6]' 
                                            : 'hover:bg-slate-50 border-l-4 border-transparent',
                                        !block.enabled && 'opacity-50'
                                    ]"
                                >
                                    <div class="flex items-start gap-3">
                                        <GripVertical class="w-4 h-4 text-slate-400 mt-1 flex-shrink-0" />
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-sm font-medium text-slate-900 truncate">
                                                    {{ getBlockLabel(block) }}
                                                </span>
                                                <span
                                                    :class="[
                                                        'inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium',
                                                        block.enabled 
                                                            ? 'bg-green-100 text-green-700'
                                                            : 'bg-slate-100 text-slate-600'
                                                    ]"
                                                >
                                                    {{ block.enabled ? 'On' : 'Off' }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-500 truncate">{{ getBlockPreview(block) }}</p>
                                        </div>
                                    </div>

                                    <!-- Block Actions -->
                                    <div class="flex items-center gap-1 mt-2 ml-7">
                                        <button
                                            @click.stop="moveBlockUp(block.id)"
                                            :disabled="index === 0"
                                            class="p-1 text-slate-400 hover:text-slate-600 disabled:opacity-30"
                                            title="Move up"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        </button>
                                        <button
                                            @click.stop="moveBlockDown(block.id)"
                                            :disabled="index === blocks.length - 1"
                                            class="p-1 text-slate-400 hover:text-slate-600 disabled:opacity-30"
                                            title="Move down"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        <button
                                            @click.stop="toggleBlockEnabled(block.id)"
                                            class="p-1 text-slate-400 hover:text-slate-600"
                                            :title="block.enabled ? 'Disable' : 'Enable'"
                                        >
                                            <Power v-if="block.enabled" class="w-4 h-4" />
                                            <PowerOff v-else class="w-4 h-4" />
                                        </button>
                                        <button
                                            @click.stop="duplicateBlock(block.id)"
                                            class="p-1 text-slate-400 hover:text-slate-600"
                                            title="Duplicate"
                                        >
                                            <Copy class="w-4 h-4" />
                                        </button>
                                        <button
                                            @click.stop="removeBlock(block.id)"
                                            class="p-1 text-red-400 hover:text-red-600"
                                            title="Delete"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Center: Block Editor -->
                    <div class="lg:col-span-5">
                        <div class="bg-white border border-slate-200/70 rounded-xl shadow-sm overflow-hidden">
                            <div v-if="selectedBlock && selectedBlockSchema" class="h-full">
                                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-slate-900">
                                        Edit {{ getBlockLabel(selectedBlock) }}
                                    </h3>
                                    <button
                                        @click="clearSelection"
                                        class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                                    >
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                                <div class="p-6 max-h-[calc(100vh-16rem)] overflow-y-auto">
                                    <SchemaForm
                                        :fields="selectedBlockSchema.fields"
                                        :modelValue="selectedBlock.props"
                                        @update:modelValue="updateBlockProps(selectedBlock.id, $event)"
                                    />
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-else class="p-12 text-center">
                                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-slate-100 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-2">No block selected</h3>
                                <p class="text-sm text-slate-600">Select a block from the list to edit its properties</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Live Preview -->
                    <div class="lg:col-span-4">
                        <div class="sticky top-24">
                            <div class="bg-white border border-slate-200/70 rounded-xl shadow-sm overflow-hidden">
                                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-slate-900">Live Preview</h3>
                                    
                                    <!-- Mobile/Desktop Toggle -->
                                    <div class="flex gap-1 bg-slate-100 rounded-lg p-1">
                                        <button
                                            @click="previewMode = 'mobile'"
                                            :class="[
                                                'px-3 py-1.5 text-xs font-medium rounded transition-colors',
                                                previewMode === 'mobile'
                                                    ? 'bg-white text-slate-900 shadow-sm'
                                                    : 'text-slate-600 hover:text-slate-900'
                                            ]"
                                        >
                                            <Smartphone class="w-4 h-4" />
                                        </button>
                                        <button
                                            @click="previewMode = 'desktop'"
                                            :class="[
                                                'px-3 py-1.5 text-xs font-medium rounded transition-colors',
                                                previewMode === 'desktop'
                                                    ? 'bg-white text-slate-900 shadow-sm'
                                                    : 'text-slate-600 hover:text-slate-900'
                                            ]"
                                        >
                                            <Monitor class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <LivePreview :site="site" :blocks="blocks" :mode="previewMode" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Layout with Tabs -->
                <div class="lg:hidden">
                    <!-- Mobile Tabs -->
                    <div class="flex gap-2 mb-4 bg-white border border-slate-200/70 rounded-xl p-1">
                        <button
                            @click="switchMobileView('blocks')"
                            :class="[
                                'flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                mobileView === 'blocks'
                                    ? 'bg-[#22B8A6] text-white'
                                    : 'text-slate-600 hover:text-slate-900'
                            ]"
                        >
                            Blocks
                        </button>
                        <button
                            @click="switchMobileView('edit')"
                            :class="[
                                'flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                mobileView === 'edit'
                                    ? 'bg-[#22B8A6] text-white'
                                    : 'text-slate-600 hover:text-slate-900'
                            ]"
                        >
                            Edit
                        </button>
                        <button
                            @click="switchMobileView('preview')"
                            :class="[
                                'flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                mobileView === 'preview'
                                    ? 'bg-[#22B8A6] text-white'
                                    : 'text-slate-600 hover:text-slate-900'
                            ]"
                        >
                            Preview
                        </button>
                    </div>

                    <!-- Blocks View -->
                    <div v-show="mobileView === 'blocks'" class="space-y-4">
                        <button
                            @click="showAddBlockModal = true"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-[#22B8A6] bg-white border-2 border-dashed border-[#22B8A6]/40 rounded-xl hover:border-[#22B8A6] hover:bg-[#22B8A6]/10 transition-all"
                        >
                            <Plus class="w-4 h-4" />
                            <span>Add Block</span>
                        </button>

                        <div class="bg-white border border-slate-200/70 rounded-xl shadow-sm overflow-hidden divide-y divide-slate-100">
                            <div
                                v-for="(block, index) in blocks"
                                :key="block.id"
                                @click="selectBlock(block.id)"
                                class="p-4 cursor-pointer hover:bg-slate-50"
                                :class="[!block.enabled && 'opacity-50']"
                            >
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-sm font-medium text-slate-900">
                                                {{ getBlockLabel(block) }}
                                            </span>
                                            <span
                                                :class="[
                                                    'inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium',
                                                    block.enabled 
                                                        ? 'bg-green-100 text-green-700'
                                                        : 'bg-slate-100 text-slate-600'
                                                ]"
                                            >
                                                {{ block.enabled ? 'On' : 'Off' }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500">{{ getBlockPreview(block) }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button
                                        @click.stop="toggleBlockEnabled(block.id)"
                                        class="flex-1 px-3 py-2 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg"
                                    >
                                        {{ block.enabled ? 'Disable' : 'Enable' }}
                                    </button>
                                    <button
                                        @click.stop="duplicateBlock(block.id)"
                                        class="flex-1 px-3 py-2 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg"
                                    >
                                        Duplicate
                                    </button>
                                    <button
                                        @click.stop="removeBlock(block.id)"
                                        class="px-3 py-2 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit View -->
                    <div v-show="mobileView === 'edit'">
                        <div class="bg-white border border-slate-200/70 rounded-xl shadow-sm overflow-hidden">
                            <div v-if="selectedBlock && selectedBlockSchema">
                                <div class="p-4 border-b border-slate-100">
                                    <h3 class="text-sm font-semibold text-slate-900">
                                        Edit {{ getBlockLabel(selectedBlock) }}
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <SchemaForm
                                        :fields="selectedBlockSchema.fields"
                                        :modelValue="selectedBlock.props"
                                        @update:modelValue="updateBlockProps(selectedBlock.id, $event)"
                                    />
                                </div>
                            </div>
                            <div v-else class="p-12 text-center">
                                <p class="text-sm text-slate-600">No block selected</p>
                                <p class="text-xs text-slate-500 mt-1">Go to Blocks tab to select one</p>
                            </div>
                        </div>
                    </div>

                    <!-- Preview View -->
                    <div v-show="mobileView === 'preview'">
                        <div class="bg-white border border-slate-200/70 rounded-xl shadow-sm overflow-hidden">
                            <div class="p-4">
                                <LivePreview :site="site" :blocks="blocks" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Block Modal -->
        <Teleport to="body">
            <div
                v-if="showAddBlockModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="showAddBlockModal = false"
            >
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <!-- Overlay -->
                    <div class="fixed inset-0 transition-opacity bg-slate-900/50" @click="showAddBlockModal = false"></div>

                    <!-- Modal -->
                    <div class="relative inline-block w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-slate-900">Add Block</h3>
                            <button
                                @click="showAddBlockModal = false"
                                class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                            >
                                <X class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="space-y-2">
                            <button
                                v-for="blockType in availableBlockTypes"
                                :key="blockType.type"
                                @click="handleAddBlock(blockType.type)"
                                class="w-full flex items-center gap-4 p-4 text-left border border-slate-200 rounded-xl hover:border-[#22B8A6]/50 hover:bg-[#22B8A6]/10 transition-all group"
                            >
                                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#22B8A6]/20 group-hover:bg-[#22B8A6]/30 flex items-center justify-center transition-colors">
                                    <component
                                        v-if="blockType.icon"
                                        :is="getIconComponent(blockType.icon)"
                                        class="w-5 h-5 text-[#22B8A6]"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-slate-900">{{ blockType.label }}</div>
                                    <div class="text-xs text-slate-600 mt-0.5">{{ blockType.description }}</div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Custom scrollbar for blocks list */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
