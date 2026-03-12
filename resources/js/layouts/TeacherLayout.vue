<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import TeacherSidebar from '../components/sidebar/TeacherSidebar.vue'
import { BsPersonBoundingBox, BsBoxArrowLeft, BsChatDots, BsBell } from 'vue-icons-plus/bs'
import axios from 'axios'

const router = useRouter()

const isSidebarOpen = ref(false)
const isDropdownOpen = ref(false)
const isNotificationOpen = ref(false)
const selectedLanguage = ref('en')
const eventNotifications = ref([])
const leaveNotifications = ref([])

// User info
const user = ref({
  name: '',
  email: '',
  role: '',
  avatar: ''
})

// Language options
const languages = [
  { code: 'en', label: 'English' },
  { code: 'bn', label: 'Bangla' },
  { code: 'es', label: 'Spanish' },
]

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
})

const unreadCount = computed(() => {
  const eventUnread = eventNotifications.value.filter(n => !n.is_read).length
  const leaveUnread = leaveNotifications.value.filter(n => !n.read_at).length
  return eventUnread + leaveUnread
})

const fetchEventNotifications = async () => {
  try {
    const res = await axios.get('/api/event-notifications', { headers: authHeader() })
    eventNotifications.value = res.data?.data || []
  } catch (err) {
    console.error(err)
  }
}

const fetchLeaveNotifications = async () => {
  try {
    const res = await axios.get('/api/notifications', {
      headers: authHeader(),
      params: { kind: 'leave_request_status_updated' },
    })
    leaveNotifications.value = res.data?.data || []
  } catch (err) {
    console.error(err)
  }
}

const toggleNotifications = async () => {
  isNotificationOpen.value = !isNotificationOpen.value
  if (isNotificationOpen.value) {
    await Promise.all([fetchEventNotifications(), fetchLeaveNotifications()])
  }
}

const openEventFromNotification = async (notification) => {
  try {
    if (!notification.is_read) {
      await axios.post(`/api/event-notifications/${notification.id}/read`, {}, { headers: authHeader() })
      notification.is_read = true
    }
  } catch (err) {
    console.error(err)
  } finally {
    isNotificationOpen.value = false
    router.push('/events-calender-teacher')
  }
}

const openLeaveFromNotification = async (notification) => {
  try {
    if (!notification.read_at) {
      await axios.post(`/api/notifications/${notification.id}/read`, {}, { headers: authHeader() })
      notification.read_at = new Date().toISOString()
    }
  } catch (err) {
    console.error(err)
  } finally {
    isNotificationOpen.value = false
    router.push('/teacher-leave-requests')
  }
}

// Fetch logged-in teacher
const fetchUser = async () => {
  try {
    const res = await axios.get('/api/user', {
      headers: authHeader(),
    })

    user.value.name = res.data.name
    user.value.email = res.data.email
    user.value.role = res.data.role || 'Teacher'
    user.value.avatar = res.data.avatar || 'https://i.pravatar.cc/40'
  } catch (err) {
    console.error(err)
    router.push('/login')
  }
}

// Logout
const logout = async () => {
  try {
    await axios.post('/api/logout', {}, {
      headers: authHeader(),
    })
    localStorage.removeItem('token')
    router.push('/login')
  } catch (err) {
    console.error(err)
  }
}

onMounted(async () => {
  await Promise.all([fetchUser(), fetchEventNotifications(), fetchLeaveNotifications()])
})
</script>

