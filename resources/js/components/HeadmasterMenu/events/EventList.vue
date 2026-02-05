<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'

import axios from 'axios'
import Swal from 'sweetalert2'
import { useToast } from 'vue-toastification'

const toast = useToast()

/* ================= STATE ================= */
const events = ref<any[]>([])
const loading = ref(false)
const activeForm = ref(false)
const isEdit = ref(false)
const editId = ref<number | null>(null)

const search = ref('')
let searchTimeout: any = null

const meta = ref({
  current_page: 1,
  from: 0,
  to: 0,
  total: 0,
  last_page: 0,
  per_page: 10,
})

const form = ref({
  title: '',
  start_date: '',
  end_date: '',
})

/* ================= CALENDAR ================= */
const calendarOptions = ref({
  plugins: [dayGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  height: 'auto',
  events: [] as any[],
})

/* ================= AUTH ================= */
const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
})

/* ================= LOAD EVENTS (SERVER PAGINATION) ================= */
const loadEvents = async (page = 1) => {
  loading.value = true
  try {
    const res = await axios.get('/api/events', {
      headers: authHeader(),
      params: {
        page,
        search: search.value,
      },
    })

    // table data
    events.value = res.data.data

    // pagination meta (Laravel default)
    meta.value = {
      current_page: res.data.current_page,
      from: res.data.from,
      to: res.data.to,
      total: res.data.total,
      last_page: res.data.last_page,
      per_page: res.data.per_page,
    }

    // calendar data
    calendarOptions.value.events = events.value.map(e => ({
      id: e.id,
      title: e.title,
      start: e.start_date ?? undefined,
      end: e.end_date ?? undefined,
    }))
  } catch (err: any) {
    console.error('EVENT LOAD ERROR:', err)
    toast.error(err?.response?.data?.message || 'Failed to load events')
  } finally {
    loading.value = false
  }
}

onMounted(() => loadEvents())

/* ================= SEARCH DEBOUNCE ================= */
watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadEvents(1), 400)
})

/* ================= FORM ================= */
const openCreate = () => {
  isEdit.value = false
  editId.value = null
  form.value = { title: '', start_date: '', end_date: '' }
  activeForm.value = true
}

const openEdit = (e: any) => {
  isEdit.value = true
  editId.value = e.id
  form.value = {
    title: e.title,
    start_date: e.start_date,
    end_date: e.end_date,
  }
  activeForm.value = true
}

/* ================= SAVE ================= */
const submit = async () => {
  loading.value = true
  try {
    if (isEdit.value && editId.value) {
      await axios.put(`/api/events/${editId.value}`, form.value, {
        headers: authHeader(),
      })
      toast.success('Event updated')
    } else {
      await axios.post('/api/events', form.value, {
        headers: authHeader(),
      })
      toast.success('Event created')
    }

    activeForm.value = false
    loadEvents(meta.value.current_page)
  } catch (err: any) {
    console.error(err)
    toast.error(err?.response?.data?.message || 'Action failed')
  } finally {
    loading.value = false
  }
}

/* ================= DELETE ================= */
const deleteEvent = async (id: number) => {
  const ok = await Swal.fire({
    title: 'Delete event?',
    icon: 'warning',
    showCancelButton: true,
  })

  if (!ok.isConfirmed) return

  await axios.delete(`/api/events/${id}`, { headers: authHeader() })
  toast.success('Deleted')

  if (events.value.length === 1 && meta.value.current_page > 1) {
    loadEvents(meta.value.current_page - 1)
  } else {
    loadEvents(meta.value.current_page)
  }
}
</script>

<template>
  <HeadmasterLayout>

     <!-- Breadcrumb -->
        <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
            <p class="text-gray-700">Headmaster ></p>
            <p class="text-gray-700">Events ></p>
            <p class="text-gray-700">List</p>
        </div>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6 bg-white p-4 shadow rounded">
      <h1 class="text-2xl font-bold">Event Calendar</h1>
      <button @click="openCreate" class="btn-primary">+ Add Event</button>
    </div>

    <!-- Calendar + Table -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white p-4 rounded shadow">
        <FullCalendar :options="calendarOptions" />
      </div>

      <div class="bg-white shadow-md rounded p-4 self-start">
        <div>
        <!-- Search -->
        <div class="mb-3 flex justify-center">
          <input v-model="search" placeholder="Search event..."
            class="border-b rounded-md border-blue-400 px-4 py-2 w-64 focus:outline-none" />
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-blue-200 text-blue-700">
              <tr>
                <th class="p-3 text-left">Title</th>
                <th class="p-3">From</th>
                <th class="p-3">To</th>
                <th class="p-3 text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(e, index) in events" :key="e.id" :class="[
                index % 2 === 0 ? 'bg-white' : 'bg-gray-200',
                'hover:bg-blue-400 hover:text-white'
              ]">
                <td class="p-3">{{ e.title }}</td>
                <td class="p-3 text-center">{{ e.start_date }}</td>
                <td class="p-3 text-center">{{ e.end_date }}</td>
                <td class="p-3 text-center space-x-2">
                  <button @click="openEdit(e)" class="bg-blue-600 text-white px-3 py-1 rounded">Edit</button>
                  <button @click="deleteEvent(e.id)" class="bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                </td>
              </tr>

              <tr v-if="events.length === 0">
                <td colspan="4" class="text-center py-6 text-gray-500">
                  No events found
                </td>
              </tr>
            </tbody>

          </table>
        </div>
      </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-4">
          <div class="text-gray-600">
            Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }}
          </div>

          <div class="flex gap-1">
            <button :disabled="meta.current_page === 1" @click="loadEvents(meta.current_page - 1)"
              class="px-2 py-1 border rounded disabled:opacity-50">
              &laquo;
            </button>

            <button v-for="p in meta.last_page" :key="p" @click="loadEvents(p)" :class="[
              'px-2 py-1 border rounded',
              p === meta.current_page ? 'bg-blue-600 text-white' : ''
            ]">
              {{ p }}
            </button>

            <button :disabled="meta.current_page === meta.last_page" @click="loadEvents(meta.current_page + 1)"
              class="px-2 py-1 border rounded disabled:opacity-50">
              &raquo;
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="activeForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      @click.self="activeForm = false">
      <div class="bg-white w-full max-w-md p-6 rounded shadow">
        <h3 class="text-xl font-semibold mb-4">
          {{ isEdit ? 'Edit Event' : 'Add Event' }}
        </h3>

        <form @submit.prevent="submit" class="space-y-4">
          <input v-model="form.title" class="input" placeholder="Title" required />
          <input v-model="form.start_date" type="date" class="input" required />
          <input v-model="form.end_date" type="date" class="input" />

          <button class="btn-primary w-full" :disabled="loading">
            {{ loading ? 'Saving...' : 'Save' }}
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
  color: white;
  padding: 10px 16px;
  border-radius: 6px;
}

.btn-edit {
  background: #f59e0b;
  color: white;
  padding: 4px 10px;
  border-radius: 4px;
}

.btn-delete {
  background: #dc2626;
  color: white;
  padding: 4px 10px;
  border-radius: 4px;
}
</style>
