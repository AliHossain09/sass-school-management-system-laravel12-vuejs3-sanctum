<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { ref, onMounted, watch, onUnmounted } from 'vue'
import Swal from 'sweetalert2'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

/* ================= STATE ================= */
const sections = ref<any[]>([])
const classes = ref<any[]>([])
const loading = ref(false)
const activeForm = ref(false)
const editingSection = ref<any | null>(null)

const meta = ref({
    current_page: 1,
    from: 0,
    to: 0,
    total: 0,
    last_page: 0,
    per_page: 10,
})

const form = ref<{
    name: string
    class_id: number | null
    description: string
}>({
    name: '',
    class_id: null,
    description: ''
})

const search = ref('')
let timeout: any = null

/* ================= AUTH ================= */
const authHeader = () => {
    const token = localStorage.getItem('token')
    return token ? { Authorization: `Bearer ${token}` } : {}
}

/* ================= HELPERS ================= */
const resetForm = () => {
    form.value = {
        name: '',
        class_id: null,
        description: ''
    }
    editingSection.value = null
}

const closeForm = () => {
    activeForm.value = false
    resetForm()
}

/* ================= LOAD DATA ================= */
const loadSections = async (page = 1) => {
    try {
        loading.value = true
        const res = await axios.get('/api/sections', {
            headers: authHeader(),
            params: {
                page,
                search: search.value
            }
        })
        sections.value = res.data.data
        meta.value = res.data.meta
    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Failed to load sections')
    } finally {
        loading.value = false
    }
}

const loadClasses = async () => {
    try {
        const res = await axios.get('/api/classes', {
            headers: authHeader()
        })
        classes.value = res.data.data
    } catch {
        toast.error('Failed to load classes')
    }
}

/* ================= LIFECYCLE ================= */
onMounted(() => {
    loadSections()
    loadClasses()
})

watch(search, () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => loadSections(1), 400)
})

onUnmounted(() => clearTimeout(timeout))

/* ================= FORM ================= */
const openForm = () => {
    activeForm.value = true
    resetForm()
}

const submit = async () => {
    if (!form.value.name || !form.value.class_id) {
        toast.error('Section name and class are required')
        return
    }

    try {
        loading.value = true

        if (editingSection.value) {
            await axios.put(
                `/api/sections/${editingSection.value.id}`,
                form.value,
                { headers: authHeader() }
            )
            toast.success('Section updated successfully')
        } else {
            await axios.post(
                '/api/sections',
                form.value,
                { headers: authHeader() }
            )
            toast.success('Section created successfully')
        }

        activeForm.value = false
        loadSections(meta.value.current_page)

    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Failed to save section')
    } finally {
        loading.value = false
    }
}

/* ================= ACTIONS ================= */
const editSection = (s: any) => {
    editingSection.value = s
    activeForm.value = true
    form.value = {
        name: s.name,
        class_id: Number(s.class_id),
        description: s.description || ''
    }
}

const deleteSection = async (id: number) => {
    const ok = await Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    })

    if (!ok.isConfirmed) return

    try {
        loading.value = true
        await axios.delete(`/api/sections/${id}`, {
            headers: authHeader()
        })
        toast.success('Section deleted successfully')

        if (sections.value.length === 1 && meta.value.current_page > 1) {
            loadSections(meta.value.current_page - 1)
        } else {
            loadSections(meta.value.current_page)
        }
    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Failed to delete section')
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
            <p class="text-gray-700">Sections ></p>
            <p class="text-gray-700">List</p>
        </div>

        <div class="p-6 shadow-2xl bg-gray-50 rounded">

            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Section List</h1>

                <input v-model="search" type="text" placeholder="Search by section name..."
                    class="border-b rounded-md border-blue-400 px-4 py-2 w-64 focus:outline-none" />

                <button @click="openForm" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">
                    Create Section
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg bg-white shadow">
                <table class="min-w-full table-auto text-left">
                    <thead class="bg-blue-200 text-blue-700">
                        <tr>
                            <th class="px-4 py-2">SL</th>
                            <th class="px-4 py-2">Section Name</th>
                            <th class="px-4 py-2">Class</th>
                            <th class="px-4 py-2">Description</th>
                            <th class="px-4 py-2 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(s, index) in sections" :key="s.id" :class="[
                            index % 2 === 0 ? 'bg-white' : 'bg-gray-200', // Alternate color
                            'last:border-b-0',
                            'hover:bg-gray-50'
                        ]">
                            <td class="px-4 py-3">
                                {{ index + 1 + (meta.current_page - 1) * meta.per_page }}
                            </td>
                            <td class="px-4 py-3">{{ s.name }}</td>
                            <td class="px-4 py-3">{{ s.school_class?.name }}</td>
                            <td class="px-4 py-3">{{ s.description || 'N/A' }}</td>
                            <td class="px-4 py-3 flex justify-center gap-2">
                                <button @click="editSection(s)"
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                    Edit
                                </button>
                                <button @click="deleteSection(s.id)"
                                    class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                    Delete
                                </button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-4">
                <div class="text-gray-600">
                    Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries
                </div>

                <div class="flex gap-1">
                    <button :disabled="meta.current_page === 1" @click="loadSections(meta.current_page - 1)"
                        class="px-2 py-1 border rounded disabled:opacity-50">
                        &laquo;
                    </button>

                    <button v-for="page in meta.last_page" :key="page" @click="loadSections(page)" :class="[
                        'px-2 py-1 border rounded',
                        page === meta.current_page ? 'bg-blue-600 text-white' : ''
                    ]">
                        {{ page }}
                    </button>

                    <button :disabled="meta.current_page === meta.last_page"
                        @click="loadSections(meta.current_page + 1)"
                        class="px-2 py-1 border rounded disabled:opacity-50">
                        &raquo;
                    </button>
                </div>
            </div>

            <!-- Modal -->
            <div v-if="activeForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                <div class="bg-white rounded p-6 w-full max-w-2xl shadow-lg relative">
                    <button @click="closeForm"
                        class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl font-bold">
                        &times;
                    </button>

                    <h3 class="text-xl font-semibold mb-4">
                        {{ editingSection ? 'Edit Section' : 'Create Section' }}
                    </h3>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1">Section Name *</label>
                                <input v-model="form.name" class="input" />
                            </div>

                            <div>
                                <label class="block mb-1">Class *</label>
                                <select v-model="form.class_id" class="input">
                                    <option value="" disabled>Select Class</option>
                                    <option v-for="c in classes" :key="c.id" :value="c.id">
                                        {{ c.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block mb-1">Description</label>
                                <textarea v-model="form.description" class="input"></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                            {{ loading ? 'Saving...' : editingSection ? 'Update Section' : 'Save Section' }}
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
    background: #2563eb;
    color: #fff;
    padding: 10px;
    border-radius: 6px;
}
</style>
