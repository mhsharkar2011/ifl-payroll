<template>
    <div class="overflow-x-auto rounded-xl border border-gray-100">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        class="px-6 py-3 font-semibold"
                    >
                        {{ column.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="!data || data.length === 0">
                    <td :colspan="columns.length" class="px-6 py-8 text-center text-gray-500">
                        <i class="bi bi-inbox text-3xl block mb-2"></i>
                        No data available
                    </td>
                </tr>
                <tr
                    v-for="(row, index) in data"
                    :key="row.id || index"
                    class="border-t border-gray-100 hover:bg-gray-50 transition-colors"
                >
                    <td
                        v-for="column in columns"
                        :key="column.key"
                        class="px-6 py-4"
                    >
                        <slot :name="column.key" :row="row" :value="row[column.key]">
                            {{ row[column.key] }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({
    columns: {
        type: Array,
        required: true,
    },
    data: {
        type: Array,
        default: () => [],
    },
});
</script>
