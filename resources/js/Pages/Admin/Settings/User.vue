<template>
    <AdminLayout page-title="User Settings">
        <Head title="User Settings" />

        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <!-- ✅ Custom Avatar Component Logic -->
                <div class="relative w-16 h-16 rounded-full ring-4 ring-indigo-100 dark:ring-indigo-900/40 bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center overflow-hidden shrink-0">
                    <img
                        v-if="!headerAvatarError && avatarUrl"
                        :src="avatarUrl"
                        @error="headerAvatarError = true"
                        class="w-full h-full object-cover"
                    />
                    <span v-else class="text-xl font-bold text-indigo-600 dark:text-indigo-300">
                        {{ userInitials }}
                    </span>
                </div>

                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ user.name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ user.email }}
                        <span v-if="user.roles?.length" class="ml-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                            {{ user.roles.join(', ') }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Flash -->
            <div v-if="$page.props.flash?.success"
                 class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-green-500 text-xl"></i>
                <p class="text-sm text-green-700 dark:text-green-300 font-medium">{{ $page.props.flash.success }}</p>
            </div>
            <div v-if="$page.props.flash?.error"
                 class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl"></i>
                <p class="text-sm text-red-700 dark:text-red-300">{{ $page.props.flash.error }}</p>
            </div>

            <div class="grid lg:grid-cols-4 gap-6">
                <!-- Sidebar tabs -->
                <aside class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-2">
                        <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition text-left"
                                :class="activeTab === tab.key
                                    ? 'bg-indigo-600 text-white'
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'">
                            <i :class="[tab.icon, 'text-lg']"></i>
                            {{ tab.label }}
                            <span v-if="tab.key === 'security' && !user.email_verified"
                                  class="ml-auto w-2 h-2 rounded-full bg-red-500"></span>
                        </button>
                    </div>
                </aside>

                <!-- Content -->
                <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <!-- ================= AVATAR ================= -->
                    <div v-show="activeTab === 'avatar'" class="p-6 space-y-6">
                        <h3 class="font-bold text-gray-800 dark:text-gray-100">Profile Picture</h3>

                        <div class="flex items-center gap-6 flex-wrap">

                            <!-- ✅ Custom Large Avatar Component Logic -->
                            <div class="relative w-24 h-24 rounded-full ring-4 ring-indigo-100 dark:ring-indigo-900/40 bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center overflow-hidden shrink-0">
                                <img
                                    v-if="!previewAvatarError && (avatarPreview || avatarUrl)"
                                    :src="avatarPreview || avatarUrl"
                                    @error="previewAvatarError = true"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else class="text-3xl font-bold text-indigo-600 dark:text-indigo-300">
                                    {{ userInitials }}
                                </span>
                            </div>

                            <div class="flex-1 min-w-[200px] space-y-3">
                                <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="onAvatarChange" />

                                <div class="flex gap-2">
                                    <button @click="avatarInput.click()"
                                            class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                                        <i class="bi bi-upload mr-2"></i> Choose File
                                    </button>
                                    <button v-if="user.avatar" @click="removeAvatar" :disabled="avatarForm.processing"
                                            class="px-4 py-2 text-sm font-semibold text-red-600 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition">
                                        <i class="bi bi-trash mr-2"></i> Remove
                                    </button>
                                </div>

                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    JPG, PNG, or WebP. Max 2 MB.
                                </p>

                                <div v-if="avatarPreview" class="flex gap-2">
                                    <button @click="uploadAvatar" :disabled="avatarForm.processing"
                                            class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition disabled:opacity-50">
                                        <i v-if="avatarForm.processing" class="bi bi-arrow-repeat animate-spin mr-2"></i>
                                        <i v-else class="bi bi-check-lg mr-2"></i> Save Avatar
                                    </button>
                                    <button @click="cancelAvatar" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================= PROFILE ================= -->
                    <div v-show="activeTab === 'profile'" class="p-6 space-y-5">
                        <h3 class="font-bold text-gray-800 dark:text-gray-100">Profile Information</h3>

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Name</label>
                                <input v-model="profileForm.name" type="text"
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none" />
                                <p v-if="profileForm.errors.name" class="text-xs text-red-600 mt-1">{{ profileForm.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email</label>
                                <input v-model="profileForm.email" type="email"
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none" />
                                <p v-if="profileForm.errors.email" class="text-xs text-red-600 mt-1">{{ profileForm.errors.email }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                                <input v-model="profileForm.phone" type="text"
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900/40 outline-none" />
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button @click="saveProfile" :disabled="profileForm.processing"
                                    class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 flex items-center gap-2">
                                <i v-if="profileForm.processing" class="bi bi-arrow-repeat animate-spin"></i>
                                <i v-else class="bi bi-check-lg"></i> Save Profile
                            </button>
                        </div>
                    </div>

                    <!-- ================= PASSWORD ================= -->
                    <div v-show="activeTab === 'password'" class="p-6 space-y-5">
                        <h3 class="font-bold text-gray-800 dark:text-gray-100">Change Password</h3>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Current Password</label>
                            <input v-model="passwordForm.current_password" type="password"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none" />
                            <p v-if="passwordForm.errors.current_password" class="text-xs text-red-600 mt-1">{{ passwordForm.errors.current_password }}</p>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                                <input v-model="passwordForm.password" type="password"
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none" />
                                <p v-if="passwordForm.errors.password" class="text-xs text-red-600 mt-1">{{ passwordForm.errors.password }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                                <input v-model="passwordForm.password_confirmation" type="password"
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none" />
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button @click="savePassword" :disabled="passwordForm.processing"
                                    class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 flex items-center gap-2">
                                <i v-if="passwordForm.processing" class="bi bi-arrow-repeat animate-spin"></i>
                                <i v-else class="bi bi-shield-lock"></i> Change Password
                            </button>
                        </div>
                    </div>

                    <!-- ================= APPEARANCE ================= -->
                    <div v-show="activeTab === 'appearance'" class="p-6 space-y-6">
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-gray-100">Appearance</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Choose how the whole application looks to you.
                            </p>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <button v-for="t in themes" :key="t.value" @click="applyTheme(t.value)"
                                    class="flex flex-col items-center gap-3 p-4 rounded-xl border-2 transition"
                                    :class="preferencesForm.theme === t.value
                                        ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/20'
                                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                                <div class="w-full h-16 rounded-lg flex items-center justify-center"
                                     :class="t.preview">
                                    <i :class="[t.icon, 'text-2xl']"></i>
                                </div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ t.label }}</span>
                            </button>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-5 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Timezone</label>
                                <select v-model="preferencesForm.timezone"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                                    <option v-for="tz in timezones" :key="tz.value" :value="tz.value">{{ tz.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Language</label>
                                <select v-model="preferencesForm.language"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                                    <option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.label }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button @click="savePreferences" :disabled="preferencesForm.processing"
                                    class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 flex items-center gap-2">
                                <i v-if="preferencesForm.processing" class="bi bi-arrow-repeat animate-spin"></i>
                                <i v-else class="bi bi-check-lg"></i> Save Preferences
                            </button>
                        </div>
                    </div>

                    <!-- ================= NOTIFICATIONS ================= -->
                    <div v-show="activeTab === 'notifications'" class="p-6 space-y-4">
                        <h3 class="font-bold text-gray-800 dark:text-gray-100">Notifications</h3>

                        <label v-for="opt in notificationOptions" :key="opt.key"
                               class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition cursor-pointer">
                            <div class="flex items-start gap-3">
                                <i :class="[opt.icon, 'text-lg text-indigo-600 dark:text-indigo-400 mt-0.5']"></i>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ opt.label }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ opt.description }}</p>
                                </div>
                            </div>
                            <input type="checkbox" v-model="preferencesForm.notifications[opt.key]"
                                   class="w-5 h-5 rounded accent-indigo-600" />
                        </label>

                        <div class="flex justify-end pt-2">
                            <button @click="savePreferences" :disabled="preferencesForm.processing"
                                    class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 flex items-center gap-2">
                                <i v-if="preferencesForm.processing" class="bi bi-arrow-repeat animate-spin"></i>
                                <i v-else class="bi bi-bell"></i> Save Notifications
                            </button>
                        </div>
                    </div>

                    <!-- ================= SECURITY / SESSIONS ================= -->
                    <div v-show="activeTab === 'security'" class="p-6 space-y-6">
                        <!-- Email verification -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-5">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                                     :class="user.email_verified
                                        ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400'
                                        : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'">
                                    <i :class="user.email_verified ? 'bi bi-patch-check-fill' : 'bi bi-exclamation-circle-fill'" class="text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-100">
                                        {{ user.email_verified ? 'Email Verified' : 'Email Not Verified' }}
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ user.email_verified
                                            ? `Your email (${user.email}) has been verified.`
                                            : `Please verify your email address (${user.email}).` }}
                                    </p>

                                    <button v-if="!user.email_verified" @click="resendVerification" :disabled="resending"
                                            class="mt-3 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50 inline-flex items-center gap-2">
                                        <i v-if="resending" class="bi bi-arrow-repeat animate-spin"></i>
                                        <i v-else class="bi bi-envelope-arrow-up"></i>
                                        Resend Verification Email
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Active sessions -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-100">Active Sessions</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Devices currently signed into your account
                                    </p>
                                </div>
                                <button v-if="sessions.length > 1" @click="revokeOtherSessions"
                                        class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30">
                                    Revoke all others
                                </button>
                            </div>

                            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                <div v-for="s in sessions" :key="s.id"
                                     class="px-5 py-4 flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                                         :class="s.is_current
                                            ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'">
                                        <i :class="s.browser.platform === 'Windows' || s.browser.platform === 'macOS' || s.browser.platform === 'Linux'
                                            ? 'bi bi-display' : 'bi bi-phone'" class="text-xl"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">
                                            {{ s.browser.browser }} · {{ s.browser.platform }}
                                            <span v-if="s.is_current"
                                                  class="ml-2 text-xs px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 font-semibold">
                                                This device
                                            </span>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ s.ip_address }} · Last active {{ formatRelative(s.last_activity) }}
                                        </p>
                                    </div>
                                    <button v-if="!s.is_current" @click="revokeSession(s)"
                                            class="p-2 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition"
                                            title="Revoke">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <div v-if="!sessions.length" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No active sessions tracked (session driver is not "database").
                                </div>
                            </div>
                        </div>

                        <!-- Danger zone -->
                        <div class="border-2 border-red-200 dark:border-red-900/50 rounded-xl p-5">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                                    <i class="bi bi-exclamation-octagon-fill text-red-600 dark:text-red-400 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-red-700 dark:text-red-300">Delete Account</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Once deleted, all your data will be permanently removed. This cannot be undone.
                                    </p>

                                    <button @click="showDeleteModal = true"
                                            class="mt-3 px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">
                                        <i class="bi bi-trash mr-2"></i> Delete My Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Account Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
             @click.self="showDeleteModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-start gap-4 mb-5">
                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                        <i class="bi bi-trash3-fill text-red-600 dark:text-red-400 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-800 dark:text-gray-100 text-lg">Delete Account?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            This will permanently delete your account, avatar, and all associated data.
                        </p>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Confirm your password
                    </label>
                    <input v-model="deleteForm.password" type="password"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg text-sm focus:border-red-500 focus:ring-2 focus:ring-red-200 outline-none" />
                    <p v-if="deleteForm.errors.password" class="text-xs text-red-600 mt-1">
                        {{ deleteForm.errors.password }}
                    </p>
                </div>

                <div class="flex justify-end gap-2">
                    <button @click="showDeleteModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button @click="deleteAccount" :disabled="deleteForm.processing || !deleteForm.password"
                            class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 flex items-center gap-2">
                        <i v-if="deleteForm.processing" class="bi bi-arrow-repeat animate-spin"></i>
                        <i v-else class="bi bi-trash"></i> Delete Forever
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTheme } from '@/composables/useTheme';

