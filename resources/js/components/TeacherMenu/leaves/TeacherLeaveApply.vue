<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import TeacherLayout from '../../../layouts/TeacherLayout.vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const loading = ref(false)
const submitting = ref(false)
const leaveTypes = ref<any[]>([])
const balances = ref<any[]>([])

const form = ref({
  leave_type_id: '',
  start_date: '',
  end_date: '',
})

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
})

const selectedType = computed(() => leaveTypes.value.find(t => String(t.id) === String(form.value.leave_type_id)))
const selectedBalance = computed(() => balances.value.find(b => String(b.leave_type?.id) === String(form.value.leave_type_id)))

const duration = computed(() => {
  if (!form.value.start_date) return 0
  const start = new Date(form.value.start_date)
  const end = form.value.end_date ? new Date(form.value.end_date) : start
  const ms = end.getTime() - start.getTime()
  if (Number.isNaN(ms) || ms < 0) return 0
  return Math.floor(ms / (1000 * 60 * 60 * 24)) + 1
})

const load = async () => {
  loading.value = true
  try {
    const [typesRes, balRes] = await Promise.all([
      axios.get('/api/leave-types/available', { headers: authHeader() }),
      axios.get('/api/my/leave-balance', { headers: authHeader() }),
    ])
    leaveTypes.value = typesRes.data?.data || []
    balances.value = balRes.data?.data || []
  } catch (err) {
    console.error(err)
    toast.error('Failed to load leave data')
  } finally {
    loading.value = false
  }
}

const submit = async () => {
  if (!form.value.leave_type_id) return toast.error('Please select a leave type')
  if (!form.value.start_date) return toast.error('Start date is required')
  if (form.value.end_date && duration.value <= 0) return toast.error('End date must be after start date')

  submitting.value = true
  try {
    await axios.post(
      '/api/leave-requests',
      {
        leave_type_id: Number(form.value.leave_type_id),
        start_date: form.value.start_date,
        end_date: form.value.end_date || null,
      },
      { headers: authHeader() }
    )
    toast.success('Leave request submitted')
    form.value = { leave_type_id: '', start_date: '', end_date: '' }
    await load()
  } catch (err: any) {
    const firstError = err.response?.data?.errors ? Object.values(err.response.data.errors)[0]?.[0] : null
    toast.error(firstError || err.response?.data?.message || 'Failed to submit')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  load()
})
</script>

<template>
  <TeacherLayout>
    <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
      <p class="text-gray-700">Teacher ></p>
      <p class="text-gray-700">Leaves ></p>
      <p class="text-gray-700">Apply</p>
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Apply Leave</h1>
        <router-link
          to="/teacher-leave-requests"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
          My Requests
        </router-link>
      </div>

      <div v-if="loading" class="text-center text-gray-600">Loading...</div>

      <div v-else class="bg-white rounded p-6 shadow space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-semibold">Leave Type *</label>
            <select v-model="form.leave_type_id" class="input text-black">
              <option value="" disabled>Select leave type</option>
              <option v-for="t in leaveTypes" :key="t.id" :value="String(t.id)">
                {{ t.name }}
              </option>
            </select>
            <p v-if="selectedType" class="text-xs text-gray-500 mt-1">
              Allowed:
              <span v-if="selectedType.allowed_days">{{ selectedType.allowed_days }} day(s)/year</span>
              <span v-else>Unlimited</span>
              • For: {{ selectedType.applicable_to || 'all' }} • Gender: {{ selectedType.applicable_gender || 'any' }}
            </p>
          </div>

          <div class="bg-gray-50 border rounded p-4">
            <p class="font-semibold mb-1">Balance (this year)</p>
            <p class="text-sm text-gray-700" v-if="selectedBalance">
              Used: {{ selectedBalance.used_days }} day(s)
              <span v-if="selectedBalance.remaining_days !== null"> • Remaining: {{ selectedBalance.remaining_days }} day(s)</span>
              <span v-else> • Remaining: Unlimited</span>
            </p>
            <p v-else class="text-sm text-gray-500">Select a leave type to see balance</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block mb-1 font-semibold">Start Date *</label>
            <input v-model="form.start_date" type="date" class="input text-black" />
          </div>
          <div>
            <label class="block mb-1 font-semibold">End Date (optional)</label>
            <input v-model="form.end_date" type="date" class="input text-black" />
          </div>
          <div class="flex items-end">
            <div class="w-full bg-gray-50 border rounded px-4 py-3">
              <p class="text-sm text-gray-600">Duration</p>
              <p class="text-lg font-bold text-gray-900">{{ duration }} day(s)</p>
            </div>
          </div>
        </div>

        <button
          @click="submit"
          class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700"
          :disabled="submitting"
        >
          {{ submitting ? 'Submitting...' : 'Submit Request' }}
        </button>
      </div>
    </div>
  </TeacherLayout>
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

