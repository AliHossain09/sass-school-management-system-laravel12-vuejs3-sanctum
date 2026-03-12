<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { onMounted, ref, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useToast } from 'vue-toastification'

const toast = useToast()

const rows = ref<any[]>([])
const meta = ref({ current_page: 1, from: 0, to: 0, total: 0, last_page: 0, per_page: 10 })
const loading = ref(false)
const search = ref('')
let searchTimeout: any = null

const classes = ref<any[]>([])
const subjects = ref<any[]>([])
const loadingSubjects = ref(false)

const activeForm = ref(false)
const editing = ref<any | null>(null)
const suppressSubjectReset = ref(false)

const form = ref({
  class_id: '' as any,
  subject_id: '' as any,
  exam_date: '',
  start_time: '',
  end_time: '',
  duration_hours: '' as any,
  duration_extra_minutes: '' as any,
  room: '',
})

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
})

const formatDuration = (totalMinutes: any) => {
  const m = Number(totalMinutes)
  if (!Number.isFinite(m) || m <= 0) return '-'
  const hrs = Math.floor(m / 60)
  const mins = m % 60
  if (hrs <= 0) return `${mins} min`
  if (mins === 0) return `${hrs} hrs`
  return `${hrs} hrs ${mins} min`
}

const loadClasses = async () => {
  try {
    const res = await axios.get('/api/classes', { headers: authHeader(), params: { per_page: 200 } })
    classes.value = res.data.data || []
  } catch (err) {
    console.error(err)
  }
}

const loadSubjectsByClass = async (classId: number | string) => {
  if (!classId) {
    subjects.value = []
    form.value.subject_id = ''
    return
  }

  loadingSubjects.value = true
  try {
    const res = await axios.get(`/api/subjects/by-class/${classId}`, { headers: authHeader() })
    subjects.value = res.data.data || []
  } catch (err) {
    console.error(err)
  } finally {
    loadingSubjects.value = false
  }
}

watch(
  () => form.value.class_id,
  async (v, oldV) => {
    if (!suppressSubjectReset.value && oldV && v !== oldV) {
      form.value.subject_id = ''
    }
    await loadSubjectsByClass(v)
  }
)