<template>
  <div class="h-screen overflow-hidden">
    <!-- NAVBAR -->
    <header class="fixed top-0 left-0 right-0 h-16 bg-blue-900 shadow flex items-center justify-between px-6 z-50">
      <div class="flex items-center gap-3">
        <button class="xl:hidden text-2xl text-white" @click="isSidebarOpen = true">
          ☰
        </button>
        <h1 class="font-bold text-2xl text-white">
          Teacher Dashboard
        </h1>
      </div>

      <div class="flex items-center gap-4">
        <!-- Notifications -->
        <div class="relative">
          <button class="text-white relative" @click="toggleNotifications">
            <BsBell />
            <span
              v-if="unreadCount > 0"
              class="absolute -top-1 -right-1 bg-red-600 text-xs min-w-4 h-4 px-1 rounded-full flex items-center justify-center">
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </button>

          <div
            v-if="isNotificationOpen"
            class="absolute right-0 mt-3 w-80 bg-white text-gray-800 rounded-md shadow-lg border z-50 max-h-96 overflow-y-auto">
            <div class="px-4 py-3 border-b font-semibold">Notifications</div>

            <div class="px-4 py-2 text-xs font-semibold text-gray-500 bg-gray-50">Leave Updates</div>
            <button
              v-for="n in leaveNotifications"
              :key="n.id"
              @click="openLeaveFromNotification(n)"
              class="w-full text-left px-4 py-3 border-b last:border-b-0 hover:bg-gray-50"
            >
              <p :class="n.read_at ? 'text-gray-600' : 'font-semibold text-gray-900'">
                Leave status: {{ n.data?.leave_request?.status || 'updated' }}
              </p>
              <p class="text-xs text-gray-500 mt-1">
                {{ n.data?.leave_request?.leave_type_name || 'Leave' }}
              </p>
            </button>
            <p v-if="leaveNotifications.length === 0" class="px-4 py-3 text-sm text-gray-500">
              No leave updates
            </p>

            <div class="px-4 py-2 text-xs font-semibold text-gray-500 bg-gray-50 border-t">Event Notifications</div>

            <button
              v-for="n in eventNotifications"
              :key="n.id"
              @click="openEventFromNotification(n)"
              class="w-full text-left px-4 py-3 border-b last:border-b-0 hover:bg-gray-50"
            >
              <p :class="n.is_read ? 'text-gray-600' : 'font-semibold text-gray-900'">
                {{ n.event?.title || 'New event added' }}
              </p>
              <p class="text-xs text-gray-500 mt-1">
                {{ n.event?.start_date }} - {{ n.event?.end_date || n.event?.start_date }}
              </p>
            </button>

            <p v-if="eventNotifications.length === 0" class="px-4 py-3 text-sm text-gray-500">
              No notifications yet
            </p>
          </div>
        </div>

        <!-- Messages -->
        <button class="text-white relative">
          <BsChatDots />
          <span
            class="absolute -top-1 -right-1 bg-red-600 text-xs w-4 h-4 rounded-full flex items-center justify-center">
            5
          </span>
        </button>

        <!-- Language -->
        <select v-model="selectedLanguage" class="bg-blue-800 text-white px-2 py-1 rounded">
          <option v-for="lang in languages" :key="lang.code" :value="lang.code">
            {{ lang.label }}
          </option>
        </select>

        <!-- Profile -->
        <div class="relative">
          <img :src="user.avatar" class="w-10 h-10 rounded-full cursor-pointer border-2 border-white"
            @click="isDropdownOpen = !isDropdownOpen" />

          <div v-if="isDropdownOpen" class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg z-50">
            <div class="p-4 border-b">
              <p class="text-gray-900 text-sm">{{ user.email }}</p>
              <p class="font-bold text-gray-900">{{ user.role }}</p>
            </div>

            <ul class="py-1">
              <li>
                <button class="w-full px-4 py-2 flex items-center gap-2 hover:bg-gray-100"
                  @click="router.push('/profile')">
                  <BsPersonBoundingBox class="text-blue-600" />
                  View Profile
                </button>
              </li>

              <li>
                <button class="w-full px-4 py-2 flex items-center gap-2 text-red-600 hover:bg-gray-100" @click="logout">
                  <BsBoxArrowLeft />
                  Logout
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </header>

    <!-- BODY -->
    <div class="flex pt-16">
      <TeacherSidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

      <main :class="isSidebarOpen ? 'pointer-events-none' : ''"
        class="flex-1 p-6 bg-gray-50 overflow-y-auto ml-0 xl:ml-64 h-[calc(100vh-4rem)]">
        <slot />
      </main>

    </div>
  </div>
</template>
