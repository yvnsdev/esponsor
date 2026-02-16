<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { User, Image as ImageIcon, Moon, Sun } from 'lucide-vue-next';

const props = defineProps({
    site: {
        type: Object,
        required: true
    }
});

const isEditing = ref(false);
const isSaving = ref(false);

const form = ref({
    name: props.site.name,
    bio: props.site.bio || '',
    avatar_url: props.site.avatar_url || '',
    theme_mode: props.site.theme_mode || 'light',
    banner_color: props.site.banner_color || '',
    banner_image_url: props.site.banner_image_url || '',
    nav_logo_url: props.site.nav_logo_url || ''
});

const toggleTheme = () => {
    form.value.theme_mode = form.value.theme_mode === 'light' ? 'dark' : 'light';
    saveSettings();
};

const saveSettings = () => {
    isSaving.value = true;
    
    router.post('/dashboard/builder/profile', form.value, {
        onSuccess: () => {
            isEditing.value = false;
            isSaving.value = false;
        },
        onError: () => {
            isSaving.value = false;
        },
        preserveScroll: true
    });
};

const cancelEdit = () => {
    form.value = {
        name: props.site.name,
        bio: props.site.bio || '',
        avatar_url: props.site.avatar_url || '',
        theme_mode: props.site.theme_mode || 'light',
        banner_color: props.site.banner_color || '',
        banner_image_url: props.site.banner_image_url || ''
    };
    isEditing.value = false;
};
</script>

<template>
    <div class="bg-white border border-slate-200/70 rounded-xl shadow-sm">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900">Profile Settings</h3>
            <button
                v-if="!isEditing"
                @click="isEditing = true"
                class="text-xs text-[#22B8A6] hover:text-[#1a9585] font-medium"
            >
                Edit
            </button>
        </div>

        <div class="p-4 space-y-4">
            <!-- Avatar Preview -->
            <div class="flex items-center gap-3">
                <div v-if="form.avatar_url" 
                     class="w-16 h-16 rounded-full overflow-hidden bg-slate-100 border-2 border-slate-200">
                    <img :src="form.avatar_url" 
                         :alt="form.name" 
                         class="w-full h-full object-cover" />
                </div>
                <div v-else 
                     class="w-16 h-16 rounded-full bg-gradient-to-br from-[#22B8A6] to-[#1a9585] flex items-center justify-center text-white text-xl font-bold border-2 border-slate-200">
                    {{ form.name.charAt(0).toUpperCase() }}
                </div>
                
                <div class="flex-1 min-w-0" v-if="!isEditing">
                    <h4 class="text-sm font-semibold text-slate-900 truncate">{{ form.name }}</h4>
                    <p class="text-xs text-slate-500 truncate">{{ form.bio || 'No bio yet' }}</p>
                </div>
            </div>

            <!-- Edit Form -->
            <div v-if="isEditing" class="space-y-3">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#22B8A6] focus:border-transparent"
                        placeholder="Your name"
                    />
                </div>

                <!-- Bio -->
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Bio</label>
                    <textarea
                        v-model="form.bio"
                        rows="3"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#22B8A6] focus:border-transparent resize-none"
                        placeholder="Tell something about yourself..."
                    ></textarea>
                </div>

                <!-- Avatar URL -->
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        <ImageIcon class="w-3 h-3 inline mr-1" />
                        Avatar URL
                    </label>
                    <input
                        v-model="form.avatar_url"
                        type="url"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#22B8A6] focus:border-transparent"
                        placeholder="https://example.com/avatar.jpg"
                    />
                </div>

                <!-- Public Nav Logo -->
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Public Nav Logo URL</label>
                    <input
                        v-model="form.nav_logo_url"
                        @change="saveSettings"
                        type="url"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#22B8A6] focus:border-transparent"
                        placeholder="https://example.com/logo.png"
                    />
                    <p class="text-xs text-slate-500 mt-2">Logo usado en la cabecera pública (no afecta al editor).</p>
                    <div v-if="form.nav_logo_url" class="mt-2">
                        <img :src="form.nav_logo_url" alt="nav logo" class="h-10 object-contain border rounded" />
                    </div>
                </div>

                <!-- Color Customization Section -->
                <div class="pt-3 border-t border-slate-200">
                    <h4 class="text-xs font-semibold text-slate-900 mb-3">🎨 Color Customization</h4>
                    
                    <!-- Banner Colors -->
                    <div class="space-y-3 mb-4">
                        <p class="text-xs font-medium text-slate-600">Banner / Profile</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">Background Color</label>
                                <input
                                    v-model="form.banner_color"
                                    @change="saveSettings"
                                    type="color"
                                    class="w-full h-9 border border-slate-300 rounded cursor-pointer"
                                />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">Or Image URL</label>
                                <input
                                    v-model="form.banner_image_url"
                                    @change="saveSettings"
                                    type="url"
                                    class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#22B8A6] focus:border-transparent"
                                    placeholder="https://..."
                                />
                            </div>
                        </div>
                    </div>


                </div>

                <!-- Save/Cancel Buttons -->
                <div class="flex gap-2 pt-2">
                    <button
                        @click="saveSettings"
                        :disabled="isSaving"
                        class="flex-1 px-3 py-2 text-xs font-semibold text-white bg-[#22B8A6] rounded-lg hover:bg-[#1a9585] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ isSaving ? 'Saving...' : 'Save' }}
                    </button>
                    <button
                        @click="cancelEdit"
                        :disabled="isSaving"
                        class="px-3 py-2 text-xs font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 disabled:opacity-50 transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </div>

            <!-- Theme Toggle (Always visible) -->
            <div v-if="!isEditing" class="pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-700">Theme Mode</span>
                    <button
                        @click="toggleTheme"
                        class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg transition-all"
                        :class="form.theme_mode === 'dark' 
                            ? 'bg-slate-800 text-slate-100' 
                            : 'bg-amber-100 text-amber-900'"
                    >
                        <Moon v-if="form.theme_mode === 'dark'" class="w-3.5 h-3.5" />
                        <Sun v-else class="w-3.5 h-3.5" />
                        {{ form.theme_mode === 'dark' ? 'Dark' : 'Light' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
