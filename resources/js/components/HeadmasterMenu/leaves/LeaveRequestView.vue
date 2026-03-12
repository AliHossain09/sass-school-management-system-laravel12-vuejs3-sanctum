<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const loading = ref(false)
const saving = ref(false)
const leaveRequest = ref<any | null>(null)

const form = ref({
  status: 'pending',
  rejection_note: '',
})

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
})

const id = computed(() => Number(route.params.id))

const dateOnly = (v: any) => {
  if (!v) return ''
  return String(v).slice(0, 10)
}

const load = async () => {
  loading.value = true
  try {
    const res = await axios.get(`/api/leave-requests/${id.value}`, { headers: authHeader() })
    leaveRequest.value = res.data.data
    form.value.status = leaveRequest.value.status
    form.value.rejection_note = leaveRequest.value.rejection_note || ''
  } catch (err) {
    console.error(err)
    toast.error('Failed to load request')
  } finally {
    loading.value = false
  }
}

const submit = async () => {
  saving.value = true
  try {
    await axios.patch(
      `/api/leave-requests/${id.value}/status`,
      {
        status: form.value.status,
        rejection_note: form.value.status === 'rejected' ? (form.value.rejection_note || null) : null,
      },
      { headers: authHeader() }
    )
    toast.success('Status updated')
    await load()
  } catch (err: any) {
    const firstError =
      err.response?.data?.errors ? Object.values(err.response.data.errors)[0]?.[0] : null
    toast.error(firstError || err.response?.data?.message || 'Failed to update status')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  load()
})
</script>

<template>
  <HeadmasterLayout>
    <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
      <p class="text-gray-700">Headmaster ></p>
      <p class="text-gray-700">Leaves ></p>
      <p class="text-gray-700">View Request</p>
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Leave Request Details</h1>
        <button @click="router.push('/leave-requests')" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">
          Back
        </button>
      </div>

      <div v-if="loading" class="text-center text-gray-600">Loading...</div>

      <div v-if="leaveRequest && !loading" class="bg-white rounded p-5 shadow space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div><span class="font-semibold">Apply Date:</span> {{ dateOnly(leaveRequest.applied_at) }}</div>
          <div><span class="font-semibold">Name:</span> {{ leaveRequest.user?.name }}</div>
          <div>
            <span class="font-semibold">User Type:</span>
            {{ leaveRequest.user?.role === 'teacher' ? 'Teacher' : leaveRequest.user?.role === 'student' ? 'Student' : leaveRequest.user?.role }}
          </div>
          <div><span class="font-semibold">Leave Type:</span> {{ leaveRequest.leave_type?.name }}</div>
          <div>
            <span class="font-semibold">Date:</span>
            <span v-if="leaveRequest.end_date">{{ dateOnly(leaveRequest.start_date) }} - {{ dateOnly(leaveRequest.end_date) }}</span>
            <span v-else>{{ dateOnly(leaveRequest.start_date) }}</span>
          </div>
          <div><span class="font-semibold">Duration:</span> {{ leaveRequest.duration }} day(s)</div>
          <div><span class="font-semibold">Current Status:</span> {{ leaveRequest.status }}</div>
        </div>

        <hr />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
          <div>
            <label class="block mb-1 font-semibold">Set Status</label>
            <select v-model="form.status" class="input text-black">
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>

          <div v-if="form.status === 'rejected'">
            <label class="block mb-1 font-semibold">Reject Note (Optional)</label>
            <textarea v-model="form.rejection_note" class="input text-black" rows="3" placeholder="Write note..."></textarea>
          </div>
        </div>

        <button @click="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700" :disabled="saving">
          {{ saving ? 'Saving...' : 'Update Status' }}
        </button>
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
</style>
