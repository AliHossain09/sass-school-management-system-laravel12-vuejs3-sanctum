<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { ref, onMounted, watch, onUnmounted } from 'vue'
import Swal from 'sweetalert2'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

/* ================= STATE ================= */
const notices = ref<any[]>([])
const classes = ref<any[]>([])
const sections = ref<any[]>([])
const loading = ref(false)
const activeForm = ref(false)
const editingNotice = ref<any | null>(null)

const meta = ref({
    current_page: 1,
    from: 0,
    to: 0,
    total: 0,
    last_page: 0,
    per_page: 10,
})

const form = ref<{
    title: string
    type: string
    publish_date: string
    class_ids: number[]
    section_ids: number[]
    description: string
}>({
    title: '',
    type: 'all',
    publish_date: '',
    class_ids: [],
    section_ids: [],
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
        title: '',
        type: 'all',
        publish_date: '',
        class_ids: [],
        section_ids: [],
        description: ''
    }
    editingNotice.value = null
}

const closeForm = () => {
    activeForm.value = false
    resetForm()
}

/* ================= LOAD DATA ================= */
const loadNotices = async (page = 1) => {
    try {
        loading.value = true
        const res = await axios.get('/api/notices', {
            headers: authHeader(),
            params: { page, search: search.value }
        })
        notices.value = res.data.data
        meta.value = {
            current_page: res.data.current_page,
            from: res.data.from,
            to: res.data.to,
            total: res.data.total,
            last_page: res.data.last_page,
            per_page: res.data.per_page,
        }
    } catch (err:any) {
        toast.error(err.response?.data?.message || 'Failed to load notices')
    } finally {
        loading.value = false
    }
}

const loadClasses = async () => {
    try {
        const res = await axios.get('/api/classes', { headers: authHeader() })
        classes.value = res.data.data
    } catch {
        toast.error('Failed to load classes')
    }
}

const loadSections = async () => {
    try {
        const res = await axios.get('/api/sections', { headers: authHeader() })
        sections.value = res.data.data
    } catch {
        toast.error('Failed to load sections')
    }
}

/* ================= LIFECYCLE ================= */
onMounted(() => {
    loadNotices()
    loadClasses()
    loadSections()
})

watch(search, () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => loadNotices(1), 400)
})

onUnmounted(() => clearTimeout(timeout))

/* ================= FORM ================= */
const openForm = (notice: any = null) => {
    if (notice) {
        editingNotice.value = notice
        form.value = {
            title: notice.title,
            type: notice.type,
            publish_date: notice.publish_date,
            class_ids: notice.class_ids || [],
            section_ids: notice.section_ids || [],
            description: notice.description || ''
        }
    } else {
        resetForm()
    }
    activeForm.value = true
}

const submit = async () => {
    if (!form.value.title || !form.value.publish_date) {
        toast.error('Title and publish date are required')
        return
    }

    try {
        loading.value = true

        if (editingNotice.value) {
            await axios.put(`/api/notices/${editingNotice.value.id}`, form.value, { headers: authHeader() })
            toast.success('Notice updated successfully')
        } else {
            await axios.post('/api/notices', form.value, { headers: authHeader() })
            toast.success('Notice created successfully')
        }

        activeForm.value = false
        loadNotices(meta.value.current_page)
    } catch (err:any) {
        toast.error(err.response?.data?.message || 'Failed to save notice')
    } finally {
        loading.value = false
    }
}

/* ================= ACTIONS ================= */
const deleteNotice = async (id: number) => {
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
        await axios.delete(`/api/notices/${id}`, { headers: authHeader() })
        toast.success('Notice deleted successfully')

        if (notices.value.length === 1 && meta.value.current_page > 1) {
            loadNotices(meta.value.current_page - 1)
        } else {
            loadNotices(meta.value.current_page)
        }
    } catch (err:any) {
        toast.error(err.response?.data?.message || 'Failed to delete notice')
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
            <p class="text-gray-700">Notice ></p>
            <p class="text-gray-700">List</p>
        </div>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Notice List</h1>
        <input v-model="search" type="text" placeholder="Search by title..."
            class="border-b rounded-md border-blue-400 px-4 py-2 w-64 focus:outline-none" />
        <button @click="openForm()" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">
            Create Notice
        </button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg bg-white shadow">
        <table class="min-w-full table-auto text-left">
            <thead class="bg-blue-200 text-blue-700">
                <tr>
                    <th class="px-4 py-2">SL</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2">Publish Date</th>
                    <th class="px-4 py-2">Classes</th>
                    <th class="px-4 py-2">Sections</th>
                    <th class="px-4 py-2 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(n, index) in notices" :key="n.id" class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3">{{ index + 1 + (meta.current_page -1) * meta.per_page }}</td>
                    <td class="px-4 py-3">{{ n.title }}</td>
                    <td class="px-4 py-3">{{ n.type }}</td>
                    <td class="px-4 py-3">{{ n.publish_date }}</td>
                    <td class="px-4 py-3">{{ n.class_ids?.map(id => classes.find(c => c.id === id)?.name).join(', ') }}</td>
                    <td class="px-4 py-3">{{ n.section_ids?.map(id => sections.find(s => s.id === id)?.name).join(', ') || 'N/A' }}</td>
                    <td class="px-4 py-3 flex justify-center gap-2">
                        <button @click="openForm(n)" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                            Edit
                        </button>
                        <button @click="deleteNotice(n.id)" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
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
            <button :disabled="meta.current_page === 1" @click="loadNotices(meta.current_page - 1)" class="px-2 py-1 border rounded disabled:opacity-50">&laquo;</button>
            <button v-for="page in meta.last_page" :key="page" @click="loadNotices(page)"
                :class="['px-2 py-1 border rounded', page===meta.current_page?'bg-blue-600 text-white':'']">
                {{ page }}
            </button>
            <button :disabled="meta.current_page === meta.last_page" @click="loadNotices(meta.current_page + 1)" class="px-2 py-1 border rounded disabled:opacity-50">&raquo;</button>
        </div>
    </div>

    <!-- Modal -->
    <div v-if="activeForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded p-6 w-full max-w-2xl shadow-lg relative">
            <button @click="closeForm" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl font-bold">&times;</button>

            <h3 class="text-xl font-semibold mb-4">{{ editingNotice ? 'Edit Notice' : 'Create Notice' }}</h3>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1">Title *</label>
                        <input v-model="form.title" class="input" />
                    </div>
                    <div>
                        <label class="block mb-1">Type *</label>
                        <select v-model="form.type" class="input">
                            <option value="all">All Classes</option>
                            <option value="class">Specific Classes</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1">Publish Date *</label>
                        <input type="date" v-model="form.publish_date" class="input" />
                    </div>
                    <div v-if="form.type==='class'">
                        <label class="block mb-1">Classes *</label>
                        <select v-model="form.class_ids" multiple class="input">
                            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div v-if="form.type==='class'">
                        <label class="block mb-1">Sections</label>
                        <select v-model="form.section_ids" multiple class="input">
                            <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block mb-1">Description</label>
                        <textarea v-model="form.description" class="input"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                    {{ loading ? 'Saving...' : editingNotice ? 'Update Notice' : 'Save Notice' }}
                </button>
            </form>
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
