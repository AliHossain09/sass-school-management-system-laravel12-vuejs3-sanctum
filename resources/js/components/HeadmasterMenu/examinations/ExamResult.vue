<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const router = useRouter()
const toast = useToast()

const results = ref([])
const loading = ref(false)

// Placeholder for fetching published results history.
// For now, this will display mock data or be empty until the backend is fully integrated.
const fetchResults = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/exam-results', { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } })
        results.value = response.data.data
    } catch (error) {
        toast.error('Failed to load exam results.')
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchResults()
})

const goToAddResult = () => {
    router.push('/exam-result/add')
}
</script>

<template>
    <HeadmasterLayout>
        <!-- Breadcrumb -->
        <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2 items-center">
            <p class="text-gray-700">Headmaster ></p>
            <p class="text-gray-700">Examination ></p>
            <p class="text-gray-700 font-semibold">Exam Result</p>
        </div>

        <div class="p-6 shadow-2xl bg-gray-50 rounded">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold mb-1">Exam Result List</h1>
                <button @click="goToAddResult" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">
                    Add Exam Result
                </button>
            </div>

            <div class="overflow-x-auto rounded-lg bg-white shadow">
                <table class="min-w-full table-auto text-left whitespace-nowrap">
                    <thead class="bg-blue-100 text-gray-700 border-b">
                        <tr>
                            <th class="px-4 py-3 font-semibold">S.L</th>
                            <th class="px-4 py-3 font-semibold">Name</th>
                            <th class="px-4 py-3 font-semibold text-center">Roll No</th>
                            <th class="px-4 py-3 font-semibold text-center">Class</th>
                            <th class="px-4 py-3 font-semibold text-center">Exam</th>
                            <th class="px-4 py-3 font-semibold text-center">Grand Total</th>
                            <th class="px-4 py-3 font-semibold text-center">Percent (%)</th>
                            <th class="px-4 py-3 font-semibold text-center">Grade</th>
                            <th class="px-4 py-3 font-semibold text-center">Result</th>
                            <th class="px-4 py-3 font-semibold text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="10" class="text-center py-4 text-gray-500">Loading results...</td>
                        </tr>
                        <tr v-else-if="results.length === 0">
                            <td colspan="10" class="text-center py-4 text-gray-500">No results found.</td>
                        </tr>
                        <tr v-else v-for="(result, index) in results" :key="result.id" class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ String(index + 1).padStart(2, '0') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ result.student.first_name }} {{ result.student.last_name }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ result.roll_number }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ result.class_name }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ result.exam_name }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ result.grand_total }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ result.percent }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ result.grade }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['px-2 py-1 rounded text-xs font-semibold', result.result === 'Pass' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                                    {{ result.result }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 hover:text-gray-800 cursor-pointer">
                                &#8942;
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </HeadmasterLayout>
</template>
