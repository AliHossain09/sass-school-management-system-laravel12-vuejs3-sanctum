<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { ref, onMounted, watch } from 'vue'
import { useToast } from 'vue-toastification'
import Swal from 'sweetalert2'
import axios from 'axios'

const toast = useToast()

/* --- State --- */
const students = ref<any[]>([])
const meta = ref({
    current_page: 1,
    from: 0,
    to: 0,
    total: 0,
    last_page: 0,
    per_page: 10,
})
const loading = ref(false)
const error = ref('')
const editingStudent = ref<any | null>(null)
const activeForm = ref(false)
const photoPreview = ref<string | null>(null)

const form = ref<any>({
    student_code: '',
    academic_year: '',
    first_name: '',
    last_name: '',
    gender: '',
    dob: '',
    religion: '',
    nationality: '',
    phone: '',
    email: '',
    present_address: '',
    permanent_address: '',
    father_name: '',
    father_phone: '',
    mother_name: '',
    mother_phone: '',
    local_guardian_name: '',
    local_guardian_phone: '',
    local_guardian_relationship: '',
    class_id: '',
    section_id: '',
    shift: '',
    roll_number: '',
    id_card_number: '',
    board_registration_number: '',
    elective_subject_id: '',
    extra_curricular: '',
    description: '',
    username: '',
    password: '',
    photo: null
})

const search = ref('')
let searchTimeout: any = null

const authHeader = () => ({
    Authorization: `Bearer ${localStorage.getItem('token')}`
})

/* --- Load Students --- */
const loadStudents = async (page = 1) => {
    loading.value = true
    try {
        const res = await axios.get('/api/students', {
            headers: authHeader(),
            params: { page, search: search.value }
        })
        students.value = res.data.data
        meta.value = res.data.meta
    } catch (err) {
        console.error('Failed to load students', err)
    } finally {
        loading.value = false
    }
}

onMounted(() => loadStudents())

/* --- Search debounce --- */
watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => loadStudents(1), 400)
})

/* --- Open form --- */
const openForm = () => {
    activeForm.value = true
    editingStudent.value = null
    photoPreview.value = null
    form.value = {
        student_code: '',
        academic_year: '',
        first_name: '',
        last_name: '',
        gender: '',
        dob: '',
        religion: '',
        nationality: '',
        phone: '',
        email: '',
        present_address: '',
        permanent_address: '',
        father_name: '',
        father_phone: '',
        mother_name: '',
        mother_phone: '',
        local_guardian_name: '',
        local_guardian_phone: '',
        local_guardian_relationship: '',
        class_id: '',
        section_id: '',
        shift: '',
        roll_number: '',
        id_card_number: '',
        board_registration_number: '',
        elective_subject_id: '',
        extra_curricular: '',
        description: '',
        username: '',
        password: '',
        photo: null
    }
}

/* --- Photo change --- */
const onPhotoChange = (e: Event) => {
    const target = e.target as HTMLInputElement
    if (target.files && target.files[0]) {
        form.value.photo = target.files[0]

        const reader = new FileReader()
        reader.onload = () => (photoPreview.value = reader.result as string)
        reader.readAsDataURL(target.files[0])
    }
}

/* --- Submit form --- */
const submit = async () => {
    error.value = ''
    if (!form.value.first_name || !form.value.last_name || !form.value.gender || !form.value.class_id) {
        toast.error('First Name, Last Name, Gender, and Class are required.')
        return
    }

    const data = new FormData()
    Object.entries(form.value).forEach(([key, value]) => {
        if (value !== null && value !== '') data.append(key, value)
    })

    try {
        loading.value = true
        if (editingStudent.value) {
            await axios.post(`/api/students/${editingStudent.value.id}`, data, {
                headers: { ...authHeader(), 'Content-Type': 'multipart/form-data' },
                params: { _method: 'PUT' }
            })
            toast.success('Student updated successfully!')
        } else {
            await axios.post('/api/students', data, {
                headers: { ...authHeader(), 'Content-Type': 'multipart/form-data' }
            })
            toast.success('Student created successfully!')
        }
        activeForm.value = false
        loadStudents(meta.value.current_page)
    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Failed to save student')
    } finally {
        loading.value = false
    }
}

/* --- Edit Student --- */
const startEdit = (student: any) => {
    editingStudent.value = student
    activeForm.value = true
    photoPreview.value = student.photo ? `/storage/${student.photo}` : null
    form.value = { ...student, photo: null }
}

/* --- Delete Student --- */
const deleteStudent = async (id: number) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    })
    if (!result.isConfirmed) return

    try {
        loading.value = true
        await axios.delete(`/api/students/${id}`, { headers: authHeader() })
        toast.success('Student deleted successfully!')
        if (students.value.length === 1 && meta.value.current_page > 1) {
            loadStudents(meta.value.current_page - 1)
        } else {
            loadStudents(meta.value.current_page)
        }
    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Failed to delete student')
    } finally {
        loading.value = false
    }
}
</script>

