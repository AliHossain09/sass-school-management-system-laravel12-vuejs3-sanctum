<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

/* ================= STATE ================= */
const routines = ref<any[]>([])
const meta = ref<any>({})
const classes = ref<any[]>([])
const sections = ref<any[]>([])

const selectedClass = ref('')
const selectedSection = ref('')
const currentPage = ref(1)

const days = [
  'Saturday','Sunday','Monday',
  'Tuesday','Wednesday','Thursday','Friday'
]

/* ================= AUTH HEADER ================= */
const authHeader = () => {
  const token = localStorage.getItem('token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

/* ================= LOAD DROPDOWNS ================= */
const loadDropdowns = async () => {
  try {
    const [c, s] = await Promise.all([
      axios.get('/api/classes', { headers: authHeader() }),
      axios.get('/api/sections', { headers: authHeader() }),
    ])
    classes.value = c.data.data ?? c.data
    sections.value = s.data.data ?? s.data
  } catch (err) {
    console.error('Failed to load dropdowns:', err)
  }
}

/* ================= LOAD ROUTINES ================= */
const loadRoutines = async (page = 1) => {
  try {
    currentPage.value = page
    const res = await axios.get('/api/class-routines', {
      headers: authHeader(),
      params: {
        class_id: selectedClass.value,
        section_id: selectedSection.value,
        page
      }
    })

    routines.value = res.data.data
    meta.value = res.data.meta ?? res.data
  } catch (err) {
    console.error('Failed to load routines:', err)
  }
}

/* ================= GROUP BY DAY ================= */
const groupedRoutines = computed(() => {
  const map: Record<string, any[]> = {}
  days.forEach(day => map[day] = [])

  routines.value.forEach(r => {
    // routines may have multiple days
    if (r.other_days?.length) {
      r.other_days.forEach((d: string) => {
        if (map[d]) map[d].push(r)
      })
    } else if (r.day && map[r.day]) {
      map[r.day].push(r)
    }
  })

  return map
})

/* ================= PAGINATION CONTROL ================= */
const totalPages = computed<number>(() => meta.value?.last_page ?? 1)

const goToPage = (page: number) => {
  if (page < 1 || page > totalPages.value) return
  loadRoutines(page)
}

/* ================= FILTER HANDLERS ================= */
// const onClassChange = async (event: Event) => {
//   const target = event.target as HTMLSelectElement
//   selectedClass.value = target.value
//   await loadRoutines(1)
// }

// const onSectionChange = async (event: Event) => {
//   const target = event.target as HTMLSelectElement
//   selectedSection.value = target.value
//   await loadRoutines(1)
// }

/* ================= LIFECYCLE ================= */
onMounted(() => {
  // loadDropdowns()
  loadRoutines()
})
</script>

<template>
  <div class="flex justify-between items-center mb-6  shadow-xl rounded  p-4 bg-white gap-2">
            <h1 class="text-2xl font-bold">Class Routine</h1>
        </div>
<div class="p-4 bg-white hadow-xl rounded">

  <!-- FILTER -->
  <!-- <div class="flex gap-4 mb-6">
    <select v-model="selectedClass" @change="onClassChange" class="input">
      <option value="">Select Class</option>
      <option v-for="c in classes" :key="c.id" :value="c.id">
        {{ c.name }}
      </option>
    </select>

    <select v-model="selectedSection" @change="onSectionChange" class="input">
      <option value="">Select Section</option>
      <option v-for="s in sections" :key="s.id" :value="s.id">
        {{ s.name }}
      </option>
    </select>
  </div> -->

  <!-- ROUTINE GRID -->
  <div v-for="day in days" :key="day" class="mb-6">
    <h2 class="font-semibold text-gray-600 mb-3">{{ day }}</h2>
    <div class="flex flex-wrap gap-4">
      <div v-for="routine in groupedRoutines[day]" :key="routine.id"
           class="bg-gray-700 text-white p-4 rounded w-60 shadow">
        <div class="font-semibold">📘 {{ routine.subject?.name }}</div>
        <div class="text-sm mt-1">⏰ {{ routine.start_time }} - {{ routine.end_time }}</div>
        <div class="text-sm">👤 {{ routine.teacher?.first_name }} {{ routine.teacher?.last_name }}</div>
        <div class="text-sm">🏫 Room: {{ routine.class_room || 'N/A' }}</div>
        <div class="text-sm">🏫 Class: {{ routine.school_class?.name || 'N/A' }}</div>
        <div class="text-sm">🔢 Section: {{ routine.section?.name || 'N/A' }}</div>
      </div>
    </div>
  </div>

 
  <!-- Pagination -->
<div class="flex justify-end items-center gap-2 mt-4">
  <button
    @click="loadRoutines(meta.current_page - 1)"
    :disabled="meta.current_page === 1"
    class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50 hover:bg-gray-300"
  >
    Prev
  </button>

  <template v-for="page in meta.last_page" :key="page">
    <button
      @click="loadRoutines(page)"
      :class="[
        'px-3 py-1 rounded hover:bg-gray-300',
        page === meta.current_page ? 'bg-blue-600 text-white' : 'bg-gray-200'
      ]"
    >
      {{ page }}
    </button>
  </template>

  <button
    @click="loadRoutines(meta.current_page + 1)"
    :disabled="meta.current_page === meta.last_page"
    class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50 hover:bg-gray-300"
  >
    Next
  </button>
</div>

</div>
</template>

<style scoped>
.input {
  border: 1px solid #ccc;
  padding: 0.5rem;
  border-radius: 0.25rem;
}
</style>