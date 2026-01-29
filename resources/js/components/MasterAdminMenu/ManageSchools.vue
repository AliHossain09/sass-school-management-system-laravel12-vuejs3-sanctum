<script setup lang="ts">
import MasterAdminLayout from '../../layouts/MasterAdminLayout.vue'
import { ref, onMounted } from 'vue'
import axios from 'axios'

/* --- State --- */
const activeRole = ref<'school' | null>(null)
const form = ref({ name: '', password: '', address: '' })
const errors = ref<any>({})
const loading = ref(false)
const schools = ref<any[]>([])
const meta = ref({
  current_page: 1,
  from: 0,
  to: 0,
  total: 0,
  last_page: 0
})
const editingSchool = ref<any | null>(null)
const showPassword = ref(false)

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`
})

/* --- Load Schools with pagination --- */
const loadSchools = async (page = 1) => {
  try {
    const res = await axios.get(`/api/schools?page=${page}`, { headers: authHeader() })
    schools.value = res.data.data
    meta.value = res.data.meta
  } catch (e) {
    console.error('Failed to load schools', e)
  }
}
onMounted(() => loadSchools())

/* --- Open Form --- */
const openForm = () => {
  activeRole.value = 'school'
  errors.value = {}
  form.value = { name: '', password: '', address: '' }
  showPassword.value = false
}

/* --- Create School --- */
const createSchool = async () => {
  errors.value = {}
  if (!form.value.name || !form.value.password || !form.value.address) {
    errors.value.general = 'All fields required'
    return
  }
  try {
    loading.value = true
    await axios.post('/api/schools', form.value, { headers: authHeader() })
    alert('School created successfully')
    activeRole.value = null
    loadSchools()
  } catch (e: any) {
    errors.value = e.response?.data?.errors || { general: 'Failed to create' }
  } finally {
    loading.value = false
  }
}

/* --- Edit School --- */
const startEdit = (school: any) => {
  editingSchool.value = { ...school }
}

const updateSchool = async () => {
  if (!editingSchool.value) return
  try {
    await axios.put(
      `/api/schools/${editingSchool.value.id}`,
      {
        name: editingSchool.value.name,
        address: editingSchool.value.address,
        email: editingSchool.value.headmaster.email
      },
      { headers: authHeader() }
    )
    editingSchool.value = null
    loadSchools(meta.value.current_page)
  } catch (e) {
    alert('Failed to update school')
  }
}

/* --- Delete School --- */
const deleteSchool = async (id: number) => {
  if (!confirm('Delete this school?')) return
  try {
    await axios.delete(`/api/schools/${id}`, { headers: authHeader() })
    loadSchools(meta.value.current_page)
  } catch (e) {
    alert('Failed to delete school')
  }
}
</script>

<template>
  <MasterAdminLayout>
    <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
          <p class="text-gray-700">Master Admin ></p>
          <p class="text-gray-700">Manage School</p> 
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">

      <!-- Header + Create Button -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold mb-1">School List</h1>
          <!-- <h1 class="text-2xl font-bold mb-1">Master Admin Dashboard</h1>
          <p class="text-gray-700">Create schools from here</p> -->
        </div>
        <button
          @click="openForm"
          class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 flex items-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create School
        </button>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto rounded-lg bg-white shadow">
        <table class="min-w-full table-auto text-left">
          <thead class="bg-blue-200 text-blue-700">
            <tr>
              <th class="px-4 py-2 font-semibold">SL.</th>
              <th class="px-4 py-2 font-semibold">Name</th>
              <th class="px-4 py-2 font-semibold">Address</th>
              <th class="px-4 py-2 font-semibold">Headmaster</th>
              <th class="px-4 py-2 font-semibold">Login Info</th>
              <th class="px-4 py-2 font-semibold text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(school, index) in schools" :key="school.id" class="border-b last:border-b-0 hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-blue-600 font-semibold">{{ String(index + 1 + (meta.current_page-1)*meta.per_page).padStart(5,'0') }}</td>
              <td class="px-4 py-3 font-semibold text-gray-700">{{ school.name }}</td>
              <td class="px-4 py-3">{{ school.address }}</td>
              <td class="px-4 py-3">{{ school.headmaster?.name || 'N/A' }}</td>
              <td class="px-4 py-3">{{ school.headmaster?.email || 'N/A' }}</td>
              <td class="px-4 py-3 flex justify-center gap-2">
                <button @click="startEdit(school)" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Edit</button>
                <button @click="deleteSchool(school.id)" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">Delete</button>
              </td>
            </tr>
            <tr v-if="schools.length === 0">
              <td colspan="6" class="text-center py-4 text-gray-500">No schools found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex justify-between items-center mt-4">
        <!-- Left side: showing entries -->
        <div class="text-gray-600">
          Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries
        </div>

        <!-- Right side: page numbers -->
        <div class="flex gap-1">
          <button :disabled="meta.current_page===1" @click="loadSchools(meta.current_page-1)" class="px-2 py-1 border rounded disabled:opacity-50">&laquo;</button>

          <button v-for="page in meta.last_page" :key="page" @click="loadSchools(page)" :class="['px-2 py-1 border rounded', page===meta.current_page ? 'bg-blue-600 text-white' : '']">
            {{ page }}
          </button>

          <button :disabled="meta.current_page===meta.last_page" @click="loadSchools(meta.current_page+1)" class="px-2 py-1 border rounded disabled:opacity-50">&raquo;</button>
        </div>
      </div>

      <!-- Create/Edit Form Modal -->
      <div v-if="activeRole==='school' || editingSchool" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded p-6 w-full max-w-md shadow-lg relative">
          <button @click="activeRole=null; editingSchool=null" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl font-bold">&times;</button>

          <h3 class="text-xl font-semibold mb-4">{{ activeRole==='school' ? 'Create School' : 'Edit School' }}</h3>

          <div v-if="errors.general" class="text-red-600 mb-2">{{ errors.general }}</div>

          <input v-if="activeRole==='school'" v-model="form.name" placeholder="School Name" class="w-full border px-3 py-2 mb-3 rounded"/>
          <template v-if="activeRole==='school'">
            <div class="relative mb-3">
              <input v-model="form.password" :type="showPassword?'text':'password'" placeholder="Password" class="w-full border px-3 py-2 rounded pr-12"/>
              <button type="button" @click="showPassword=!showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-600">{{ showPassword?'Hide':'Show' }}</button>
            </div>
            <textarea v-model="form.address" placeholder="Address" class="w-full border px-3 py-2 mb-4 rounded"></textarea>
          </template>

          <template v-if="editingSchool">
            <textarea v-model="editingSchool.name" placeholder="Name" class="w-full border px-3 py-2 mb-4 rounded"></textarea>
            <textarea v-model="editingSchool.address" placeholder="Address" class="w-full border px-3 py-2 mb-4 rounded"></textarea>
            <textarea v-model="editingSchool.headmaster.email" placeholder="Email" class="w-full border px-3 py-2 mb-4 rounded"></textarea>
          </template>

          <div class="flex justify-end gap-2">
            <button @click="activeRole=null; editingSchool=null" class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
            <button v-if="activeRole==='school'" @click="createSchool" :disabled="loading" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:opacity-60">{{ loading?'Creating...':'Create' }}</button>
            <button v-if="editingSchool" @click="updateSchool" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Update</button>
          </div>
        </div>
      </div>
    </div>
  </MasterAdminLayout>
</template>
