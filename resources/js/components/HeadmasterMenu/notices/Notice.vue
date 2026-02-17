<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

/* ================= STATE ================= */
const notices = ref([])
const classes = ref([])
const sections = ref([])
const search = ref('')
const loading = ref(false)

const meta = ref({
  current_page: 1,
  from: 0,
  to: 0,
  total: 0,
  last_page: 0,
  per_page: 10,
})

/* ================= AUTH ================= */
const authHeader = () => {
  const token = localStorage.getItem('token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

/* ================= LOAD FUNCTIONS ================= */
const loadNotices = async (page = 1) => {
  try {
    loading.value = true

    const res = await axios.get('/api/notices', {
      headers: authHeader(),
      params: {
        page,
        search: search.value
      }
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

  } catch (err) {
    console.error('Failed to load notices')
  } finally {
    loading.value = false
  }
}

const loadClasses = async () => {
  const res = await axios.get('/api/classes', { headers: authHeader() })
  classes.value = res.data.data
}

const loadSections = async () => {
  const res = await axios.get('/api/sections', { headers: authHeader() })
  sections.value = res.data.data
}

/* ================= SEARCH DEBOUNCE ================= */
let timeout = null
watch(search, () => {
  clearTimeout(timeout)
  timeout = setTimeout(() => {
    loadNotices(1)
  }, 400)
})

/* ================= MOUNT ================= */
onMounted(() => {
  loadNotices()
  loadClasses()
  loadSections()
})
</script>


<template>

  <!-- Breadcrumb -->
  <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
    <p class="text-gray-700">Notice ></p>
    <p class="text-gray-700">List</p>
  </div>

  <!-- Header -->
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Notice List</h1>

    <input
      v-model="search"
      type="text"
      placeholder="Search by title..."
      class="border-b rounded-md border-blue-400 px-4 py-2 w-64 focus:outline-none"
    />
  </div>

  <!-- Table -->
  <div class="overflow-x-auto rounded-lg bg-white shadow">

    <table class="min-w-full table-auto text-left">
      <thead class="bg-blue-200 text-blue-700">
        <tr>
          <th class="px-4 py-2">SL</th>
          <th class="px-4 py-2">Title</th>
          <th class="px-4 py-2">Type</th>
          <th class="px-4 py-2">Classes</th>
          <th class="px-4 py-2">Sections</th>
          <th class="px-4 py-2">Publish Date</th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="(n, index) in notices"
          :key="n.id"
          class="border-b hover:bg-gray-50"
        >
          <td class="px-4 py-3">
            {{ index + 1 + (meta.current_page - 1) * meta.per_page }}
          </td>

          <td class="px-4 py-3">{{ n.title }}</td>
          <td class="px-4 py-3">{{ n.type }}</td>

          <!-- Classes -->
          <td class="px-4 py-3">
            {{
              n.class_ids?.length
                ? n.class_ids
                    .map(id => classes.find(c => c.id === id)?.name)
                    .filter(Boolean)
                    .join(', ')
                : 'All'
            }}
          </td>

          <!-- Sections -->
          <td class="px-4 py-3">
            {{
              n.section_ids?.length
                ? n.section_ids
                    .map(id => sections.find(s => s.id === id)?.name)
                    .filter(Boolean)
                    .join(', ')
                : 'N/A'
            }}
          </td>

          <td class="px-4 py-3">{{ n.publish_date }}</td>
        </tr>

        <tr v-if="!loading && notices.length === 0">
          <td colspan="6" class="text-center py-4 text-gray-500">
            No notices found
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="flex justify-between items-center mt-4 p-4">

      <div class="text-gray-600">
        Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} entries
      </div>

      <div class="flex gap-1">
        <button
          :disabled="meta.current_page === 1"
          @click="loadNotices(meta.current_page - 1)"
          class="px-2 py-1 border rounded disabled:opacity-50"
        >
          &laquo;
        </button>

        <button
          v-for="page in meta.last_page"
          :key="page"
          @click="loadNotices(page)"
          :class="[
            'px-2 py-1 border rounded',
            page === meta.current_page ? 'bg-blue-600 text-white' : ''
          ]"
        >
          {{ page }}
        </button>

        <button
          :disabled="meta.current_page === meta.last_page"
          @click="loadNotices(meta.current_page + 1)"
          class="px-2 py-1 border rounded disabled:opacity-50"
        >
          &raquo;
        </button>
      </div>
    </div>

  </div>

</template>
