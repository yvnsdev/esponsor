<template>
    <div class="space-y-6">
        <div
            v-for="field in fields"
            :key="field.name"
            class="space-y-2"
        >
            <!-- Field Label -->
            <label
                :for="field.name"
                class="block text-sm font-medium text-gray-700"
            >
                {{ field.label }}
                <span v-if="isRequired(field)" class="text-red-500">*</span>
            </label>

            <!-- Text Input -->
            <input
                v-if="field.type === 'text'"
                :id="field.name"
                :value="modelValue[field.name] || ''"
                @input="updateValue(field.name, $event.target.value)"
                type="text"
                :placeholder="field.placeholder"
                :required="isRequired(field)"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': hasError(field.name) }"
            />

            <!-- Textarea -->
            <textarea
                v-else-if="field.type === 'textarea'"
                :id="field.name"
                :value="modelValue[field.name] || ''"
                @input="updateValue(field.name, $event.target.value)"
                :placeholder="field.placeholder"
                :required="isRequired(field)"
                rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': hasError(field.name) }"
            ></textarea>

            <!-- URL Input -->
            <input
                v-else-if="field.type === 'url' || field.type === 'image'"
                :id="field.name"
                :value="modelValue[field.name] || ''"
                @input="updateValue(field.name, $event.target.value)"
                type="url"
                :placeholder="field.placeholder"
                :required="isRequired(field)"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': hasError(field.name) }"
            />

            <!-- Color Input -->
            <div
                v-else-if="field.type === 'color'"
                class="flex items-center space-x-3"
            >
                <input
                    :id="field.name"
                    :value="modelValue[field.name] || '#000000'"
                    @input="updateValue(field.name, $event.target.value)"
                    type="color"
                    :required="isRequired(field)"
                    class="h-10 w-20 rounded border border-gray-300 cursor-pointer"
                />
                <input
                    :value="modelValue[field.name] || '#000000'"
                    @input="updateValue(field.name, $event.target.value)"
                    type="text"
                    :placeholder="field.placeholder"
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': hasError(field.name) }"
                />
            </div>

            <!-- Select Input -->
            <select
                v-else-if="field.type === 'select'"
                :id="field.name"
                :value="modelValue[field.name] || ''"
                @change="updateValue(field.name, $event.target.value)"
                :required="isRequired(field)"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': hasError(field.name) }"
            >
                <option value="" disabled>{{ field.placeholder || 'Select an option' }}</option>
                <option
                    v-for="option in field.options"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>

            <!-- Icon Picker -->
            <IconPicker
                v-else-if="field.type === 'icon'"
                :modelValue="modelValue[field.name] || 'link'"
                @update:modelValue="updateValue(field.name, $event)"
            />

            <!-- Checkbox Input -->
            <div
                v-else-if="field.type === 'checkbox'"
                class="flex items-center"
            >
                <input
                    :id="field.name"
                    :checked="modelValue[field.name] || false"
                    @change="updateValue(field.name, $event.target.checked)"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <label
                    :for="field.name"
                    class="ml-2 block text-sm text-gray-700"
                >
                    {{ field.help || field.placeholder }}
                </label>
            </div>

            <!-- Array Input (for links) -->
            <div
                v-else-if="field.type === 'array'"
                class="space-y-3"
            >
                <div
                    v-for="(item, index) in (modelValue[field.name] || [])"
                    :key="index"
                    class="p-4 border border-gray-200 rounded-md bg-gray-50"
                >
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-gray-700">
                            Item {{ index + 1 }}
                        </span>
                        <button
                            @click="removeArrayItem(field.name, index)"
                            type="button"
                            class="text-red-500 hover:text-red-700"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Sub-fields -->
                    <div class="space-y-3">
                        <div
                            v-for="subField in field.subFields"
                            :key="subField.name"
                            class="space-y-1"
                        >
                            <label class="block text-xs font-medium text-gray-600">
                                {{ subField.label }}
                                <span v-if="isRequired(subField)" class="text-red-500">*</span>
                            </label>
                            
                            <!-- Sub-field inputs -->
                            <input
                                v-if="subField.type === 'text'"
                                :value="item[subField.name] || ''"
                                @input="updateArrayItem(field.name, index, subField.name, $event.target.value)"
                                type="text"
                                :placeholder="subField.placeholder"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            />
                            <input
                                v-else-if="subField.type === 'url'"
                                :value="item[subField.name] || ''"
                                @input="updateArrayItem(field.name, index, subField.name, $event.target.value)"
                                type="url"
                                :placeholder="subField.placeholder"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            />
                            <select
                                v-else-if="subField.type === 'select'"
                                :value="item[subField.name] || ''"
                                @change="updateArrayItem(field.name, index, subField.name, $event.target.value)"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            >
                                <option value="" disabled>{{ subField.placeholder || 'Select' }}</option>
                                <option
                                    v-for="option in subField.options"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <IconPicker
                                v-else-if="subField.type === 'icon'"
                                :modelValue="item[subField.name] || 'link'"
                                @update:modelValue="updateArrayItem(field.name, index, subField.name, $event)"
                            />
                            <div
                                v-else-if="subField.type === 'color'"
                                class="flex items-center space-x-2"
                            >
                                <input
                                    :value="item[subField.name] || '#000000'"
                                    @input="updateArrayItem(field.name, index, subField.name, $event.target.value)"
                                    type="color"
                                    class="h-8 w-16 rounded border border-gray-300 cursor-pointer"
                                />
                                <input
                                    :value="item[subField.name] || '#000000'"
                                    @input="updateArrayItem(field.name, index, subField.name, $event.target.value)"
                                    type="text"
                                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Array Item Button -->
                <button
                    @click="addArrayItem(field)"
                    type="button"
                    class="w-full px-3 py-2 text-sm text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 transition-colors"
                >
                    + Add {{ field.label.slice(0, -1) || 'Item' }}
                </button>
            </div>

            <!-- Unsupported Field Type -->
            <div
                v-else
                class="text-sm text-gray-500 italic"
            >
                Unsupported field type: {{ field.type }}
            </div>

            <!-- Field Error -->
            <div
                v-if="hasError(field.name)"
                class="text-sm text-red-600"
            >
                {{ errors[field.name] }}
            </div>

            <!-- Field Help Text -->
            <div
                v-if="field.help"
                class="text-xs text-gray-500"
            >
                {{ field.help }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import IconPicker from './IconPicker.vue';

// Props
const props = defineProps({
    fields: {
        type: Array,
        required: true,
    },
    modelValue: {
        type: Object,
        default: () => ({}),
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

// Emits
const emit = defineEmits(['update:modelValue']);

// Computed
const isRequired = (field) => {
    return field.rules && field.rules.includes('required');
};

const hasError = (fieldName) => {
    return props.errors && props.errors[fieldName];
};

// Methods
const updateValue = (fieldName, value) => {
    const newModelValue = { ...props.modelValue };
    newModelValue[fieldName] = value;
    emit('update:modelValue', newModelValue);
};

const updateArrayItem = (fieldName, index, subFieldName, value) => {
    const newModelValue = { ...props.modelValue };
    const arrayValue = [...(newModelValue[fieldName] || [])];
    
    if (arrayValue[index]) {
        arrayValue[index] = { ...arrayValue[index], [subFieldName]: value };
    }
    
    newModelValue[fieldName] = arrayValue;
    emit('update:modelValue', newModelValue);
};

const addArrayItem = (field) => {
    const newModelValue = { ...props.modelValue };
    const arrayValue = [...(newModelValue[field.name] || [])];
    
    // Create new item with defaults based on subFields
    const newItem = {};
    if (field.subFields) {
        field.subFields.forEach(subField => {
            if (subField.type === 'color') {
                newItem[subField.name] = '#000000';
            } else {
                newItem[subField.name] = '';
            }
        });
    }
    
    arrayValue.push(newItem);
    newModelValue[field.name] = arrayValue;
    emit('update:modelValue', newModelValue);
};

const removeArrayItem = (fieldName, index) => {
    const newModelValue = { ...props.modelValue };
    const arrayValue = [...(newModelValue[fieldName] || [])];
    arrayValue.splice(index, 1);
    newModelValue[fieldName] = arrayValue;
    emit('update:modelValue', newModelValue);
};
</script>