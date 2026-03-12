<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import HeadmasterSidebar from '../components/sidebar/HeadmasterSidebar.vue'
import { BsPersonBoundingBox, BsBoxArrowLeft, BsChatDots, BsBell } from 'vue-icons-plus/bs'
import axios from 'axios'

const isSidebarOpen = ref(false)
const isDropdownOpen = ref(false)
const isNotificationOpen = ref(false)
const selectedLanguage = ref('en')
const router = useRouter()
const leaveNotifications = ref([])

// User info state
const user = ref({
  name: '',
  email: '',
  role: '',
  avatar: '' // if have not profile image then use placeholder image
})

// Language options
const languages = [
  { code: 'en', label: 'English' },
  { code: 'bn', label: 'Bangla' },
  { code: 'es', label: 'Spanish' },
]

// Fetch logged-in user info
const fetchUser = async () => {
  try {
    const res = await axios.get('/api/user', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    })

    user.value.name = res.data.name
    user.value.email = res.data.email
    user.value.role = res.data.role || 'Headmaster'
    // if user doesn't have avatar, use default image
    user.value.avatar = res.data.avatar || 'https://i.pravatar.cc/40'
  } catch (err) {
    console.error('User fetch failed', err)
    router.push('/login')
  }
}

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
})

const unreadLeaveCount = () => leaveNotifications.value.filter(n => !n.read_at).length

const fetchLeaveNotifications = async () => {
  try {
    const res = await axios.get('/api/notifications', {
      headers: authHeader(),
      params: { kind: 'leave_request_created' },
    })
    leaveNotifications.value = res.data?.data || []
  } catch (err) {
    console.error(err)
  }
}

const toggleNotifications = async () => {
  isNotificationOpen.value = !isNotificationOpen.value
  if (isNotificationOpen.value) {
    await fetchLeaveNotifications()
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
    const id = notification?.data?.leave_request?.id
    if (id) router.push(`/leave-requests/${id}`)
  }
}

// Logout
const logout = async () => {
  try {
    await axios.post('/api/logout', {}, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    })
    localStorage.removeItem('token')
    router.push('/login')
  } catch (err) {
    console.error('Logout failed', err)
  }
}

// if Component mounted  user fetching
onMounted(() => {
  fetchUser()
  fetchLeaveNotifications()
})
</script>

<template>
  <div class="h-screen overflow-hidden">
    <!-- Navbar -->
    <header class="fixed top-0 left-0 right-0 h-16 bg-blue-900 shadow flex items-center justify-between px-6 z-50">
      <!-- Sidebar toggle + Title -->
      <div class="flex items-center gap-3">
        <button class="xl:hidden text-2xl text-white" @click="isSidebarOpen = true">☰</button>
        <h1 class="font-bold text-2xl text-white">Headmaster Dashboard</h1>
      </div>

      <!-- Right: notifications, language, profile -->
      <div class="flex items-center gap-4">
        <!-- Notifications -->
        <div class="relative">
          <button class="text-white relative" @click="toggleNotifications">
            <BsBell />
            <span
              v-if="unreadLeaveCount() > 0"
              class="absolute -top-1 -right-1 bg-red-600 text-xs min-w-4 h-4 px-1 rounded-full flex items-center justify-center"
            >
              {{ unreadLeaveCount() > 9 ? '9+' : unreadLeaveCount() }}
            </span>
          </button>

          <div
            v-if="isNotificationOpen"
            class="absolute right-0 mt-3 w-96 bg-white text-gray-800 rounded-md shadow-lg border z-50 max-h-96 overflow-y-auto"
          >
            <div class="px-4 py-3 border-b font-semibold">Leave Notifications</div>

            <button
              v-for="n in leaveNotifications"
              :key="n.id"
              @click="openLeaveFromNotification(n)"
              class="w-full text-left px-4 py-3 border-b last:border-b-0 hover:bg-gray-50"
            >
              <p :class="n.read_at ? 'text-gray-600' : 'font-semibold text-gray-900'">
                New leave request: {{ n.data?.leave_request?.user_name || 'User' }}
              </p>
              <p class="text-xs text-gray-500 mt-1">
                {{ n.data?.leave_request?.leave_type_name || 'Leave' }} • {{ n.data?.leave_request?.start_date }}
              </p>
            </button>

            <p v-if="leaveNotifications.length === 0" class="px-4 py-3 text-sm text-gray-500">
              No notifications yet
            </p>
          </div>
        </div>

        <!-- Messages -->
        <button class="text-white relative">
            <BsChatDots />
          <span class="absolute -top-1 -right-1 bg-red-600 text-xs w-4 h-4 rounded-full flex items-center justify-center">5</span>
        </button>

        <!-- Language selector -->
        <select v-model="selectedLanguage" class="bg-blue-800 text-white px-2 py-1 rounded">
          <option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.label }}</option>
        </select>

        <!-- Profile dropdown -->
        <div class="relative">
          <img
            @click="isDropdownOpen = !isDropdownOpen"
            :src="user.avatar"
            alt="avatar"
            class="w-10 h-10 rounded-full cursor-pointer border-2 border-white"
          />

          <div v-if="isDropdownOpen" class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg z-50">
            <div class="p-4 border-b">
              <p class="text-gray-900">{{ user.email }}</p>
              <p class="font-bold text-md text-gray-900">{{ user.role }}</p>
            </div>

            <ul class="py-1">
              <li>
                <button class="text-md text-gray-900 w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-2"
                        @click="router.push('/profile')">
                  <BsPersonBoundingBox class="w-5 h-5 text-blue-600"/> 
                  View Profile
                </button>
              </li>
              <li>
                <button class="text-md w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 flex items-center gap-2"
                        @click="logout">
                  <BsBoxArrowLeft class="w-5 h-5"/>
                  Logout
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </header>

    <!-- Body -->
    <div class="flex pt-16">
      <HeadmasterSidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false"/>
      <main :class="isSidebarOpen ? 'pointer-events-none' : ''" class="flex-1 p-6 bg-gray-50 overflow-y-auto ml-0 xl:ml-64 h-[calc(100vh-4rem)]">
        <slot/>
      </main>
    </div>
  </div>
</template>
