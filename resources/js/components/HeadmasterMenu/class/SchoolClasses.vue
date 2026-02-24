<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { ref, onMounted, watch } from 'vue'
import { useToast } from 'vue-toastification'
import Swal from 'sweetalert2'
import axios from 'axios'

const toast = useToast()

/* --- State --- */
const classes = ref<any[]>([])
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

const editingClass = ref<any | null>(null)
const activeForm = ref(false)

const form = ref<any>({
    name: '',
    order: null,
    group: '',
    description: ''
})

const groups = ['Science', 'Humanity', 'Business Studies']

const search = ref('')
let searchTimeout: any = null

const authHeader = () => ({
    Authorization: `Bearer ${localStorage.getItem('token')}`
})

/* --- Load Classes --- */
const loadClasses = async (page = 1) => {
    loading.value = true
    try {
        const res = await axios.get('/api/classes', {
            headers: authHeader(),
            params: {
                page,
                search: search.value
            }
        })
        classes.value = res.data.data
        meta.value = res.data.meta
    } catch (err) {
        console.error('Failed to load classes', err)
    } finally {
        loading.value = false
    }
}
onMounted(() => loadClasses())

/* --- Watch search and debounce --- */
watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        loadClasses(1)
    }, 400)
})

/* --- Open Form --- */
const openForm = () => {
    activeForm.value = true
    editingClass.value = null
    form.value = {
        name: '',
        order: null,
        group: '',
        description: ''
    }
}

/* --- Submit Form --- */
const submit = async () => {
    error.value = ''

    if (!form.value.name) {
        toast.error('Class name and group are required.')
        return
    }

    try {
        loading.value = true
        if (editingClass.value) {
            // Update
            await axios.put(`/api/classes/${editingClass.value.id}`, form.value, {
                headers: authHeader()
            })
            toast.success('Class updated successfully!')
        } else {
            // Create
            await axios.post('/api/classes', form.value, {
                headers: authHeader()
            })
            toast.success('Class created successfully!')
        }
        activeForm.value = false
        loadClasses(meta.value.current_page)
    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Failed to save class')
    } finally {
        loading.value = false
    }
}

/* --- Edit Class --- */
const startEdit = (cls: any) => {
    editingClass.value = cls
    activeForm.value = true
    form.value = { ...cls }
}

/* --- Delete Class --- */
const deleteClass = async (id: number) => {
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
        await axios.delete(`/api/classes/${id}`, { headers: authHeader() })
        toast.success('Class deleted successfully!')

        if (classes.value.length === 1 && meta.value.current_page > 1) {
            loadClasses(meta.value.current_page - 1)
        } else {
            loadClasses(meta.value.current_page)
        }
    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Failed to delete class')
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <HeadmasterLayout>
        <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
            <p class="text-gray-700">Headmaster ></p>
            <p class="text-gray-700">Classes ></p>
            <p class="text-gray-700">List</p>
        </div>

        <div class="p-6 shadow-2xl bg-gray-50 rounded">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold mb-1">Class List</h1>
                <input v-model="search" type="text" placeholder="Search by class name..."
                    class="border-b rounded-md border-blue-400 px-4 py-2 w-64 focus:outline-none" />
                <button @click="openForm"
                    class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 flex items-center gap-2">
                    Create Class
                </button>
            </div>

            <div class="overflow-x-auto rounded-lg bg-white shadow">
                <table class="min-w-full table-auto text-left">
                    <thead class="bg-blue-200 text-blue-700">
                        <tr>
                            <th class="px-4 py-2">SL.</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Order</th>
                            <th class="px-4 py-2">Group</th>
                            <th class="px-4 py-2">Description</th>
                            <th class="px-4 py-2 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(cls, index) in classes" :key="cls.id" :class="[
                            index % 2 === 0 ? 'bg-white' : 'bg-gray-200',  // Alternate color
                            'last:border-b-0',
                            'hover:bg-gray-50'
                        ]">
                            <td class="px-4 py-3">{{ index + 1 + (meta.current_page - 1) * meta.per_page }}</td>
                            <td class="px-4 py-3">{{ cls.name }}</td>
                            <td class="px-4 py-3">{{ cls.order || 'N/A' }}</td>
                            <td class="px-4 py-3">{{ cls.group }}</td>
                            <td class="px-4 py-3">{{ cls.description || 'N/A' }}</td>
                            <td class="px-4 py-3 flex justify-center gap-2">
                                <button @click="startEdit(cls)"
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Edit</button>
                                <button @click="deleteClass(cls.id)"
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
                    <button :disabled="meta.current_page === 1" @click="loadClasses(meta.current_page - 1)"
                        class="px-2 py-1 border rounded disabled:opacity-50">&laquo;</button>
                    <button v-for="page in meta.last_page" :key="page" @click="loadClasses(page)"
                        :class="['px-2 py-1 border rounded', page === meta.current_page ? 'bg-blue-600 text-white' : '']">{{
                        page }}</button>
                    <button :disabled="meta.current_page === meta.last_page" @click="loadClasses(meta.current_page + 1)"
                        class="px-2 py-1 border rounded disabled:opacity-50">&raquo;</button>
                </div>
            </div>

            <!-- Create/Edit Modal -->
            <div v-if="activeForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                <div class="bg-white rounded p-6 w-full max-w-2xl shadow-lg relative overflow-y-auto max-h-[90vh]">
                    <button @click="activeForm = false"
                        class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl font-bold">&times;</button>
                    <h3 class="text-xl font-semibold mb-4">{{ editingClass ? 'Edit Class' : 'Create Class' }}</h3>
                    <p v-if="error" class="text-red-600 mb-2">{{ error }}</p>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block mb-1">First Name *</label>
                                <input v-model="form.name" placeholder="Class Name *" class="input" />
                            </div>

                            <div>
                                <label for="group" class="block mb-1">Group</label>
                                <select v-model="form.group" class="input">
                                    <option value="" disabled>Select Group</option>
                                    <option v-for="grp in groups" :key="grp" :value="grp">{{ grp }}</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label for="order" class="block mb-1">Order</label>
                                <input type="number" v-model="form.order" placeholder="Order" class="input" />
                            </div>

                            <div class="md:col-span-2">
                                <label for="description" class="block mb-1">Description</label>
                                <textarea v-model="form.description" placeholder="Description" class="input"></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                            {{ loading ? 'Saving...' : editingClass ? 'Update Class' : 'Save Class' }}
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