const props = defineProps({
    user:      { type: Object, required: true },
    sessions:  { type: Array,  default: () => [] },
    timezones: { type: Array,  default: () => [] },
    languages: { type: Array,  default: () => [] },
});

/* ========== Global theme sync ========== */
const { setTheme } = useTheme();

/* ========== Tabs ========== */
const tabs = [
    { key: 'avatar',        label: 'Avatar',        icon: 'bi bi-person-circle' },
    { key: 'profile',       label: 'Profile',       icon: 'bi bi-person' },
    { key: 'password',      label: 'Password',      icon: 'bi bi-shield-lock' },
    { key: 'appearance',    label: 'Appearance',    icon: 'bi bi-palette' },
    { key: 'notifications', label: 'Notifications', icon: 'bi bi-bell' },
    { key: 'security',      label: 'Security',      icon: 'bi bi-shield-check' },
];
const activeTab = ref('avatar');

/* ========== Avatar ========== */
const avatarInput   = ref(null);
const avatarPreview = ref(null);

// Error tracking states
const headerAvatarError  = ref(false);
const previewAvatarError = ref(false);

// Compute initials for the pure CSS fallback
const userInitials = computed(() => {
    if (!props.user.name) return 'U';
    return props.user.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();
});

// ✅ Compute the Avatar URL
const avatarUrl = computed(() => {
    if (props.user.avatar_url) {
        return props.user.avatar_url;
    }
    // Generate a UI Avatar based on the user's name
    const safeName = encodeURIComponent(props.user.name || 'User');
    return `https://ui-avatars.com/api/?name=${safeName}&background=6366f1&color=fff&bold=true`;
});

