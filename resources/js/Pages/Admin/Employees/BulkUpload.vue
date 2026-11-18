<template>
    <AdminLayout page-title="Bulk Upload Employees">
        <Head title="Bulk Upload Employees" />

        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-6 flex items-center gap-4">
                <Link
                    :href="route('admin.employees.index')"
                    class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                >
                    <i class="bi bi-arrow-left text-gray-600"></i>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Bulk Upload Employees</h1>
                    <p class="text-sm text-gray-500">Import multiple employees from Excel/CSV file</p>
                </div>
            </div>

            <!-- Success Message -->
            <div
                v-if="$page.props.flash?.success"
                class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-start gap-3"
            >
                <i class="bi bi-check-circle-fill text-green-500 text-xl shrink-0"></i>
                <p class="text-sm text-green-700 font-medium">{{ $page.props.flash.success }}</p>
            </div>

            <!-- Error Summary (ALL errors) -->
            <div
                v-if="Object.keys(form.errors).length > 0"
                class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6"
            >
                <div class="flex items-start gap-3">
                    <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl shrink-0"></i>
                    <div class="flex-1">
                        <h4 class="font-semibold text-red-800 mb-2">Import Failed - Please fix:</h4>
                        <ul class="text-sm text-red-700 space-y-1">
                            <li v-for="(error, field) in form.errors" :key="field" class="flex items-start gap-2">
                                <i class="bi bi-dot"></i>
                                <span><strong>{{ field }}:</strong> {{ error }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Step 1: Download Template -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                            <span class="font-bold">1</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Download Template</h3>
                            <p class="text-xs text-gray-500">Get the Excel template with example data</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        Download our template file. Fill it with your employee data and upload it back.
                    </p>
                    <a
                        :href="route('admin.employees.download-template')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg hover:from-green-700 hover:to-emerald-700 transition shadow-lg"
                    >
                        <i class="bi bi-download"></i>
                        Download Template (.xlsx)
                    </a>
                </div>
            </div>

            <!-- Step 2: Upload File -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-600 flex items-center justify-center text-white">
                            <span class="font-bold">2</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Upload Filled File</h3>
                            <p class="text-xs text-gray-500">Select your completed Excel/CSV file</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Dropzone -->
                        <div
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="handleDrop"
                            @click="$refs.fileInput.click()"
                            class="border-2 border-dashed rounded-2xl p-8 text-center cursor-pointer transition-all"
                            :class="isDragging
                                ? 'border-indigo-500 bg-indigo-50'
                                : selectedFile
                                    ? 'border-green-400 bg-green-50'
                                    : 'border-gray-300 bg-gray-50 hover:border-indigo-400 hover:bg-indigo-50'"
                        >
                            <input
                                ref="fileInput"
                                type="file"
                                @change="handleFileSelect"
                                accept=".xlsx,.xls,.csv"
                                class="hidden"
                            />

                            <template v-if="!selectedFile">
                                <div class="w-16 h-16 mx-auto rounded-full bg-white flex items-center justify-center mb-4">
                                    <i class="bi bi-cloud-arrow-up text-3xl text-indigo-600"></i>
                                </div>
                                <p class="text-sm font-semibold text-gray-800 mb-1">
                                    Drop your file here, or click to browse
                                </p>
                                <p class="text-xs text-gray-500">
                                    Supports .xlsx, .xls, .csv (max 10MB)
                                </p>
                            </template>

                            <template v-else>
                                <div class="w-16 h-16 mx-auto rounded-full bg-white flex items-center justify-center mb-4">
                                    <i class="bi bi-file-earmark-excel text-3xl text-green-600"></i>
                                </div>
                                <p class="text-sm font-semibold text-gray-800 mb-1">
                                    {{ selectedFile.name }}
                                </p>
                                <p class="text-xs text-gray-500 mb-3">
                                    {{ formatSize(selectedFile.size) }}
                                </p>
                                <button
                                    type="button"
                                    @click.stop="clearFile"
                                    class="text-xs text-red-600 hover:text-red-800 font-medium"
                                >
                                    <i class="bi bi-x-circle"></i> Remove
                                </button>
                            </template>
                        </div>

                        <!-- File-level Error -->
                        <div
                            v-if="form.errors?.file"
                            class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3"
                        >
                            <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl shrink-0"></i>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-red-800 mb-1">File Error</p>
                                <p class="text-sm text-red-700">{{ form.errors.file }}</p>
                            </div>
                        </div>

                        <!-- Progress Indicator -->
                        <div v-if="uploading" class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-arrow-repeat animate-spin text-indigo-600 text-xl"></i>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-indigo-800">Uploading & Processing...</p>
                                    <p class="text-xs text-indigo-600">Please wait, this may take a moment</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end gap-3">
                            <Link
                                :href="route('admin.employees.index')"
                                class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="!selectedFile || uploading"
                                class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-lg"
                            >
                                <i v-if="uploading" class="bi bi-arrow-repeat animate-spin"></i>
                                <i v-else class="bi bi-cloud-upload"></i>
                                {{ uploading ? 'Uploading...' : 'Upload & Import' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
                <h3 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                    <i class="bi bi-info-circle-fill"></i>
                    Important Notes
                </h3>
                <ul class="space-y-2 text-sm text-blue-800">
                    <li class="flex items-start gap-2">
                        <i class="bi bi-check-circle-fill text-blue-600 mt-0.5"></i>
                        <span><strong>Required fields:</strong> employee_id, first_name, email</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="bi bi-check-circle-fill text-blue-600 mt-0.5"></i>
                        <span><strong>Date format:</strong> YYYY-MM-DD (e.g., 2024-01-15)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="bi bi-check-circle-fill text-blue-600 mt-0.5"></i>
                        <span><strong>Password:</strong> If empty, default 'password' will be used</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="bi bi-check-circle-fill text-blue-600 mt-0.5"></i>
                        <span><strong>Duplicate rows:</strong> Will be automatically skipped</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="bi bi-check-circle-fill text-blue-600 mt-0.5"></i>
                        <span><strong>Device User ID:</strong> For ZKTeco machine matching</span>
                    </li>
                </ul>

                <div class="mt-4 p-3 bg-white rounded-lg border border-blue-200">
                    <p class="text-xs font-semibold text-blue-900 mb-1">Supported Headers:</p>
                    <p class="text-xs text-blue-700 font-mono leading-relaxed">
                        employee_id, first_name, last_name, email, phone, department, designation,
                        salary, joining_date, date_of_birth, address, device_user_id, status, password
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

/* ========== State ========== */
const selectedFile = ref(null);
const isDragging = ref(false);
const uploading = ref(false);

const form = useForm({
    file: null,
});

/* ========== File Handlers ========== */
const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file size (10MB max)
    if (file.size > 10 * 1024 * 1024) {
        alert('❌ File size exceeds 10MB limit');
        return;
    }

    // Validate extension
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['xlsx', 'xls', 'csv'].includes(ext)) {
        alert('❌ Only .xlsx, .xls, .csv files are allowed');
        return;
    }

    selectedFile.value = file;
    form.file = file;
    form.clearErrors();

    console.log('📁 File selected:', {
        name: file.name,
        size: file.size,
        type: file.type,
    });
};

const handleDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    if (!file) return;

    // Same validation
    if (file.size > 10 * 1024 * 1024) {
        alert('❌ File size exceeds 10MB limit');
        return;
    }

    const ext = file.name.split('.').pop().toLowerCase();
    if (!['xlsx', 'xls', 'csv'].includes(ext)) {
        alert('❌ Only .xlsx, .xls, .csv files are allowed');
        return;
    }

    selectedFile.value = file;
    form.file = file;
    form.clearErrors();

    console.log('📁 File dropped:', file.name);
};

