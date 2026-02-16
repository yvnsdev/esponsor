import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash-es';

export function useBlockManager(initialBlocks, schemas, catalog = null) {
    const blocks = ref([...initialBlocks]);
    const isLoading = ref(false);
    const saveError = ref(null);
    const selectedBlockId = ref(null);
    
    // Publishing state
    const isPublishing = ref(false);
    const publishError = ref(null);

    // Undo/Redo system
    const history = ref([JSON.stringify(initialBlocks)]);
    const historyIndex = ref(0);
    const hasUnsavedChanges = ref(false);

    // Save status for UX
    const saveStatus = ref('saved'); // 'saved', 'saving', 'error'
    const lastSavedAt = ref(Date.now());

    // Computed properties
    const blocksCount = computed(() => blocks.value.length);
    const enabledBlocksCount = computed(() => 
        blocks.value.filter(block => block.enabled).length
    );

    const selectedBlock = computed(() => 
        blocks.value.find(block => block.id === selectedBlockId.value)
    );

    const selectedBlockSchema = computed(() => {
        if (!selectedBlock.value) return null;
        return schemas[selectedBlock.value.type] || null;
    });

    // Check if page can be published (has enabled blocks)
    const canPublish = computed(() => {
        return enabledBlocksCount.value > 0;
    });

    // Undo/Redo capabilities
    const canUndo = computed(() => historyIndex.value > 0);
    const canRedo = computed(() => historyIndex.value < history.value.length - 1);

    // Add to history (for undo/redo)
    const addToHistory = () => {
        const currentState = JSON.stringify(blocks.value);
        
        // Only add if different from current history state
        if (history.value[historyIndex.value] !== currentState) {
            // Remove any redo history
            history.value = history.value.slice(0, historyIndex.value + 1);
            history.value.push(currentState);
            historyIndex.value++;
            
            // Limit history to last 50 states
            if (history.value.length > 50) {
                history.value.shift();
                historyIndex.value--;
            }
        }
    };

    // Undo action
    const undo = () => {
        if (!canUndo.value) return;
        
        historyIndex.value--;
        blocks.value = JSON.parse(history.value[historyIndex.value]);
        hasUnsavedChanges.value = true;
        debouncedSave();
    };

    // Redo action
    const redo = () => {
        if (!canRedo.value) return;
        
        historyIndex.value++;
        blocks.value = JSON.parse(history.value[historyIndex.value]);
        hasUnsavedChanges.value = true;
        debouncedSave();
    };

    // Debounced save function
    const debouncedSave = debounce(async () => {
        try {
            saveStatus.value = 'saving';
            isLoading.value = true;
            saveError.value = null;
            
            await new Promise((resolve, reject) => {
                router.patch('/dashboard/builder/blocks', 
                    { blocks: blocks.value },
                    {
                        onSuccess: () => resolve(),
                        onError: (errors) => reject(errors),
                        preserveState: true,
                        preserveScroll: true,
                    }
                );
            });
            
            saveStatus.value = 'saved';
            lastSavedAt.value = Date.now();
            hasUnsavedChanges.value = false;
        } catch (error) {
            saveStatus.value = 'error';
            saveError.value = 'Error saving blocks. Please try again.';
            console.error('Save error:', error);
        } finally {
            isLoading.value = false;
        }
    }, 600);

    // Block operations
    const addBlock = (type) => {
        const schema = schemas[type];
        if (!schema) return;

        const newBlock = {
            id: generateUUID(),
            type: type,
            enabled: true,
            props: { ...schema.defaults }
        };

        blocks.value.push(newBlock);
        addToHistory();
        hasUnsavedChanges.value = true;
        debouncedSave();
    };

    const removeBlock = (blockId) => {
        blocks.value = blocks.value.filter(block => block.id !== blockId);
        // Clear selection if removed block was selected
        if (selectedBlockId.value === blockId) {
            selectedBlockId.value = null;
        }
        // Also clear selection if no blocks remain
        if (blocks.value.length === 0) {
            selectedBlockId.value = null;
        }
        addToHistory();
        hasUnsavedChanges.value = true;
        debouncedSave();
    };

    const duplicateBlock = (blockId) => {
        const blockIndex = blocks.value.findIndex(block => block.id === blockId);
        if (blockIndex === -1) return;

        const originalBlock = blocks.value[blockIndex];
        const duplicatedBlock = {
            ...originalBlock,
            id: generateUUID(),
            props: { ...originalBlock.props }
        };

        blocks.value.splice(blockIndex + 1, 0, duplicatedBlock);
        addToHistory();
        hasUnsavedChanges.value = true;
        debouncedSave();
    };

    const moveBlockUp = (blockId) => {
        const currentIndex = blocks.value.findIndex(block => block.id === blockId);
        if (currentIndex <= 0) return;

        const block = blocks.value.splice(currentIndex, 1)[0];
        blocks.value.splice(currentIndex - 1, 0, block);
        addToHistory();
        hasUnsavedChanges.value = true;
        debouncedSave();
    };

    const moveBlockDown = (blockId) => {
        const currentIndex = blocks.value.findIndex(block => block.id === blockId);
        if (currentIndex === -1 || currentIndex >= blocks.value.length - 1) return;

        const block = blocks.value.splice(currentIndex, 1)[0];
        blocks.value.splice(currentIndex + 1, 0, block);
        addToHistory();
        hasUnsavedChanges.value = true;
        debouncedSave();
    };

    const toggleBlockEnabled = (blockId) => {
        const block = blocks.value.find(block => block.id === blockId);
        if (!block) return;

        block.enabled = !block.enabled;
        addToHistory();
        hasUnsavedChanges.value = true;
        debouncedSave();
    };

    // Block selection
    const selectBlock = (blockId) => {
        selectedBlockId.value = blockId;
    };

    const clearSelection = () => {
        selectedBlockId.value = null;
    };

    // Block props editing
    const updateBlockProps = (blockId, newProps) => {
        const block = blocks.value.find(block => block.id === blockId);
        if (!block) return;

        block.props = { ...newProps };
        addToHistory();
        hasUnsavedChanges.value = true;
        debouncedSave();
    };

    // Get available block types for adding
    const availableBlockTypes = computed(() => {
        if (catalog && catalog.length > 0) {
            return catalog;
        }
        
        // Fallback to schemas
        return Object.entries(schemas).map(([type, schema]) => ({
            type,
            label: schema.label || type,
            icon: schema.icon || 'square',
            description: schema.description || getBlockDescription(type)
        }));
    });

    const getBlockDescription = (type) => {
        const descriptions = {
            text: 'Add text content with custom styling',
            links: 'Create clickable buttons and links',
            image: 'Display images with optional links',
            video: 'Embed YouTube videos',
            'social-icons': 'Display social media icons in a row',
            'cta': 'Call-to-action with title, text and button'
        };
        return descriptions[type] || 'Custom content block';
    };

    // Helper function to generate UUID
    const generateUUID = () => {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    };

    // Get block label from schema
    const getBlockLabel = (block) => {
        const schema = schemas[block.type];
        return schema ? schema.label : `Unknown Block (${block.type})`;
    };

    // Publish functionality
    const publishPage = async () => {
        if (!canPublish.value) {
            publishError.value = 'Cannot publish a page with no enabled blocks. Please enable at least one block first.';
            return false;
        }

        try {
            isPublishing.value = true;
            publishError.value = null;
            
            const response = await new Promise((resolve, reject) => {
                router.post('/dashboard/builder/publish', {}, {
                    onSuccess: (page) => {
                        resolve(page.props.flash || {});
                    },
                    onError: (errors) => {
                        reject(errors);
                    },
                    preserveState: true,
                    preserveScroll: true,
                });
            });
            
            return true;
        } catch (error) {
            publishError.value = error.message || 'Error publishing page. Please try again.';
            console.error('Publish error:', error);
            return false;
        } finally {
            isPublishing.value = false;
        }
    };

    // Clear publish messages
    const clearPublishMessages = () => {
        publishError.value = null;
    };

    return {
        blocks,
        isLoading,
        saveError,
        saveStatus,
        lastSavedAt,
        hasUnsavedChanges,
        blocksCount,
        enabledBlocksCount,
        availableBlockTypes,
        selectedBlockId,
        selectedBlock,
        selectedBlockSchema,
        canPublish,
        isPublishing,
        publishError,
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
        publishPage,
        clearPublishMessages
    };
}