const avatarForm = useForm({ avatar: null });

const onAvatarChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    avatarForm.avatar = file;
    avatarPreview.value = URL.createObjectURL(file);
    previewAvatarError.value = false; // reset error on new upload
};

const uploadAvatar = () => {
    avatarForm.post(route('admin.settings.user.avatar'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            avatarPreview.value = null;
            avatarForm.reset();
            headerAvatarError.value = false; // reset error on success
            previewAvatarError.value = false;
        },
    });
};

const cancelAvatar = () => {
    avatarPreview.value = null;
    avatarForm.reset();
    previewAvatarError.value = false;
    if (avatarInput.value) avatarInput.value.value = '';
};

const removeAvatar = () => {
    if (!confirm('Remove your avatar?')) return;
    router.delete(route('admin.settings.user.avatar.delete'), {
        preserveScroll: true,
        onSuccess: () => {
            headerAvatarError.value = false;
            previewAvatarError.value = false;
        }
    });
};

/* ========== Forms ========== */
const profileForm = useForm({
    name:  props.user.name,
    email: props.user.email,
    phone: props.user.phone || '',
});

const passwordForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
});

const preferencesForm = useForm({
    timezone: props.user.timezone || 'Asia/Dhaka',
    language: props.user.language || 'en',
    theme:    props.user.theme || 'system',
    notifications: {
        email:  props.user.notifications?.email ?? true,
        push:   props.user.notifications?.push ?? false,
        weekly: props.user.notifications?.weekly ?? true,
    },
});

