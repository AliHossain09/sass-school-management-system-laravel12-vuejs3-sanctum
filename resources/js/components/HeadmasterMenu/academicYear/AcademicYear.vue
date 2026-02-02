<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { ref, onMounted, watch } from 'vue'
import { useToast } from 'vue-toastification'
import Swal from 'sweetalert2'
import axios from 'axios'

const toast = useToast()

// --- State ---
const academicYears = ref<any[]>([])
const loading = ref(false)
const activeForm = ref(false)
const editingYear = ref<any | null>(null)

const form = ref({
  start_date: '',
  end_date: '',
  is_active: false
})

const search = ref('')
let searchTimeout: any = null

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`
})

// --- Load Academic Years ---
const loadAcademicYears = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/academic-years', {
      headers: authHeader()
    })
    academicYears.value = res.data // direct array from API
  } catch (err) {
    console.error(err)
    toast.error('Failed to load academic years')
  } finally {
    loading.value = false
  }
}

// --- Watch search ---
watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadAcademicYears(), 400)
})

// --- Lifecycle ---
onMounted(() => {
  loadAcademicYears()
})

// --- Open Form ---
const openForm = () => {
  activeForm.value = true
  editingYear.value = null
  form.value = {
    start_date: '',
    end_date: '',
    is_active: false
  }
}

// --- Submit Form ---
const submit = async () => {
  if (!form.value.start_date || !form.value.end_date) {
    toast.error('Start date and End date required')
    return
  }

  loading.value = true
  try {
    if (editingYear.value) {
      await axios.put(`/api/academic-years/${editingYear.value.id}`, form.value, {
        headers: authHeader()
      })
      toast.success('Academic Year updated')
    } else {
      await axios.post('/api/academic-years', form.value, {
        headers: authHeader()
      })
      toast.success('Academic Year created')
    }

    activeForm.value = false
    loadAcademicYears()
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Failed to save academic year')
  } finally {
    loading.value = false
  }
}

// --- Edit ---
const startEdit = (year: any) => {
  editingYear.value = year
  activeForm.value = true
  form.value = {
    start_date: year.start_date,
    end_date: year.end_date,
    is_active: year.is_active
  }
}

// --- Delete ---
const deleteYear = async (id: number) => {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: 'This action cannot be undone!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!'
  })

  if (!result.isConfirmed) return

  try {
    loading.value = true
    await axios.delete(`/api/academic-years/${id}`, { headers: authHeader() })
    toast.success('Academic Year deleted')
    loadAcademicYears()
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Failed to delete')
  } finally {
    loading.value = false
  }
}
</script>

<template>
<HeadmasterLayout>
  <!-- breadcrumb -->
  <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
    <p class="text-gray-700">Headmaster ></p>
    <p class="text-gray-700">Academic Years ></p>
    <p class="text-gray-700">List</p>
  </div>

  <div class="p-6 shadow-2xl bg-gray-50 rounded">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold mb-1">Academic Years</h1>

      <!-- <input
        v-model="search"
        type="text"
        placeholder="Search by year..."
        class="border-b rounded-md border-blue-400 px-4 py-2 w-64 focus:outline-none"
      /> -->

      <button type="button" @click="openForm" class="bg-blue-600 text-white px-5 py-2 rounded">Create Academic Year</button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg bg-white shadow">
      <table class="min-w-full table-auto text-left">
        <thead class="bg-blue-200 text-blue-700">
          <tr>
            <th class="px-4 py-2">SL.</th>
            <th class="px-4 py-2">Start Date</th>
            <th class="px-4 py-2">End Date</th>
            <th class="px-4 py-2">Active</th>
            <th class="px-4 py-2 text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(year, index) in academicYears" :key="year.id" class="border-b last:border-b-0 hover:bg-gray-50">
            <td class="px-4 py-3">{{ index + 1 }}</td>
            <td class="px-4 py-3">{{ year.start_date }}</td>
            <td class="px-4 py-3">{{ year.end_date }}</td>
            <td class="px-4 py-3">
              <span :class="year.is_active ? 'text-green-600 font-semibold' : 'text-gray-500'">
                {{ year.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-4 py-3 flex justify-center gap-2">
              <button @click="startEdit(year)" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Edit</button>
              <button @click="deleteYear(year.id)" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="activeForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded p-6 w-full max-w-xl shadow-lg relative overflow-y-auto max-h-[90vh]">
        <button @click="activeForm = false" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl font-bold">&times;</button>

        <h3 class="text-xl font-semibold mb-4">{{ editingYear ? 'Edit Academic Year' : 'Create Academic Year' }}</h3>

        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block mb-1">Start Date *</label>
              <input type="date" v-model="form.start_date" class="input" />
            </div>

            <div>
              <label class="block mb-1">End Date *</label>
              <input type="date" v-model="form.end_date" class="input" />
            </div>

            <div class="md:col-span-2 flex items-center gap-2">
              <input type="checkbox" v-model="form.is_active" />
              <label>Set as Active</label>
            </div>
          </div>

          <button type="submit" class="btn-primary w-full" :disabled="loading">
            {{ loading ? 'Saving...' : editingYear ? 'Update Academic Year' : 'Save Academic Year' }}
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
.fixed {
  z-index: 9999;
}
</style>