<template>
<HeadmasterLayout>
    <!-- Breadcrumb -->
    <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
        <p class="text-gray-700">Headmaster ></p>
        <p class="text-gray-700">Students ></p>
        <p class="text-gray-700">List</p>
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold mb-1">Student List</h1>
            <input
                v-model="search"
                type="text"
                placeholder="Search by name, email, or phone..."
                class="border-b rounded-md border-blue-400 px-4 py-2 w-64 focus:outline-none"
            />
            <button @click="openForm"
                class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 flex items-center gap-2">
                Create Student
            </button>
        </div>

        <div class="overflow-x-auto rounded-lg bg-white shadow">
            <table class="min-w-full table-auto text-left">
                <thead class="bg-blue-200 text-blue-700">
                    <tr>
                        <th class="px-4 py-2">SL.</th>
                        <th class="px-4 py-2">Photo</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Gender</th>
                        <th class="px-4 py-2">Class</th>
                        <th class="px-4 py-2">Phone</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(student, index) in students" :key="student.id" class="border-b last:border-b-0 hover:bg-gray-50">
                        <td class="px-4 py-3">{{ index + 1 + (meta.current_page - 1) * meta.per_page }}</td>
                        <td class="px-4 py-3">
                            <img v-if="student.photo" :src="`/storage/${student.photo}`" alt="Photo"
                                 class="h-12 w-12 object-cover rounded-full"/>
                            <span v-else class="text-gray-400">N/A</span>
                        </td>
                        <td class="px-4 py-3">{{ student.first_name }} {{ student.last_name }}</td>
                        <td class="px-4 py-3">{{ student.gender }}</td>
                        <td class="px-4 py-3">{{ student.class_id || 'N/A' }}</td>
                        <td class="px-4 py-3">{{ student.phone || 'N/A' }}</td>
                        <td class="px-4 py-3">{{ student.email || 'N/A' }}</td>
                        <td class="px-4 py-3 flex justify-center gap-2">
                            <button @click="startEdit(student)"
                                class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Edit</button>
                            <button @click="deleteStudent(student.id)"
                                class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-4">
            <div class="text-gray-600">Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries</div>
            <div class="flex gap-1">
                <button :disabled="meta.current_page === 1" @click="loadStudents(meta.current_page - 1)"
                        class="px-2 py-1 border rounded disabled:opacity-50">&laquo;</button>
                <button v-for="page in meta.last_page" :key="page" @click="loadStudents(page)"
                        :class="['px-2 py-1 border rounded', page === meta.current_page ? 'bg-blue-600 text-white' : '']">{{ page }}</button>
                <button :disabled="meta.current_page === meta.last_page" @click="loadStudents(meta.current_page + 1)"
                        class="px-2 py-1 border rounded disabled:opacity-50">&raquo;</button>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="activeForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded p-6 w-full max-w-3xl shadow-lg relative overflow-y-auto max-h-[90vh]">
                <button @click="activeForm = false"
                        class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl font-bold">&times;</button>
                <h3 class="text-xl font-semibold mb-4">{{ editingStudent ? 'Edit Student' : 'Create Student' }}</h3>
                <p v-if="error" class="text-red-600 mb-2">{{ error }}</p>

                <form @submit.prevent="submit" class="space-y-4">

                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-100 rounded shadow-sm">
                        <div class="flex flex-col">
                            <label class="mb-1 font-medium text-gray-600">First Name *</label>
                            <input v-model="form.first_name" class="input" />
                        </div>
                        <div class="flex flex-col">
                            <label class="mb-1 font-medium text-gray-600">Last Name *</label>
                            <input v-model="form.last_name" class="input" />
                        </div>
                        <div class="flex flex-col">
                            <label class="mb-1 font-medium text-gray-600">Gender *</label>
                            <select v-model="form.gender" class="input">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label class="mb-1 font-medium text-gray-600">Date of Birth</label>
                            <input type="date" v-model="form.dob" class="input" />
                        </div>
                        <div class="flex flex-col md:col-span-2">
                            <label class="mb-1 font-medium text-gray-600">Photo</label>
                            <input type="file" @change="onPhotoChange" accept="image/*" class="h-40 border-2 border-dashed rounded-lg p-4"/>
                            <img v-if="photoPreview" :src="photoPreview" class="mt-2 h-32 w-32 rounded border"/>
                        </div>
                    </div>

                    <!-- Academic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-100 rounded shadow-sm">
                        <div class="flex flex-col">
                            <label class="mb-1 font-medium text-gray-600">Student Code</label>
                            <input v-model="form.student_code" class="input"/>
                        </div>
                        <div class="flex flex-col">
                            <label class="mb-1 font-medium text-gray-600">Academic Year</label>
                            <input v-model="form.academic_year" class="input"/>
                        </div>
                        <div class="flex flex-col">
                            <label class="mb-1 font-medium text-gray-600">Class</label>
                            <input v-model="form.class_id" class="input"/>
                        </div>
                        <div class="flex flex-col">
                            <label class="mb-1 font-medium text-gray-600">Section</label>
                            <input v-model="form.section_id" class="input"/>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary w-full" :disabled="loading">
                        {{ loading ? 'Saving...' : editingStudent ? 'Update Student' : 'Save Student' }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</HeadmasterLayout>
</template>

<style scoped>
.input {
    border: 1px solid #ccc;
    padding: 8px 12px;
    border-radius: 4px;
    width: 100%;
}
.btn-primary {
    background-color: #2563eb;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    border: none;
}
.btn-primary:disabled {
    background-color: #a5b4fc;
    cursor: not-allowed;
}
</style>
