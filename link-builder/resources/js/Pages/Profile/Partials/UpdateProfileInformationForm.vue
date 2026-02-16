<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    avatar: null,
});

const avatarPreview = ref(user.avatar ? `/storage/${user.avatar}` : null);

const handleAvatarChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.avatar = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeAvatar = () => {
    form.avatar = null;
    avatarPreview.value = user.avatar ? `/storage/${user.avatar}` : null;
    const input = document.getElementById('avatar');
    if (input) input.value = '';
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.post(route('profile.update'), { 
                forceFormData: true
            })"
            class="mt-6 space-y-6"
        >
            <!-- Avatar Section -->
            <div>
                <InputLabel for="avatar" value="Profile Photo" />
                
                <div class="mt-2 flex items-center gap-4">
                    <!-- Avatar Preview -->
                    <div class="relative">
                        <div v-if="avatarPreview" 
                             class="h-20 w-20 rounded-full overflow-hidden bg-gray-100 border-2 border-gray-200">
                            <img :src="avatarPreview" 
                                 alt="Avatar preview" 
                                 class="h-full w-full object-cover" />
                        </div>
                        <div v-else 
                             class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-2xl font-semibold border-2 border-gray-300">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                    </div>
                    
                    <!-- Upload Controls -->
                    <div class="flex-1">
                        <input
                            id="avatar"
                            type="file"
                            accept="image/*"
                            @change="handleAvatarChange"
                            class="hidden"
                        />
                        <div class="flex gap-2">
                            <label
                                for="avatar"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 cursor-pointer"
                            >
                                Choose Photo
                            </label>
                            <button
                                v-if="form.avatar"
                                type="button"
                                @click="removeAvatar"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                Remove
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            JPG, PNG or GIF. Max 2MB.
                        </p>
                    </div>
                </div>

                <InputError class="mt-2" :message="form.errors.avatar" />
            </div>

            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