const clearFile = () => {
    selectedFile.value = null;
    form.file = null;
    form.clearErrors();

    if (document.querySelector('input[type="file"]')) {
        document.querySelector('input[type="file"]').value = '';
    }
};

/* ========== Submit ========== */
const submit = () => {
    if (!selectedFile.value) {
        alert('⚠️ Please select a file first');
        return;
    }

    uploading.value = true;
    form.clearErrors();

    console.log('🚀 Uploading to:', route('admin.employees.import'));
    console.log('📦 Form data:', form.data());

    form.post(route('admin.employees.import'), {
        forceFormData: true,
        preserveScroll: true,

        onStart: () => {
            console.log('⏳ Upload started');
        },

        onProgress: (progress) => {
            console.log('📊 Upload progress:', progress);
        },

        onSuccess: (page) => {
            console.log('✅ Upload successful:', page);
            uploading.value = false;
            clearFile();
        },

        onError: (errors) => {
            console.error('❌ Upload errors:', errors);
            uploading.value = false;

            // Show detailed error alert
            const errorMessages = Object.entries(errors)
                .map(([field, msg]) => `• ${field}: ${msg}`)
                .join('\n');

            alert('❌ Import Failed:\n\n' + errorMessages);
        },

        onFinish: () => {
            uploading.value = false;
        },
    });
};

/* ========== Helpers ========== */
const formatSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / 1048576).toFixed(2) + ' MB';
};
</script>
