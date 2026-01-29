<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { ref, onMounted, watch } from 'vue'
import { useToast } from 'vue-toastification'
import Swal from 'sweetalert2'
import axios from 'axios'

const toast = useToast()

/* --- State --- */
const teachers = ref<any[]>([])
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
const editingTeacher = ref<any | null>(null)
const activeForm = ref(false)
const form = ref<any>({
    first_name: '',
    last_name: '',
    gender: '',
    dob: '',
    subjects: [],
    class_assigned: '',
    joining_date: '',
    grade: '',
    employment_type: 'full-time',
    department: '',
    phone: '',
    email: '',
    address: '',
    emergency_contact: '',
    qualification: '',
    experience: '',
    salary: '',
    photo: null
})

const subjectsInput = ref('')

const search = ref('')
let searchTimeout: any = null

const authHeader = () => ({
    Authorization: `Bearer ${localStorage.getItem('token')}`
})

/* --- Load Teachers --- */
const loadTeachers = async (page = 1) => {
    loading.value = true
    try {
        const res = await axios.get('/api/teachers', {
            headers: authHeader(),
            params: {
                page,
                search: search.value,
            }
        })
        teachers.value = res.data.data
        meta.value = res.data.meta
    } catch (err) {
        console.error('Failed to load teachers', err)
    } finally {
        loading.value = false
    }
}
onMounted(() => loadTeachers())

/* --- Watch search and debounce --- */
watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        loadTeachers(1)
    }, 400)
})

/* --- Open Create Form --- */
const openForm = () => {
    activeForm.value = true
    editingTeacher.value = null
    subjectsInput.value = ''
    form.value = {
        first_name: '',
        last_name: '',
        gender: '',
        dob: '',
        subjects: [],
        class_assigned: '',
        joining_date: '',
        grade: '',
        employment_type: 'full-time',
        department: '',
        phone: '',
        email: '',
        address: '',
        emergency_contact: '',
        qualification: '',
        experience: '',
        salary: '',
        photo: null
    }
}

/* --- Handle file change --- */
const onPhotoChange = (e: Event) => {
    const target = e.target as HTMLInputElement
    if (target.files && target.files[0]) {
        form.value.photo = target.files[0]
    }
}

/* --- Submit form --- */
const submit = async () => {
    error.value = ''
    if (!form.value.first_name || !form.value.last_name || !form.value.gender) {
        toast.error('First name, last name, and gender are required.')
        return
    }

    const data = new FormData()
    Object.keys(form.value).forEach((key) => {
        if (key === 'subjects') {
            if (subjectsInput.value.trim()) {
                const arr = subjectsInput.value.split(',').map(s => s.trim())
                data.append('subjects', JSON.stringify(arr))
            }
        } else if (form.value[key] !== null) {
            data.append(key, form.value[key])
        }
    })

    try {
        loading.value = true
        if (editingTeacher.value) {
            await axios.post(`/api/teachers/${editingTeacher.value.id}`, data, {
    headers: { ...authHeader(), 'Content-Type': 'multipart/form-data' },
    params: { _method: 'PUT' }
})

            toast.success('Teacher updated successfully!')
        } else {
            await axios.post('/api/teachers', data, {
                headers: {
                    ...authHeader(),
                    'Content-Type': 'multipart/form-data'
                }
            })
            toast.success('Teacher created successfully!')
        }
        activeForm.value = false
        loadTeachers(meta.value.current_page)
    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Failed to save teacher')
    } finally {
        loading.value = false
    }
}

/* --- Edit Teacher --- */
const startEdit = (teacher: any) => {
    editingTeacher.value = teacher
    activeForm.value = true
    subjectsInput.value = teacher.subjects ? teacher.subjects.join(', ') : ''
    form.value = { ...teacher, photo: null }
}

/* --- Delete Teacher --- */
const deleteTeacher = async (id: number) => {
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
        await axios.delete(`/api/teachers/${id}`, { headers: authHeader() })
        toast.success('Teacher deleted successfully!')

        if (teachers.value.length === 1 && meta.value.current_page > 1) {
            loadTeachers(meta.value.current_page - 1)
        } else {
            loadTeachers(meta.value.current_page)
        }
    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Failed to delete teacher')
    } finally {
        loading.value = false
    }
}
</script>


