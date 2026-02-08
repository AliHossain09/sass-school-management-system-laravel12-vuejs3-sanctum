<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import TeacherSidebar from '../components/sidebar/TeacherSidebar.vue'
import { BsPersonBoundingBox, BsBoxArrowLeft, BsChatDots, BsBell } from 'vue-icons-plus/bs'
import axios from 'axios'

const router = useRouter()

const isSidebarOpen = ref(false)
const isDropdownOpen = ref(false)
const selectedLanguage = ref('en')

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

// Fetch logged-in teacher
const fetchUser = async () => {
  try {
    const res = await axios.get('/api/user', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
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
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    })
    localStorage.removeItem('token')
    router.push('/login')
  } catch (err) {
    console.error(err)
  }
}

onMounted(fetchUser)
</script>

<template>
  <div class="h-screen overflow-hidden">
    <!-- NAVBAR -->
    <header
      class="fixed top-0 left-0 right-0 h-16 bg-blue-900 shadow flex items-center justify-between px-6 z-50"
    >
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
        <button class="text-white relative">
          <BsBell />
          <span
            class="absolute -top-1 -right-1 bg-red-600 text-xs w-4 h-4 rounded-full flex items-center justify-center"
          >
            3
          </span>
        </button>

        <!-- Messages -->
        <button class="text-white relative">
          <BsChatDots />
          <span
            class="absolute -top-1 -right-1 bg-red-600 text-xs w-4 h-4 rounded-full flex items-center justify-center"
          >
            5
          </span>
        </button>

        <!-- Language -->
        <select v-model="selectedLanguage" class="bg-blue-800 text-white px-2 py-1 rounded">
          <option
            v-for="lang in languages"
            :key="lang.code"
            :value="lang.code"
          >
            {{ lang.label }}
          </option>
        </select>

        <!-- Profile -->
        <div class="relative">
          <img
            :src="user.avatar"
            class="w-10 h-10 rounded-full cursor-pointer border-2 border-white"
            @click="isDropdownOpen = !isDropdownOpen"
          />

          <div
            v-if="isDropdownOpen"
            class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg z-50"
          >
            <div class="p-4 border-b">
              <p class="text-gray-900 text-sm">{{ user.email }}</p>
              <p class="font-bold text-gray-900">{{ user.role }}</p>
            </div>

            <ul class="py-1">
              <li>
                <button
                  class="w-full px-4 py-2 flex items-center gap-2 hover:bg-gray-100"
                  @click="router.push('/profile')"
                >
                  <BsPersonBoundingBox class="text-blue-600" />
                  View Profile
                </button>
              </li>

              <li>
                <button
                  class="w-full px-4 py-2 flex items-center gap-2 text-red-600 hover:bg-gray-100"
                  @click="logout"
                >
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
      <TeacherSidebar
        :isOpen="isSidebarOpen"
        @close="isSidebarOpen = false"
      />

      <main
        class="flex-1 p-6 bg-gray-50 overflow-y-auto ml-0 xl:ml-64 h-[calc(100vh-4rem)]"
        :class="isSidebarOpen ? 'pointer-events-none' : ''"
      >
        <slot />
      </main>
    </div>
  </div>
</template>
