<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { ref, onMounted, watch } from 'vue'
import { useToast } from 'vue-toastification'
import Swal from 'sweetalert2'
import axios from 'axios'

const toast = useToast()

// --- State ---
const subjects = ref<any[]>([])
const classes = ref<any[]>([])
const teachers = ref<any[]>([])
const meta = ref({ current_page: 1, from: 0, to: 0, total: 0, last_page: 0, per_page: 10 })
const loading = ref(false)
const activeForm = ref(false)
const editingSubject = ref<any | null>(null)

const form = ref({
    class_id: '',
    teacher_id: '',
    name: '',
    code: '',
    type: 'core',
})

const search = ref('')
let searchTimeout: any = null

const authHeader = () => ({
    Authorization: `Bearer ${localStorage.getItem('token')}`
})

// --- Load Subjects ---
const loadSubjects = async (page = 1) => {
    loading.value = true
    try {
        const res = await axios.get('/api/subjects', {
            headers: authHeader(),
            params: { page, search: search.value }
        })
        subjects.value = res.data.data
        meta.value = res.data.meta
    } catch (err) {
        console.error(err)
    } finally {
        loading.value = false
    }
}

// --- Load Classes ---
const loadClasses = async () => {
    try {
        const res = await axios.get('/api/classes', { headers: authHeader() })
        classes.value = res.data.data
    } catch (err) {
        console.error(err)
    }
}

// --- Load Teachers ---
const loadTeachers = async () => {
    try {
        const res = await axios.get('/api/teachers', { headers: authHeader() })
        teachers.value = res.data.data
    } catch (err) {
        console.error(err)
    }
}

// --- Watch search ---
watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => loadSubjects(1), 400)
})

// --- Lifecycle ---
onMounted(() => {
    loadSubjects()
    loadClasses()
    loadTeachers()
})

// --- Open Form ---
const openForm = () => {
    activeForm.value = true
    editingSubject.value = null
    form.value = {
        class_id: '',
        teacher_id: '',
        name: '',
        code: '',
        type: 'core',
    }
}

// --- Submit Form ---
const submit = async () => {
    if (!form.value.name || !form.value.class_id) {
        toast.error('Class and Subject name required')
        return
    }
    loading.value = true
    try {
        if (editingSubject.value) {
            await axios.put(`/api/subjects/${editingSubject.value.id}`, form.value, { headers: authHeader() })
            toast.success('Subject updated')
        } else {
            await axios.post('/api/subjects', form.value, { headers: authHeader() })
            toast.success('Subject created')
        }
        activeForm.value = false
        loadSubjects(meta.value.current_page)
    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Failed to save subject')
    } finally {
        loading.value = false
    }
}

// --- Edit Subject ---
const startEdit = (subject: any) => {
    editingSubject.value = subject
    activeForm.value = true
    form.value = {
        class_id: subject.class_id,
        teacher_id: subject.teacher_id ?? '',
        name: subject.name,
        code: subject.code,
        type: subject.type,
    }
}

