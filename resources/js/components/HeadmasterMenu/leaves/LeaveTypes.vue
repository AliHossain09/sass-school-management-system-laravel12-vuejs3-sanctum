<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { ref, onMounted, watch } from 'vue'
import { useToast } from 'vue-toastification'
import Swal from 'sweetalert2'
import axios from 'axios'

const toast = useToast()

const leaveTypes = ref<any[]>([])
const meta = ref({ current_page: 1, from: 0, to: 0, total: 0, last_page: 0, per_page: 10 })
const loading = ref(false)
const activeForm = ref(false)
const editing = ref<any | null>(null)

const form = ref({
  name: '',
  is_active: true,
})

const search = ref('')
let searchTimeout: any = null

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
})

const loadLeaveTypes = async (page = 1) => {
  loading.value = true
  try {
    const res = await axios.get('/api/leave-types', {
      headers: authHeader(),
      params: { page, search: search.value },
    })
    leaveTypes.value = res.data.data
    meta.value = res.data.meta
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadLeaveTypes(1), 400)
})

onMounted(() => {
  loadLeaveTypes()
})

const openForm = () => {
  activeForm.value = true
  editing.value = null
  form.value = { name: '', is_active: true }
}

const startEdit = (row: any) => {
  editing.value = row
  activeForm.value = true
  form.value = {
    name: row.name,
    is_active: !!row.is_active,
  }
}

const submit = async () => {
  if (!form.value.name) {
    toast.error('Leave type is required')
    return
  }

  loading.value = true
  try {
    if (editing.value) {
      await axios.put(`/api/leave-types/${editing.value.id}`, form.value, { headers: authHeader() })
      toast.success('Leave type updated')
    } else {
      await axios.post('/api/leave-types', form.value, { headers: authHeader() })
      toast.success('Leave type created')
    }

    activeForm.value = false
    await loadLeaveTypes(meta.value.current_page)
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Failed to save')
  } finally {
    loading.value = false
  }
}

const deleteLeaveType = async (id: number) => {
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
    await axios.delete(`/api/leave-types/${id}`, { headers: authHeader() })
    toast.success('Leave type deleted')
    await loadLeaveTypes(meta.value.current_page)
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
      <p class="text-gray-700">Leaves ></p>
      <p class="text-gray-700">Leave Type</p>
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold mb-1">Leave Types</h1>
        <input
          v-model="search"
          type="text"
          placeholder="Search leave type..."
          class="border-b rounded-md border-blue-400 px-4 py-2 w-64 focus:outline-none"
        />
        <button @click="openForm" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">
          + Add Leave Type
        </button>
      </div>

      <div v-if="loading" class="text-center text-gray-600">Loading...</div>

      <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-200 bg-white rounded-md">
          <thead class="bg-blue-900 text-white">
            <tr>
              <th class="border border-gray-200 px-4 py-2 text-left">SL</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Leave Type</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Status</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, i) in leaveTypes" :key="row.id" class="hover:bg-gray-50">
              <td class="border border-gray-200 px-4 py-2">{{ (meta.from || 1) + i }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ row.name }}</td>
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
                <button @click="deleteLeaveType(row.id)" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="!loading && leaveTypes.length === 0">
              <td colspan="4" class="text-center py-6 text-gray-600">No leave types found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-between items-center mt-4">
        <div class="text-gray-600">Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries</div>
        <div class="flex gap-1">
          <button
            :disabled="meta.current_page === 1"
            @click="loadLeaveTypes(meta.current_page - 1)"
            class="px-2 py-1 border rounded disabled:opacity-50"
          >
            &laquo;
          </button>
          <button
            v-for="page in meta.last_page"
            :key="page"
            @click="loadLeaveTypes(page)"
            :class="['px-2 py-1 border rounded', page === meta.current_page ? 'bg-blue-600 text-white' : '']"
          >
            {{ page }}
          </button>
          <button
            :disabled="meta.current_page === meta.last_page"
            @click="loadLeaveTypes(meta.current_page + 1)"
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

          <h3 class="text-xl font-semibold mb-4">{{ editing ? 'Edit Leave Type' : 'Create Leave Type' }}</h3>

          <form @submit.prevent="submit" class="space-y-4">
            <div>
              <label class="block mb-1">Leave Type *</label>
              <input v-model="form.name" placeholder="Leave Type" class="input text-black" />
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

