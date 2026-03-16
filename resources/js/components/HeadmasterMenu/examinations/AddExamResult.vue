<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const router = useRouter()
const toast = useToast()

// Filter Form State
const filter = ref({
    class_id: '',
    section_id: '',
    academic_year: '',
    exam_name_id: '',
})

// Optional feature: Total Marks to base percentage calculations off
const maxMarks = ref(1000)

// Data arrays for dropdowns
const classes = ref([])
const sections = ref([])
const examNames = ref([])

// loaded students ready for marking
const studentsToMark = ref([])
const isPublishing = ref(false)
const schedulePublishDate = ref('')
const loadingStudents = ref(false)

const authHeader = () => ({
    Authorization: `Bearer ${localStorage.getItem('token')}`
})

// Lifecycle: Load initial dropdown data
onMounted(() => {
    loadDropdowns()
})

const loadDropdowns = async () => {
    try {
        const [clsRes, examRes] = await Promise.all([
            axios.get('/api/classes', { headers: authHeader() }).catch(() => ({ data: { data: [{ id: 1, name: 'Class 1' }, { id: 2, name: 'Class 2' }] } })),
            axios.get('/api/examinations', { headers: authHeader() }).catch(() => ({ data: { data: [{ id: 1, name: 'Monthly Test' }, { id: 2, name: 'Final Exam' }] } }))
        ])
        classes.value = clsRes.data.data || clsRes.data
        examNames.value = examRes.data.data || examRes.data
    } catch (error) {
        toast.error('Failed to load classes or exams')
    }
}

// Watch class selection to load sections
watch(() => filter.value.class_id, async (newVal) => {
    filter.value.section_id = ''
    sections.value = []
    if (newVal) {
        try {
            const res = await axios.get(`/api/sections/by-class/${newVal}`, { headers: authHeader() })
            sections.value = res.data.data
        } catch (e) {
            // Mocking for UI dev
            sections.value = [{ id: 1, name: 'Section A' }, { id: 2, name: 'Section B' }]
        }
    }
})

// Watch all filters: If all required ones are selected, auto-load students
watch([() => filter.value.class_id, () => filter.value.section_id, () => filter.value.academic_year, () => filter.value.exam_name_id], ([cls, sec, yr, ex]) => {
    if (cls && sec && yr && ex) {
        fetchStudents()
    } else {
        studentsToMark.value = []
    }
})

const fetchStudents = async () => {
    loadingStudents.value = true
    try {
        // Since backend StudentService only filters by text search, we fetch a large page
        // and filter by class_id and section_id on the frontend.
        const res = await axios.get('/api/students', {
            headers: authHeader(),
            params: { per_page: 500 } // Get a large chunk to filter
        })
        const allItems = res.data.data || res.data
        
        // Filter by selected Class & Section
        let filteredStudents = allItems;
        if (filter.value.class_id) {
            filteredStudents = filteredStudents.filter(s => String(s.class_id) === String(filter.value.class_id))
        }
        if (filter.value.section_id) {
             filteredStudents = filteredStudents.filter(s => String(s.section_id) === String(filter.value.section_id))
        }

        // Map over them and attach marking fields
        studentsToMark.value = filteredStudents.map(stu => ({
            id: stu.id,
            name: `${stu.first_name || ''} ${stu.last_name || ''}`.trim(),
            roll_no: stu.roll_number || 'N/A',
            grand_total: ''
        }))

        if(studentsToMark.value.length === 0) {
            toast.info("No students found for this class and section.")
        }
    } catch (e) {
        toast.error("Failed to load students. Currently using dummy layout.")
        // Mock if error
        studentsToMark.value = [
            { id: 101, name: 'Kathryn Murphy', roll_no: '12', grand_total: '' },
            { id: 102, name: 'Jerome Bell', roll_no: '14', grand_total: '' },
        ]
    } finally {
        loadingStudents.value = false
    }
}

// Calculations are derived via computed logic or getters
const calculatePercent = (total) => {
    const t = parseFloat(total)
    if (isNaN(t)) return 0
    let per = (t / maxMarks.value) * 100
    return per > 100 ? 100 : parseFloat(per.toFixed(2))
}

const calculateGrade = (percent) => {
    if (percent === 0 || isNaN(percent)) return '-'
    if (percent >= 80) return 'A+'
    if (percent >= 70) return 'A'
    if (percent >= 60) return 'A-'
    if (percent >= 50) return 'B'
    if (percent >= 40) return 'C'
    if (percent >= 33) return 'D'
    return 'F'
}

const calculateResult = (grade) => {
    if (grade === '-' || grade === '') return '-'
    return grade === 'F' ? 'Fail' : 'Pass'
}

// Derived array with calculations included dynamically
const markedList = computed(() => {
    return studentsToMark.value.map(stu => {
        const percent = calculatePercent(stu.grand_total)
        const grade = calculateGrade(percent)
        const result = calculateResult(grade)
        return {
            ...stu,
            percent,
            grade,
            result
        }
    })
})

const submitResults = async () => {
    // Collect data
    const payload = {
        class_id: filter.value.class_id,
        section_id: filter.value.section_id,
        academic_year: filter.value.academic_year,
        exam_name_id: filter.value.exam_name_id,
        schedule_time: schedulePublishDate.value || null,
        results: markedList.value
    }
    
    isPublishing.value = true
    try {
        await axios.post('/api/exam-results', payload, { headers: authHeader() })
        
        toast.success(`Exam Results Published! ${payload.schedule_time ? 'Scheduled for ' + payload.schedule_time : 'Live Now'}`)
        router.push('/exam-result')
    } catch (e) {
        toast.error('Failed to submit results')
    } finally {
        isPublishing.value = false
    }
}
</script>

