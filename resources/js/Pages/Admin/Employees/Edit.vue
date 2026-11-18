<template>
    <AdminLayout page-title="Edit Employee">
        <Head :title="`Edit - ${employee.first_name} ${employee.last_name}`" />

        <div class="max-w-6xl mx-auto space-y-3 text-sm pb-20">

            <!-- Compact Top Bar -->
            <div class="flex items-center justify-between bg-white border border-orange-200 p-2">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.employees.index')" class="text-orange-600 hover:text-orange-800 font-bold px-2">
                        <i class="bi bi-arrow-left"></i> Back
                    </Link>
                    <span class="text-gray-600 font-semibold">Entity Name: {{ employee.company_name || 'Default Company' }}</span>
                </div>
                <div class="text-gray-700 font-bold">
                    Editing: {{ employee.first_name }} {{ employee.last_name }} ({{ employee.employee_id }})
                </div>
            </div>

            <!-- Error Summary (Compact) -->
            <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 border border-red-200 p-2 text-red-700">
                <strong>Errors:</strong> {{ Object.values(form.errors).join(', ') }}
            </div>

            <form @submit.prevent="submit" class="flex gap-4 items-start">

                <!-- LEFT COLUMN: Main Form -->
                <div class="flex-1 space-y-4">

                    <!-- Personal Information -->
                    <div class="border border-orange-200 bg-white">
                        <div class="bg-orange-50 px-3 py-1 border-b border-orange-200 font-bold text-gray-700">
                            Personal Information
                        </div>
                        <div class="p-3 grid grid-cols-2 gap-x-6 gap-y-2">
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">Employee ID <span class="text-red-500">*</span></label>
                                <input v-model="form.employee_id" type="text" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none" />
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">First Name <span class="text-red-500">*</span></label>
                                <input v-model="form.first_name" type="text" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none" />
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">Last Name <span class="text-red-500">*</span></label>
                                <input v-model="form.last_name" type="text" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none" />
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">Email <span class="text-red-500">*</span></label>
                                <input v-model="form.email" type="email" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none" />
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">Phone <span class="text-red-500">*</span></label>
                                <input v-model="form.phone" type="text" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none" />
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">Date of Birth</label>
                                <input v-model="form.date_of_birth" type="date" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none" />
                            </div>
                            <div class="flex items-center gap-2 col-span-2">
                                <label class="w-32 text-right text-xs">Address</label>
                                <input v-model="form.address" type="text" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none" />
                            </div>
                        </div>
                    </div>

                    <!-- Employment Details -->
                    <div class="border border-orange-200 bg-white">
                        <div class="bg-orange-50 px-3 py-1 border-b border-orange-200 font-bold text-gray-700">
                            Employment Details
                        </div>
                        <div class="p-3 grid grid-cols-2 gap-x-6 gap-y-2">
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">Department <span class="text-red-500">*</span></label>
                                <select v-model="form.department_id" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none">
                                    <option value="">Select</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">Designation <span class="text-red-500">*</span></label>
                                <select v-model="form.designation_id" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none">
                                    <option value="">Select</option>
                                    <option v-for="desig in designations" :key="desig.id" :value="desig.id">{{ desig.name }}</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">Salary <span class="text-red-500">*</span></label>
                                <input v-model="form.salary" type="number" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none" />
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">Joining Date <span class="text-red-500">*</span></label>
                                <input v-model="form.joining_date" type="date" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none" />
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">Status <span class="text-red-500">*</span></label>
                                <select v-model="form.status" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="terminated">Terminated</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">Device ID</label>
                                <input v-model="form.device_user_id" type="text" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none" />
                            </div>
                        </div>
                    </div>

                    <!-- Bank Information -->
                    <div class="border border-orange-200 bg-white">
                        <div class="bg-orange-50 px-3 py-1 border-b border-orange-200 font-bold text-gray-700">
                            Bank Information
                        </div>
                        <div class="p-3 grid grid-cols-2 gap-x-6 gap-y-2">
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">Bank Name</label>
                                <input v-model="form.bank_name" type="text" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none" />
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="w-32 text-right text-xs">Account Number</label>
                                <input v-model="form.bank_account_number" type="text" class="flex-1 border border-gray-300 px-2 py-1 text-xs focus:border-orange-500 outline-none" />
                            </div>
                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN: Photo (Compact) -->
                <div class="w-48 border border-orange-200 bg-white p-3 flex flex-col items-center gap-2">
                    <div class="w-full aspect-square border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                        <img v-if="avatarPreview || employee.avatar_url" :src="avatarPreview || employee.avatar_url" class="w-full h-full object-cover" @error="onImageError" />
                        <i v-else class="bi bi-person text-6xl text-gray-300"></i>
                    </div>

                    <input ref="avatarInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onAvatarChange" />

                    <div class="flex flex-wrap gap-1 w-full justify-center mt-1">
                        <button type="button" @click="$refs.avatarInput.click()" class="bg-orange-300 hover:bg-orange-400 text-gray-800 text-[10px] px-2 py-1 flex items-center gap-1 rounded">
                            <i class="bi bi-upload"></i> Upload
                        </button>
                        <button v-if="employee.avatar_url && !avatarPreview" type="button" @click="removeAvatar" class="bg-red-300 hover:bg-red-400 text-gray-800 text-[10px] px-2 py-1 flex items-center gap-1 rounded">
                            <i class="bi bi-x"></i> Remove
                        </button>
                        <button v-if="avatarPreview" type="button" @click="cancelAvatar" class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-[10px] px-2 py-1 flex items-center gap-1 rounded">
                            <i class="bi bi-arrow-counterclockwise"></i> Cancel
                        </button>
                    </div>
                </div>

            </form>

            <!-- Sticky Bottom Bar for Actions -->
            <div class="fixed bottom-0 left-0 right-0 bg-gray-100 border-t border-gray-300 p-2 flex justify-end gap-2 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-50 md:pl-64">
                <Link :href="route('admin.employees.index')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold px-6 py-1.5 border border-gray-400 rounded text-xs flex items-center gap-1">
                    <i class="bi bi-x-circle"></i> Cancel
                </Link>
                <button type="button" @click="submit" :disabled="form.processing" class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-1.5 border border-orange-600 rounded text-xs flex items-center gap-1 disabled:opacity-50">
                    <i class="bi bi-floppy" v-if="!form.processing"></i>
                    <i class="bi bi-arrow-repeat animate-spin" v-else></i>
                    {{ form.processing ? 'Saving...' : 'Save' }}
                </button>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    employee:     { type: Object, required: true, default: () => ({}) },
    departments:  { type: Array,  default: () => [] },
    designations: { type: Array,  default: () => [] },
});

