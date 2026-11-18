<template>
    <EmployeeLayout page-title="My Payslips">
        <Head title="My Payslips" />
        <div class="space-y-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold text-gray-800">My Payslips</h2>
                <p class="text-sm text-gray-500 mt-1">View your monthly salary details</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Month</th>
                                <th class="px-6 py-3 text-left font-semibold">Basic</th>
                                <th class="px-6 py-3 text-left font-semibold">Overtime</th>
                                <th class="px-6 py-3 text-left font-semibold">Deductions</th>
                                <th class="px-6 py-3 text-left font-semibold">Net Salary</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="payroll in payrolls?.data || []" :key="payroll.id" class="hover:bg-gray-50/70">
                                <td class="px-6 py-3 text-gray-700 font-medium">
                                    {{ getMonthName(payroll.month) }} {{ payroll.year }}
                                </td>
                                <td class="px-6 py-3 text-gray-700">৳{{ formatCurrency(payroll.basic_salary) }}</td>
                                <td class="px-6 py-3 text-green-600">+৳{{ formatCurrency(payroll.overtime_amount) }}</td>
                                <td class="px-6 py-3 text-red-600">-৳{{ formatCurrency(payroll.deductions) }}</td>
                                <td class="px-6 py-3 font-bold text-gray-800">৳{{ formatCurrency(payroll.net_salary) }}</td>
                                <td class="px-6 py-3">
                                    <span :class="statusClass(payroll.status)" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold">
                                        {{ capitalize(payroll.status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!payrolls?.data?.length">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="bi bi-inbox text-3xl block mb-2 text-gray-300"></i>
                                    No payroll records found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </EmployeeLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';

defineProps({
    payrolls: {
        type: Object,
        default: () => ({ data: [] }),
    },
});

const getMonthName = (m) => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][m - 1] || '-';
const formatCurrency = (v) => v ? Number(v).toLocaleString('en-BD') : '0';
const capitalize = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
const statusClass = (s) => ({
    draft: 'bg-gray-50 text-gray-700 ring-1 ring-gray-200',
    approved: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
    paid: 'bg-green-50 text-green-700 ring-1 ring-green-200',
}[s] || 'bg-gray-50 text-gray-700 ring-1 ring-gray-200');
</script>