<template>
    <HeadmasterLayout>
        <!-- Breadcrumb -->
        <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2 items-center">
            <p class="text-gray-700">Headmaster ></p>
            <p class="text-gray-700">Examination ></p>
            <p class="text-gray-700 font-semibold cursor-pointer hover:underline" @click="router.push('/exam-result')">Exam Result</p>
            <p class="text-gray-700">></p>
            <p class="text-gray-700">Add</p>
        </div>

        <div class="p-6 shadow-2xl bg-gray-50 rounded">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">Add Exam Result</h1>

            <!-- Filters Section -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8 bg-white p-4 rounded shadow-sm">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class *</label>
                    <select v-model="filter.class_id" class="w-full border-gray-300 rounded-md shadow-sm border p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Class</option>
                        <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Section *</label>
                    <select v-model="filter.section_id" :disabled="!filter.class_id" class="w-full border-gray-300 rounded-md shadow-sm border p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Section</option>
                        <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Year *</label>
                    <select v-model="filter.academic_year" class="w-full border-gray-300 rounded-md shadow-sm border p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Year</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Exam *</label>
                    <select v-model="filter.exam_name_id" class="w-full border-gray-300 rounded-md shadow-sm border p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Exam</option>
                        <option v-for="e in examNames" :key="e.id" :value="e.id">{{ e.name }}</option>
                    </select>
                </div>
                <div class="flex items-center justify-center pt-6">
                    <p class="text-sm text-gray-500 italic text-center w-full bg-gray-100 rounded p-1">
                        Select all to load students
                    </p>
                </div>
            </div>

            <!-- Pre-computation Settings -->
            <div v-if="studentsToMark.length > 0" class="flex justify-between items-center mb-4 bg-blue-50 p-4 rounded border border-blue-100">
                <div class="flex items-center gap-2">
                    <label class="font-medium text-blue-900">Base Max Marks (for % calc):</label>
                    <input type="number" v-model="maxMarks" class="border rounded p-1 w-24 text-center" min="10" />
                </div>
                
                <div class="flex items-center gap-2">
                    <label class="font-medium text-blue-900">Schedule Publish Time (Optional):</label>
                    <input type="datetime-local" v-model="schedulePublishDate" class="border rounded p-1" title="Leave empty to publish immediately" />
                </div>
            </div>

            <!-- Students Marking Table -->
            <div v-if="loadingStudents" class="text-center py-10 text-gray-500 text-lg">Loading students...</div>
            
            <div v-else-if="studentsToMark.length === 0" class="text-center py-12 bg-white rounded border border-dashed border-gray-300 text-gray-500">
                Please select Class, Section, Year, and Exam to auto-load students.
            </div>

            <div v-else class="overflow-x-auto rounded-lg bg-white shadow mb-6">
                <table class="min-w-full table-auto text-left whitespace-nowrap">
                    <thead class="bg-blue-100 text-gray-700 border-b">
                        <tr>
                            <th class="px-4 py-3 font-semibold">S.L</th>
                            <th class="px-4 py-3 font-semibold">Name</th>
                            <th class="px-4 py-3 font-semibold text-center">Roll No</th>
                            <th class="px-4 py-3 font-semibold text-center">Grand Total</th>
                            <th class="px-4 py-3 font-semibold text-center">Percent (%)</th>
                            <th class="px-4 py-3 font-semibold text-center">Grade</th>
                            <th class="px-4 py-3 font-semibold text-center">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(student, index) in markedList" :key="student.id" class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ String(index + 1).padStart(2, '0') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ student.name }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ student.roll_no }}</td>
                            
                            <!-- Input -->
                            <td class="px-4 py-2 w-48 text-center">
                                <input type="number" 
                                       v-model="studentsToMark[index].grand_total" 
                                       class="border border-blue-300 rounded p-2 text-center w-full focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                       placeholder="Enter Marks" />
                            </td>

                            <!-- Computed Outputs -->
                            <td class="px-4 py-3 text-center text-gray-600 font-semibold">{{ student.grand_total ? student.percent + '%' : '-' }}</td>
                            <td class="px-4 py-3 text-center font-bold" 
                                :class="{'text-green-600': student.grade.includes('A'), 'text-yellow-600': ['B','C'].includes(student.grade), 'text-red-600': ['D','F'].includes(student.grade)}">
                                {{ student.grand_total ? student.grade : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span v-if="student.grand_total" :class="['px-2 py-1 rounded text-xs font-semibold', student.result === 'Pass' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                                    {{ student.result }}
                                </span>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Submit Button -->
            <div v-if="studentsToMark.length > 0" class="flex justify-end mt-4">
                <button @click="submitResults" :disabled="isPublishing" class="bg-indigo-600 text-white font-bold px-8 py-3 rounded-lg shadow hover:bg-indigo-700 flex items-center gap-2 disabled:opacity-50">
                    <span v-if="schedulePublishDate">📅 Schedule & Publish</span>
                    <span v-else>🚀 Publish Now</span>
                </button>
            </div>

        </div>
    </HeadmasterLayout>
</template>

<style scoped>
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
