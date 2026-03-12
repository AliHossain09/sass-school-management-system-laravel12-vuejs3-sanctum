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
  grade: '',
  grade_point: '' as any,
  mark_from: '' as any,
  mark_upto: '' as any,
})

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
})

const load = async (page = 1) => {
  loading.value = true
  try {
    const res = await axios.get('/api/grades', {
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
  form.value = { grade: '', grade_point: '', mark_from: '', mark_upto: '' }
  activeForm.value = true
}

const startEdit = (row: any) => {
  editing.value = row
  form.value = {
    grade: row.grade,
    grade_point: row.grade_point,
    mark_from: row.mark_from,
    mark_upto: row.mark_upto,
  }
  activeForm.value = true
}

const submit = async () => {
  if (!form.value.grade) return toast.error('Grade is required')
  if (form.value.grade_point === '' || form.value.grade_point === null) return toast.error('Grade point is required')
  if (form.value.mark_from === '' || form.value.mark_from === null) return toast.error('Mark from is required')
  if (form.value.mark_upto === '' || form.value.mark_upto === null) return toast.error('Mark upto is required')

  const payload = {
    grade: String(form.value.grade).trim(),
    grade_point: Number(form.value.grade_point),
    mark_from: Number(form.value.mark_from),
    mark_upto: Number(form.value.mark_upto),
  }

  if (Number.isNaN(payload.grade_point)) return toast.error('Grade point must be a number')
  if (Number.isNaN(payload.mark_from) || Number.isNaN(payload.mark_upto)) return toast.error('Marks must be numbers')
  if (payload.mark_from > payload.mark_upto) return toast.error('Mark from cannot be greater than mark upto')

  loading.value = true
  try {
    if (editing.value) {
      await axios.put(`/api/grades/${editing.value.id}`, payload, { headers: authHeader() })
      toast.success('Grade updated')
    } else {
      await axios.post('/api/grades', payload, { headers: authHeader() })
      toast.success('Grade created')
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
    await axios.delete(`/api/grades/${id}`, { headers: authHeader() })
    toast.success('Grade deleted')
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
      <p class="text-gray-700">Grade</p>
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">
      <div class="flex justify-between items-center mb-6 gap-3">
        <h1 class="text-2xl font-bold mb-1">Grade</h1>

        <input
          v-model="search"
          type="text"
          placeholder="Search grade..."
          class="border-b rounded-md border-blue-400 px-4 py-2 w-64 focus:outline-none"
        />

        <button @click="openForm" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">
          + Add Grade
        </button>
      </div>

      <div v-if="loading" class="text-center text-gray-600">Loading...</div>

      <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-200 bg-white rounded-md">
          <thead class="bg-blue-900 text-white">
            <tr>
              <th class="border border-gray-200 px-4 py-2 text-left">SL</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Grade</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Grade Point</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Mark From</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Mark Upto</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, i) in rows" :key="row.id" class="hover:bg-gray-50">
              <td class="border border-gray-200 px-4 py-2">{{ (meta.from || 1) + i }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ row.grade }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ row.grade_point }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ row.mark_from }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ row.mark_upto }}</td>
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
              <td colspan="6" class="text-center py-6 text-gray-600">No grades found</td>
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

          <h3 class="text-xl font-semibold mb-4">{{ editing ? 'Edit Grade' : 'Create Grade' }}</h3>

          <form @submit.prevent="submit" class="space-y-4">
            <div>
              <label class="block mb-1">Grade *</label>
              <input v-model="form.grade" placeholder="e.g. A+" class="input text-black" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div>
                <label class="block mb-1">Grade Point *</label>
                <input v-model="form.grade_point" type="number" step="0.01" placeholder="e.g. 5.00" class="input text-black" />
              </div>
              <div>
                <label class="block mb-1">Mark From *</label>
                <input v-model="form.mark_from" type="number" min="0" max="100" placeholder="e.g. 80" class="input text-black" />
              </div>
              <div>
                <label class="block mb-1">Mark Upto *</label>
                <input v-model="form.mark_upto" type="number" min="0" max="100" placeholder="e.g. 100" class="input text-black" />
              </div>
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