// --- Delete Subject ---
const deleteSubject = async (id: number) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!'
    })
    if (!result.isConfirmed) return
    try {
        loading.value = true
        await axios.delete(`/api/subjects/${id}`, { headers: authHeader() })
        toast.success('Subject deleted')
        loadSubjects(meta.value.current_page)
    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Failed to delete')
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <HeadmasterLayout>
        <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
            <p class="text-gray-700">Headmaster ></p>
            <p class="text-gray-700">Subjects ></p>
            <p class="text-gray-700">List</p>
        </div>

        <div class="p-6 shadow-2xl bg-gray-50 rounded">

            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold mb-1">Subjects</h1>
                <input v-model="search" type="text" placeholder="Search by subject name..."
                    class="border-b rounded-md border-blue-400 px-4 py-2 w-64 focus:outline-none" />
                <button @click="openForm"
                    class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 flex items-center gap-2">
                    Create Subject
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg bg-white shadow">
                <table class="min-w-full table-auto text-left">
                    <thead class="bg-blue-200 text-blue-700">
                        <tr>
                            <th class="px-4 py-2">SL.</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Code</th>
                            <th class="px-4 py-2">Type</th>
                            <th class="px-4 py-2">Class</th>
                            <th class="px-4 py-2">Teacher</th>
                            <th class="px-4 py-2 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(subject, index) in subjects" :key="subject.id" :class="[
                            index % 2 === 0 ? 'bg-white' : 'bg-gray-200', // Alternate row color
                            'last:border-b-0',
                            'hover:bg-gray-50'
                        ]">
                            <td class="px-4 py-3">{{ index + 1 + (meta.current_page - 1) * meta.per_page }}</td>
                            <td class="px-4 py-3">{{ subject.name }}</td>
                            <td class="px-4 py-3">{{ subject.code || 'N/A' }}</td>
                            <td class="px-4 py-3">{{ subject.type }}</td>
                            <td class="px-4 py-3">{{ subject.school_class?.name || 'N/A' }}</td>
                            <td class="px-4 py-3 text-black">
                                {{ subject.teacher?.first_name ? subject.teacher.first_name + ' ' +
                                subject.teacher.last_name : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 flex justify-center gap-2">
                                <button @click="startEdit(subject)"
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Edit</button>
                                <button @click="deleteSubject(subject.id)"
                                    class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">Delete</button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-4">
                <div class="text-gray-600">Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries</div>
                <div class="flex gap-1">
                    <button :disabled="meta.current_page === 1" @click="loadSubjects(meta.current_page - 1)"
                        class="px-2 py-1 border rounded disabled:opacity-50">&laquo;</button>
                    <button v-for="page in meta.last_page" :key="page" @click="loadSubjects(page)"
                        :class="['px-2 py-1 border rounded', page === meta.current_page ? 'bg-blue-600 text-white' : '']">{{
                            page }}</button>
                    <button :disabled="meta.current_page === meta.last_page"
                        @click="loadSubjects(meta.current_page + 1)"
                        class="px-2 py-1 border rounded disabled:opacity-50">&raquo;</button>
                </div>
            </div>

            <!-- Create/Edit Modal -->
            <div v-if="activeForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                <div class="bg-white rounded p-6 w-full max-w-2xl shadow-lg relative overflow-y-auto max-h-[90vh]">
                    <button @click="activeForm = false"
                        class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl font-bold">&times;</button>
                    <h3 class="text-xl font-semibold mb-4">{{ editingSubject ? 'Edit Subject' : 'Create Subject' }}</h3>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1">Class *</label>
                                <select v-model="form.class_id" class="input text-black">
                                    <option value="">Select Class</option>
                                    <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block mb-1">Teacher</label>
                                <select v-model="form.teacher_id" class="input text-black">
                                    <option value="">Select Teacher</option>
                                    <option v-for="t in teachers" :key="t.id" :value="t.id">
                                        {{ t.first_name }} {{ t.last_name }}
                                    </option>
                                </select>

                            </div>

                            <div>
                                <label class="block mb-1">Name *</label>
                                <input v-model="form.name" placeholder="Subject Name" class="input text-black" />
                            </div>

                            <div>
                                <label class="block mb-1">Code</label>
                                <input v-model="form.code" placeholder="Subject Code" class="input text-black" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block mb-1">Type</label>
                                <select v-model="form.type" class="input text-black">
                                    <option value="core">Core</option>
                                    <option value="elective">Elective</option>
                                    <option value="optional">Optional</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                            {{ loading ? 'Saving...' : editingSubject ? 'Update Subject' : 'Save Subject' }}
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
    color: #000;
}

.btn-primary {
    background-color: #2563eb;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}

.btn-primary:disabled {
    background-color: #a5b4fc;
    cursor: not-allowed;
}
</style>