const load = async (page = 1) => {
  loading.value = true
  try {
    const res = await axios.get('/api/exam-schedules', {
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

onMounted(async () => {
  await loadClasses()
  await load()
})

const openForm = async () => {
  editing.value = null
  form.value = {
    class_id: '',
    subject_id: '',
    exam_date: '',
    start_time: '',
    end_time: '',
    duration_hours: '',
    duration_extra_minutes: '',
    room: '',
  }
  subjects.value = []
  activeForm.value = true
}

const startEdit = async (row: any) => {
  editing.value = row
  suppressSubjectReset.value = true
  const total = Number(row.duration_minutes || 0)
  const hrs = total > 0 ? Math.floor(total / 60) : 0
  const mins = total > 0 ? total % 60 : 0
  form.value = {
    class_id: row.class_id,
    subject_id: row.subject_id,
    exam_date: String(row.exam_date || '').slice(0, 10),
    start_time: String(row.start_time || '').slice(0, 5),
    end_time: String(row.end_time || '').slice(0, 5),
    duration_hours: hrs || '',
    duration_extra_minutes: mins || '',
    room: row.room || '',
  }
  await loadSubjectsByClass(row.class_id)
  suppressSubjectReset.value = false
  activeForm.value = true
}

const submit = async () => {
  if (!form.value.class_id) return toast.error('Class is required')
  if (!form.value.subject_id) return toast.error('Subject is required')
  if (!form.value.exam_date) return toast.error('Exam date is required')
  if (!form.value.start_time) return toast.error('Start time is required')
  if (!form.value.end_time) return toast.error('End time is required')
  if (form.value.duration_hours === '' && form.value.duration_extra_minutes === '') return toast.error('Duration is required')

  const hours = form.value.duration_hours === '' ? 0 : Number(form.value.duration_hours)
  const extraMinutes = form.value.duration_extra_minutes === '' ? 0 : Number(form.value.duration_extra_minutes)
  if (Number.isNaN(hours) || hours < 0) return toast.error('Hours must be a number')
  if (Number.isNaN(extraMinutes) || extraMinutes < 0 || extraMinutes > 59) return toast.error('Minutes must be 0-59')
  const totalMinutes = hours * 60 + extraMinutes
  if (totalMinutes <= 0) return toast.error('Duration must be greater than 0')

  const payload = {
    class_id: Number(form.value.class_id),
    subject_id: Number(form.value.subject_id),
    exam_date: form.value.exam_date,
    start_time: form.value.start_time,
    end_time: form.value.end_time,
    duration_minutes: totalMinutes,
    room: form.value.room ? String(form.value.room).trim() : null,
  }

  loading.value = true
  try {
    if (editing.value) {
      await axios.put(`/api/exam-schedules/${editing.value.id}`, payload, { headers: authHeader() })
      toast.success('Schedule updated')
    } else {
      await axios.post('/api/exam-schedules', payload, { headers: authHeader() })
      toast.success('Schedule created')
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
    await axios.delete(`/api/exam-schedules/${id}`, { headers: authHeader() })
    toast.success('Schedule deleted')
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
      <p class="text-gray-700">Exam Schedule</p>
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">
      <div class="flex justify-between items-center mb-6 gap-3">
        <h1 class="text-2xl font-bold mb-1">Exam Schedule</h1>

        <input
          v-model="search"
          type="text"
          placeholder="Search class/subject/room..."
          class="border-b rounded-md border-blue-400 px-4 py-2 w-72 focus:outline-none"
        />

        <button @click="openForm" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">
          + Add Schedule
        </button>
      </div>

      <div v-if="loading" class="text-center text-gray-600">Loading...</div>

      <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-200 bg-white rounded-md">
          <thead class="bg-blue-900 text-white">
            <tr>
              <th class="border border-gray-200 px-4 py-2 text-left">SL</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Class</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Subject</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Exam Date</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Start Time</th>
              <th class="border border-gray-200 px-4 py-2 text-left">End Time</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Duration</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Room</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, i) in rows" :key="row.id" class="hover:bg-gray-50">
              <td class="border border-gray-200 px-4 py-2">{{ (meta.from || 1) + i }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ row.school_class?.name || '-' }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ row.subject?.name || '-' }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ String(row.exam_date).slice(0, 10) }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ String(row.start_time).slice(0, 5) }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ String(row.end_time).slice(0, 5) }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ formatDuration(row.duration_minutes) }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ row.room || '-' }}</td>
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
              <td colspan="9" class="text-center py-6 text-gray-600">No schedules found</td>
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
        <div class="bg-white rounded p-6 w-full max-w-2xl shadow-lg relative overflow-y-auto max-h-[90vh]">
          <button
            @click="activeForm = false"
            class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl font-bold"
          >
            &times;
          </button>

          <h3 class="text-xl font-semibold mb-4">{{ editing ? 'Edit Schedule' : 'Create Schedule' }}</h3>

          <form @submit.prevent="submit" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div>
                <label class="block mb-1">Class *</label>
                <select v-model="form.class_id" class="input text-black">
                  <option value="">Select class</option>
                  <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>

              <div>
                <label class="block mb-1">Subject *</label>
                <select v-model="form.subject_id" class="input text-black" :disabled="loadingSubjects || !form.class_id">
                  <option value="">Select subject</option>
                  <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div>
                <label class="block mb-1">Exam Date *</label>
                <input v-model="form.exam_date" type="date" class="input text-black" />
              </div>
              <div>
                <label class="block mb-1">Start Time *</label>
                <input v-model="form.start_time" type="time" class="input text-black" />
              </div>
              <div>
                <label class="block mb-1">End Time *</label>
                <input v-model="form.end_time" type="time" class="input text-black" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div>
                <label class="block mb-1">Duration *</label>
                <div class="grid grid-cols-2 gap-3">
                  <input
                    v-model="form.duration_hours"
                    type="number"
                    min="0"
                    max="24"
                    placeholder="Hours (e.g. 3)"
                    class="input text-black"
                  />
                  <input
                    v-model="form.duration_extra_minutes"
                    type="number"
                    min="0"
                    max="59"
                    placeholder="Minutes (e.g. 25)"
                    class="input text-black"
                  />
                </div>
              </div>
              <div>
                <label class="block mb-1">Room</label>
                <input v-model="form.room" placeholder="e.g. 201" class="input text-black" />
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
