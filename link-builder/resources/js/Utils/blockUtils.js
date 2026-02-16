/**
 * Block Manager Utilities
 * Helper functions for the Block Manager UI
 */

// UUID Generator (simple version)
export const generateUUID = () => {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = Math.random() * 16 | 0;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
};

// Block Categories
export const BLOCK_CATEGORIES = {
    CONTENT: 'Content',
    NAVIGATION: 'Navigation', 
    MEDIA: 'Media',
    OTHER: 'Other'
};

// Block Icons
export const BLOCK_ICONS = {
    text: '📝',
    links: '🔗', 
    image: '🖼️',
    video: '📹',
    default: '⬜'
};

// Default debounce delay (ms)
export const DEFAULT_SAVE_DELAY = 500;

// Block status badges
export const getStatusBadgeClass = (enabled) => {
    return enabled 
        ? 'bg-green-100 text-green-800' 
        : 'bg-gray-100 text-gray-800';
};

// Block preview text generators
export const generateBlockPreview = (block) => {
    switch (block.type) {
        case 'text':
            return block.props.content?.substring(0, 50) + 
                   (block.props.content?.length > 50 ? '...' : '');
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

// Validate block structure
export const validateBlockStructure = (block) => {
    const requiredFields = ['id', 'type', 'enabled', 'props'];
    return requiredFields.every(field => 
        Object.prototype.hasOwnProperty.call(block, field)
    );
};

// Array move helpers
export const moveArrayElement = (array, fromIndex, toIndex) => {
    const element = array[fromIndex];
    const newArray = [...array];
    newArray.splice(fromIndex, 1);
    newArray.splice(toIndex, 0, element);
    return newArray;
};