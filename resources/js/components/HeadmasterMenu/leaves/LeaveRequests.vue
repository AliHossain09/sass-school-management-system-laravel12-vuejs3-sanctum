<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import Swal from 'sweetalert2'
import axios from 'axios'

const router = useRouter()
const toast = useToast()

const leaveRequests = ref<any[]>([])
const meta = ref({ current_page: 1, from: 0, to: 0, total: 0, last_page: 0, per_page: 10 })
const loading = ref(false)

const search = ref('')
const status = ref('')
let searchTimeout: any = null

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
})

const dateOnly = (v: any) => {
  if (!v) return ''
  return String(v).slice(0, 10)
}

const loadLeaveRequests = async (page = 1) => {
  loading.value = true
  try {
    const res = await axios.get('/api/leave-requests', {
      headers: authHeader(),
      params: { page, search: search.value, status: status.value || undefined },
    })
    leaveRequests.value = res.data.data
    meta.value = res.data.meta
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadLeaveRequests(1), 400)
})

watch(status, () => {
  loadLeaveRequests(1)
})

onMounted(() => {
  loadLeaveRequests()
})

const deleteLeaveRequest = async (id: number) => {
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
    await axios.delete(`/api/leave-requests/${id}`, { headers: authHeader() })
    toast.success('Leave request deleted')
    await loadLeaveRequests(meta.value.current_page)
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
      <p class="text-gray-700">Leave Request</p>
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">
      <div class="flex justify-between items-center mb-6 gap-3">
        <h1 class="text-2xl font-bold mb-1">Leave Requests</h1>

        <input
          v-model="search"
          type="text"
          placeholder="Search by name or leave type..."
          class="border-b rounded-md border-blue-400 px-4 py-2 w-72 focus:outline-none"
        />

        <select v-model="status" class="border rounded-md px-3 py-2 text-black">
          <option value="">All</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>

      <div v-if="loading" class="text-center text-gray-600">Loading...</div>

      <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-200 bg-white rounded-md">
          <thead class="bg-blue-900 text-white">
            <tr>
              <th class="border border-gray-200 px-4 py-2 text-left">SL</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Apply Date</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Name</th>
              <th class="border border-gray-200 px-4 py-2 text-left">User Type</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Leave Type</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Date</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Duration</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Status</th>
              <th class="border border-gray-200 px-4 py-2 text-left">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, i) in leaveRequests" :key="row.id" class="hover:bg-gray-50">
              <td class="border border-gray-200 px-4 py-2">{{ (meta.from || 1) + i }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ dateOnly(row.applied_at) }}</td>
              <td class="border border-gray-200 px-4 py-2">{{ row.user?.name }}</td>
              <td class="border border-gray-200 px-4 py-2">
                {{ row.user_type === 'teacher' ? 'Teacher' : row.user_type === 'student' ? 'Student' : row.user_type }}
              </td>
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
              <td class="border border-gray-200 px-4 py-2 space-x-2">
                <button
                  @click="router.push(`/leave-requests/${row.id}`)"
                  class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700"
                >
                  View
                </button>
                <button
                  @click="deleteLeaveRequest(row.id)"
                  class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="!loading && leaveRequests.length === 0">
              <td colspan="9" class="text-center py-6 text-gray-600">No leave requests found</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-between items-center mt-4">
        <div class="text-gray-600">Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries</div>
        <div class="flex gap-1">
          <button
            :disabled="meta.current_page === 1"
            @click="loadLeaveRequests(meta.current_page - 1)"
            class="px-2 py-1 border rounded disabled:opacity-50"
          >
            &laquo;
          </button>
          <button
            v-for="page in meta.last_page"
            :key="page"
            @click="loadLeaveRequests(page)"
            :class="['px-2 py-1 border rounded', page === meta.current_page ? 'bg-blue-600 text-white' : '']"
          >
            {{ page }}
          </button>
          <button
            :disabled="meta.current_page === meta.last_page"
            @click="loadLeaveRequests(meta.current_page + 1)"
            class="px-2 py-1 border rounded disabled:opacity-50"
          >
            &raquo;
          </button>
        </div>
      </div>
    </div>
  </HeadmasterLayout>
</template>

