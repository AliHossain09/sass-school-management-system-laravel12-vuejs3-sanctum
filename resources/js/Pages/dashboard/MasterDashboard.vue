<script setup lang="ts">
import AdminLayout from '../../layouts/AdminLayout.vue'
import { ref, onMounted } from 'vue'
import axios from 'axios'

/* --- State --- */
const activeRole = ref<'school' | null>(null)
const form = ref({ name: '', password: '', address: '' })
const errors = ref<any>({})
const loading = ref(false)
const schools = ref<any[]>([])
const editingSchool = ref<any | null>(null)

/* --- Helpers --- */
const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`
})

/* --- Load Schools --- */
const loadSchools = async () => {
  try {
    const res = await axios.get('/api/schools', { headers: authHeader() })
    schools.value = res.data.data
  } catch (e) {
    console.error('Failed to load schools', e)
  }
}
onMounted(loadSchools)

/* --- Open Form --- */
const openForm = () => {
  activeRole.value = 'school'
  errors.value = {}
  form.value = { name: '', password: '', address: '' }
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
    openForm()
    loadSchools()
  } catch (e: any) {
    errors.value = e.response?.data?.errors || { general: 'Failed to create' }
  } finally {
    loading.value = false
  }
}

/* --- Edit School --- */
const startEdit = (school: any) => { editingSchool.value = { ...school } }
const updateSchool = async () => {
  if (!editingSchool.value) return
  try {
    await axios.put(`/api/schools/${editingSchool.value.id}`, {
      name: editingSchool.value.name,
      address: editingSchool.value.address
    }, { headers: authHeader() })
    editingSchool.value = null
    loadSchools()
  } catch (e) { alert('Failed to update school') }
}

/* --- Delete School --- */
const deleteSchool = async (id: number) => {
  if (!confirm('Delete this school?')) return
  try {
    await axios.delete(`/api/schools/${id}`, { headers: authHeader() })
    loadSchools()
  } catch (e) { alert('Failed to delete school') }
}
</script>

<template>
  <AdminLayout>
    <div>
      <h1 class="text-3xl font-bold mb-4">Master Admin Dashboard</h1>
      <p class="mb-6">Create schools from here</p>

      <button @click="openForm" class="mb-6 bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded">
        ➕ Create School
      </button>

      <!-- Create Form -->
      <div v-if="activeRole === 'school'" class="max-w-md bg-white p-6 rounded shadow mb-8">
        <h3 class="text-xl font-semibold mb-4">Create School</h3>
        <div v-if="errors.general" class="text-red-600 mb-2">{{ errors.general }}</div>

        <input v-model="form.name" placeholder="School Name" class="w-full border px-3 py-2 mb-3 rounded" />
        <input v-model="form.password" type="password" placeholder="Password" class="w-full border px-3 py-2 mb-3 rounded" />
        <textarea v-model="form.address" placeholder="Address" class="w-full border px-3 py-2 mb-4 rounded"></textarea>

        <button @click="createSchool" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded">
          {{ loading ? 'Creating...' : 'Create' }}
        </button>
      </div>

      <!-- School List -->
      <h2 class="text-2xl font-semibold mb-3">All Schools</h2>
      <table class="w-full bg-white border rounded shadow">
        <thead class="bg-gray-100">
          <tr>
            <th class="border p-2">Name</th>
            <th class="border p-2">Address</th>
            <th class="border p-2">Headmaster</th>
            <th class="border p-2">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in schools" :key="s.id">
            <td class="border p-2">{{ s.name }}</td>
            <td class="border p-2">{{ s.address }}</td>
            <td class="border p-2">{{ s.headmaster?.name }}</td>
            <td class="border p-2 flex gap-2">
              <button @click="startEdit(s)" class="bg-blue-600 text-white px-3 py-1 rounded text-sm">Edit</button>
              <button @click="deleteSchool(s.id)" class="bg-red-600 text-white px-3 py-1 rounded text-sm">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Edit Modal -->
      <div v-if="editingSchool" class="fixed inset-0 bg-black/40 flex items-center justify-center">
        <div class="bg-white p-6 rounded w-96">
          <h3 class="text-xl font-semibold mb-4">Edit School</h3>
          <input v-model="editingSchool.name" class="w-full border px-3 py-2 mb-3 rounded" />
          <textarea v-model="editingSchool.address" class="w-full border px-3 py-2 mb-4 rounded"></textarea>
          <div class="flex justify-end gap-2">
            <button @click="editingSchool = null" class="px-4 py-2 border rounded">Cancel</button>
            <button @click="updateSchool" class="bg-green-600 text-white px-4 py-2 rounded">Update</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