const deleteForm = useForm({ password: '' });

/* ========== Notifications / themes ========== */
const notificationOptions = [
    { key: 'email',  icon: 'bi bi-envelope',      label: 'Email Notifications', description: 'Receive important updates via email' },
    { key: 'push',   icon: 'bi bi-phone',         label: 'Push Notifications',  description: 'Browser push notifications' },
    { key: 'weekly', icon: 'bi bi-calendar-week', label: 'Weekly Digest',       description: 'Summary of activity every Monday' },
];

const themes = [
    { value: 'light',  label: 'Light',  icon: 'bi bi-sun-fill',     preview: 'bg-gradient-to-br from-yellow-100 to-orange-100 text-orange-500' },
    { value: 'dark',   label: 'Dark',   icon: 'bi bi-moon-stars-fill', preview: 'bg-gradient-to-br from-slate-800 to-slate-900 text-indigo-400' },
    { value: 'system', label: 'System', icon: 'bi bi-display',      preview: 'bg-gradient-to-br from-gray-100 to-gray-300 text-gray-600' },
];

/* ========== Actions ========== */
const saveProfile = () => {
    profileForm.post(route('admin.settings.user.profile'), { preserveScroll: true });
};

const savePassword = () => {
    passwordForm.post(route('admin.settings.user.password'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

const savePreferences = () => {
    preferencesForm.post(route('admin.settings.user.preferences'), {
        preserveScroll: true,
    });
};

/**
 * Applies the theme globally AND stores it for the user.
 */
const applyTheme = (value) => {
    preferencesForm.theme = value;
    setTheme(value);   // instant global change
    savePreferences(); // persist to backend
};

/* ========== Email verification ========== */
const resending = ref(false);

const resendVerification = () => {
    resending.value = true;
    router.post(route('admin.settings.user.resend-verification'), {}, {
        preserveScroll: true,
        onFinish: () => { resending.value = false; },
    });
};

/* ========== Sessions ========== */
const revokeSession = (session) => {
    if (!confirm('Revoke this session? The device will be signed out.')) return;
    router.delete(route('admin.settings.user.sessions.destroy', session.id), {
        preserveScroll: true,
    });
};

const revokeOtherSessions = () => {
    if (!confirm('Revoke all other sessions? All other devices will be signed out.')) return;
    router.delete(route('admin.settings.user.sessions.destroy-others'), {
        preserveScroll: true,
    });
};

/* ========== Delete account ========== */
const showDeleteModal = ref(false);

const deleteAccount = () => {
    deleteForm.delete(route('admin.settings.user.account.destroy'), {
        onFinish: () => { deleteForm.reset(); },
    });
};

/* ========== Helpers ========== */
const formatRelative = (iso) => {
    if (!iso) return '-';
    const diff = (Date.now() - new Date(iso).getTime()) / 1000;
    if (diff < 60) return 'just now';
    if (diff < 3600) return `${Math.floor(diff / 60)} min ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} hr ago`;
    return `${Math.floor(diff / 86400)} days ago`;
};

/* ========== Sync theme on mount ========== */
onMounted(() => {
    setTheme(preferencesForm.theme);
});
</script>