<template>
    <HeadmasterLayout>
        <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
            <p class="text-gray-700">Headmaster ></p>
            <p class="text-gray-700">Teachers ></p>
            <p class="text-gray-700">List</p>
        </div>

        <div class="p-6 shadow-2xl bg-gray-50 rounded">

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold mb-1">Teacher List</h1>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by name, email or phone..."
                    class="border-b rounded-md border-blue-400 px-4 py-2 w-64 focus:outline-none"
                />
                <button @click="openForm"
                    class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 flex items-center gap-2">
                    Create Teacher
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
                            <th class="px-4 py-2">Department</th>
                            <th class="px-4 py-2">Phone</th>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(teacher, index) in teachers" :key="teacher.id"
                            class="border-b last:border-b-0 hover:bg-gray-50">
                            <td class="px-4 py-3">{{ index + 1 + (meta.current_page - 1) * meta.per_page }}</td>

                            <td class="px-4 py-3">
                                <img v-if="teacher.photo" :src="`/storage/${teacher.photo}`" alt="Teacher Photo"
                                    class="h-12 w-12 object-cover rounded-full" />
                                <span v-else class="text-gray-400">N/A</span>
                            </td>

                            <td class="px-4 py-3">{{ teacher.first_name }} {{ teacher.last_name }}</td>
                            <td class="px-4 py-3">{{ teacher.gender }}</td>
                            <td class="px-4 py-3">{{ teacher.class_assigned || 'N/A' }}</td>
                            <td class="px-4 py-3">{{ teacher.department || 'N/A' }}</td>
                            <td class="px-4 py-3">{{ teacher.phone || 'N/A' }}</td>
                            <td class="px-4 py-3">{{ teacher.email || 'N/A' }}</td>
                            <td class="px-4 py-3 flex justify-center gap-2">
                                <button @click="startEdit(teacher)"
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Edit</button>
                                <button @click="deleteTeacher(teacher.id)"
                                    class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">Delete</button>
                            </td>
                        </tr>
                    </tbody>

                </table>
            </div>

            <div class="flex justify-between items-center mt-4">
                <div class="text-gray-600">Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries</div>
                <div class="flex gap-1">
                    <button :disabled="meta.current_page === 1" @click="loadTeachers(meta.current_page - 1)"
                        class="px-2 py-1 border rounded disabled:opacity-50">&laquo;</button>
                    <button v-for="page in meta.last_page" :key="page" @click="loadTeachers(page)"
                        :class="['px-2 py-1 border rounded', page === meta.current_page ? 'bg-blue-600 text-white' : '']">{{
                        page }}</button>
                    <button :disabled="meta.current_page === meta.last_page" @click="loadTeachers(meta.current_page + 1)"
                        class="px-2 py-1 border rounded disabled:opacity-50">&raquo;</button>
                </div>
            </div>

            <!-- Create/Edit Modal -->
            <div v-if="activeForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                <div class="bg-white rounded p-6 w-full max-w-3xl shadow-lg relative overflow-y-auto max-h-[90vh]">
                    <button @click="activeForm = false"
                        class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl font-bold">&times;</button>
                    <h3 class="text-xl font-semibold mb-4">{{ editingTeacher ? 'Edit Teacher' : 'Create Teacher' }}</h3>
                    <p v-if="error" class="text-red-600 mb-2">{{ error }}</p>

                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Basic -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-100 rounded shadow-sm">
                            <input v-model="form.first_name" placeholder="First Name *" class="input" />
                            <input v-model="form.last_name" placeholder="Last Name *" class="input" />
                            <select v-model="form.gender" class="input">
                                <option value="">Select Gender *</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <input type="date" v-model="form.dob" class="input" placeholder="DOB" />
                            <input type="file" @change="onPhotoChange" accept="image/*"
                                class="col-span-2 h-40 border-2 border-dashed rounded-lg p-4" />
                        </div>

                        <!-- Professional -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-100 rounded shadow-sm">
                            <input v-model="subjectsInput" placeholder="Subjects (comma separated)" class="input" />
                            <input v-model="form.class_assigned" placeholder="Class Assigned" class="input" />
                            <input type="date" v-model="form.joining_date" placeholder="Joining Date" class="input" />
                            <input v-model="form.grade" placeholder="Grade" class="input" />
                            <select v-model="form.employment_type" class="input">
                                <option value="full-time">Full-time</option>
                                <option value="part-time">Part-time</option>
                            </select>
                            <input v-model="form.department" placeholder="Department" class="input" />
                        </div>

                        <!-- Contact -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-100 rounded shadow-sm">
                            <input v-model="form.phone" placeholder="Phone" class="input" />
                            <input v-model="form.email" placeholder="Email" class="input" />
                            <textarea v-model="form.address" placeholder="Address"
                                class="input md:col-span-2"></textarea>
                        </div>

                        <!-- Other -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-100 rounded shadow-sm">
                            <input v-model="form.emergency_contact" placeholder="Emergency Contact" class="input" />
                            <input v-model="form.qualification" placeholder="Qualification" class="input" />
                            <input v-model="form.experience" type="number" placeholder="Experience (years)"
                                class="input" />
                            <input v-model="form.salary" type="number" step="0.01" placeholder="Salary" class="input" />
                        </div>

                        <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                            {{ loading ? 'Saving...' : editingTeacher ? 'Update Teacher' : 'Save Teacher' }}
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
