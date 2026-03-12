<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import TeacherLayout from '../../../layouts/TeacherLayout.vue'
import axios from 'axios'

const loading = ref(false)
const rows = ref<any[]>([])
const meta = ref({ current_page: 1, from: 0, to: 0, total: 0, last_page: 0, per_page: 10 })
const status = ref('')

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
})

const dateOnly = (v: any) => {
  if (!v) return ''
  return String(v).slice(0, 10)
}

const load = async (page = 1) => {
  loading.value = true
  try {
    const res = await axios.get('/api/my/leave-requests', {
      headers: authHeader(),
      params: { page, status: status.value || undefined },
    })
    rows.value = res.data?.data || []
    meta.value = res.data?.meta || meta.value
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

watch(status, () => load(1))

onMounted(() => {
  load()
})
</script>

<template>
  <TeacherLayout>
    <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
      <p class="text-gray-700">Teacher ></p>
      <p class="text-gray-700">Leaves ></p>
      <p class="text-gray-700">My Requests</p>
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">
      <div class="flex items-center justify-between mb-6 gap-3">
        <h1 class="text-2xl font-bold">My Leave Requests</h1>

        <div class="flex items-center gap-2">
          <select v-model="status" class="border rounded-md px-3 py-2 text-black">
            <option value="">All</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>

          <router-link
            to="/teacher-leave-apply"
            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
          >
            + Apply Leave
          </router-link>
        </div>
      </div>

      <div v-if="loading" class="text-center text-gray-600">Loading...</div>

      <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-200 bg-white rounded-md">
          <thead class="bg-blue-900 text-white">
            <tr>
              <th class="border border-gray-200 px-4 py-2 text-left">SL</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Apply Date</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Leave Type</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Date</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Duration</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Status</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Note</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, i) in rows" :key="row.id" class="hover:bg-gray-50">
              <td class="border border-gray-200 px-4 py-2">{{ (meta.from || 1) + i }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ dateOnly(row.applied_at) }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ row.leave_type?.name }}</td>
              <td class="border border-gray-200 px-4 py-2">
                <span v-if="row.end_date">{{ dateOnly(row.start_date) }} - {{ dateOnly(row.end_date) }}</span>
                <span v-else>{{ dateOnly(row.start_date) }}</span>
              </td>
              <td class="border border-gray-200 px-4 py-2">{{ row.duration }} day(s)</td>
              <td class="border border-gray-200 px-4 py-2">
                <span
                  :class="
                    row.status === 'approved'
                      ? 'bg-green-100 text-green-700'
                      : row.status === 'rejected'
                        ? 'bg-red-100 text-red-700'
                        : 'bg-yellow-100 text-yellow-700'
                  "
                  class="px-2 py-1 rounded text-sm"
                >
                  {{ row.status }}
                </span>
              </td>
              <td class="border border-gray-200 px-4 py-2">
                <span v-if="row.status === 'rejected'">{{ row.rejection_note || '-' }}</span>
                <span v-else class="text-gray-500">-</span>
              </td>
            </tr>
            <tr v-if="!loading && rows.length === 0">
              <td colspan="7" class="text-center py-6 text-gray-600">No leave requests found</td>
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
    </div>
  </TeacherLayout>
</template>

