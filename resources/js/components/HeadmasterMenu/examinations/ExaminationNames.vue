<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useToast } from 'vue-toastification'

const toast = useToast()

const rows = ref<any[]>([])
const meta = ref({ current_page: 1, from: 0, to: 0, total: 0, last_page: 0, per_page: 10 })
const loading = ref(false)
const search = ref('')
let searchTimeout: any = null

const activeForm = ref(false)
const editing = ref<any | null>(null)

const form = ref({
  name: '',
  start_date: '',
  end_date: '',
  is_active: true,
})

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
})

const load = async (page = 1) => {
  loading.value = true
  try {
    const res = await axios.get('/api/examinations', {
      headers: authHeader(),
      params: { page, search: search.value },
    })
    rows.value = res.data.data
    meta.value = res.data.meta
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => load(1), 400)
})

onMounted(() => {
  load()
})

const openForm = () => {
  editing.value = null
  form.value = { name: '', start_date: '', end_date: '', is_active: true }
  activeForm.value = true
}

const startEdit = (row: any) => {
  editing.value = row
  form.value = {
    name: row.name,
    start_date: String(row.start_date || '').slice(0, 10),
    end_date: String(row.end_date || '').slice(0, 10),
    is_active: !!row.is_active,
  }
  activeForm.value = true
}

const submit = async () => {
  if (!form.value.name) return toast.error('Exam name is required')
  if (!form.value.start_date) return toast.error('Starting date is required')
  if (!form.value.end_date) return toast.error('Ending date is required')

  loading.value = true
  try {
    if (editing.value) {
      await axios.put(`/api/examinations/${editing.value.id}`, form.value, { headers: authHeader() })
      toast.success('Examination updated')
    } else {
      await axios.post('/api/examinations', form.value, { headers: authHeader() })
      toast.success('Examination created')
    }
    activeForm.value = false
    await load(meta.value.current_page)
  } catch (err: any) {
    const firstError = err.response?.data?.errors ? Object.values(err.response.data.errors)[0]?.[0] : null
    toast.error(firstError || err.response?.data?.message || 'Failed to save')
  } finally {
    loading.value = false
  }
}

const removeRow = async (id: number) => {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: 'This action cannot be undone!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
  })
  if (!result.isConfirmed) return

  loading.value = true
  try {
    await axios.delete(`/api/examinations/${id}`, { headers: authHeader() })
    toast.success('Examination deleted')
    await load(meta.value.current_page)
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
      <p class="text-gray-700">Examination ></p>
      <p class="text-gray-700">Examination Name</p>
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">
      <div class="flex justify-between items-center mb-6 gap-3">
        <h1 class="text-2xl font-bold mb-1">Examination Name</h1>

        <input
          v-model="search"
          type="text"
          placeholder="Search exam..."
          class="border-b rounded-md border-blue-400 px-4 py-2 w-64 focus:outline-none"
        />

        <button @click="openForm" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">
          + Add Exam
        </button>
      </div>

      <div v-if="loading" class="text-center text-gray-600">Loading...</div>

      <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-200 bg-white rounded-md">
          <thead class="bg-blue-900 text-white">
            <tr>
              <th class="border border-gray-200 px-4 py-2 text-left">SL</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Exam Name</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Starting Date</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Ending Date</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Status</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, i) in rows" :key="row.id" class="hover:bg-gray-50">
              <td class="border border-gray-200 px-4 py-2">{{ (meta.from || 1) + i }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ row.name }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ String(row.start_date).slice(0, 10) }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ String(row.end_date).slice(0, 10) }}</td>
              <td class="border border-gray-200 px-4 py-2">
                <span
                  :class="row.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700'"
                  class="px-2 py-1 rounded text-sm"
                >
                  {{ row.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="border border-gray-200 px-4 py-2 space-x-2">
                <button @click="startEdit(row)" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm hover:bg-yellow-600">
                  Edit
                </button>
                <button @click="removeRow(row.id)" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="!loading && rows.length === 0">
              <td colspan="6" class="text-center py-6 text-gray-600">No examinations found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-between items-center mt-4">
        <div class="text-gray-600">Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries</div>
        <div class="flex gap-1">
          <button
            :disabled="meta.current_page === 1"
            @click="load(meta.current_page - 1)"
            class="px-2 py-1 border rounded disabled:opacity-50"
          >
            &laquo;
          </button>
          <button
            v-for="page in meta.last_page"
            :key="page"
            @click="load(page)"
            :class="['px-2 py-1 border rounded', page === meta.current_page ? 'bg-blue-600 text-white' : '']"
          >
            {{ page }}
          </button>
          <button
            :disabled="meta.current_page === meta.last_page"
            @click="load(meta.current_page + 1)"
            class="px-2 py-1 border rounded disabled:opacity-50"
          >
            &raquo;
          </button>
        </div>
      </div>

      <div v-if="activeForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded p-6 w-full max-w-xl shadow-lg relative overflow-y-auto max-h-[90vh]">
          <button
            @click="activeForm = false"
            class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl font-bold"
          >
            &times;
          </button>

          <h3 class="text-xl font-semibold mb-4">{{ editing ? 'Edit Examination' : 'Create Examination' }}</h3>

          <form @submit.prevent="submit" class="space-y-4">
            <div>
              <label class="block mb-1">Exam Name *</label>
              <input v-model="form.name" placeholder="Exam Name" class="input text-black" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div>
                <label class="block mb-1">Starting Date *</label>
                <input v-model="form.start_date" type="date" class="input text-black" />
              </div>
              <div>
                <label class="block mb-1">Ending Date *</label>
                <input v-model="form.end_date" type="date" class="input text-black" />
              </div>
            </div>

            <div>
              <label class="block mb-1">Status</label>
              <select v-model="form.is_active" class="input text-black">
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary w-full" :disabled="loading">
              {{ loading ? 'Saving...' : editing ? 'Update' : 'Save' }}
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