const avatarInput   = ref(null);
const avatarPreview = ref(null);
const removeAvatarFlag = ref(false);

const onAvatarChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        alert('❌ File size exceeds 2 MB limit');
        return;
    }
    form.avatar = file;
    avatarPreview.value = URL.createObjectURL(file);
    removeAvatarFlag.value = false;
};

const cancelAvatar = () => {
    avatarPreview.value = null;
    form.avatar = null;
    removeAvatarFlag.value = false;
    if (avatarInput.value) avatarInput.value.value = '';
};

const removeAvatar = () => {
    if (!confirm('Remove the current profile photo?')) return;
    avatarPreview.value = null;
    form.avatar = null;
    removeAvatarFlag.value = true;
    form.remove_avatar = true;
};

const onImageError = (event) => {
    event.target.style.display = 'none';
};

const form = useForm({
    first_name:           props.employee?.first_name || '',
    last_name:            props.employee?.last_name || '',
    email:                props.employee?.email || '',
    phone:                props.employee?.phone || '',
    employee_id:          props.employee?.employee_id || '',
    department_id:        props.employee?.department_id || '',
    designation_id:       props.employee?.designation_id || '',
    salary:               props.employee?.salary || '',
    joining_date:         formatDateForInput(props.employee?.joining_date),
    date_of_birth:        formatDateForInput(props.employee?.date_of_birth),
    address:              props.employee?.address || '',
    device_user_id:       props.employee?.device_user_id || '',
    status:               props.employee?.status || 'active',

    // ✅ FIXED: Access the nested 'bank' relationship object
    bank_name:            props.employee?.bank?.bank_name || '',
    bank_account_number:  props.employee?.bank?.bank_account_number || '',

    avatar:               null,
    remove_avatar:        false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'POST',
        remove_avatar: removeAvatarFlag.value,
    })).post(route('admin.employees.update', props.employee.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            avatarPreview.value = null;
            removeAvatarFlag.value = false;
        },
    });
};

function formatDateForInput(date) {
    if (!date) return '';
    try {
        const d = new Date(date);
        if (isNaN(d.getTime())) return '';
        return d.toISOString().split('T')[0];
    } catch (e) {
        return '';
    }
}
</script>
