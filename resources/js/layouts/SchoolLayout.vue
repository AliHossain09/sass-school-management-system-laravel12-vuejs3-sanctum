<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const sidebarOpen = ref(false)

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const logout = () => {
  localStorage.clear()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <!-- Navbar/Header -->
    <header class="bg-white shadow flex items-center justify-between px-4 py-3 md:px-6">
      <div class="flex items-center gap-4">
        <button
          @click="toggleSidebar"
          class="md:hidden text-gray-700 focus:outline-none"
          aria-label="Toggle sidebar"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <h1 class="text-xl font-bold">School Dashboard</h1>
      </div>

      <div class="flex items-center gap-4">
        <span class="hidden md:inline-block">Welcome, Admin</span>
        <button
          @click="logout"
          class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded"
        >
          Logout
        </button>
      </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
      <!-- Sidebar -->
      <aside
        :class="[
          'bg-blue-900 text-white w-64 p-4 flex flex-col justify-between fixed inset-y-0 left-0 transform md:relative md:translate-x-0 transition-transform duration-200 ease-in-out z-30',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        ]"
      >
        <nav class="flex flex-col gap-3">
          <router-link
            to="/school-dashboard"
            class="block py-2 px-3 rounded hover:bg-blue-700"
            @click="sidebarOpen = false"
          >
            Dashboard
          </router-link>
          <router-link
            to="/students"
            class="block py-2 px-3 rounded hover:bg-blue-700"
            @click="sidebarOpen = false"
          >
            Students
          </router-link>
          <router-link
            to="/teachers"
            class="block py-2 px-3 rounded hover:bg-blue-700"
            @click="sidebarOpen = false"
          >
            Teachers
          </router-link>
          <router-link
            to="/classes"
            class="block py-2 px-3 rounded hover:bg-blue-700"
            @click="sidebarOpen = false"
          >
            Classes
          </router-link>
          <router-link
            to="/attendance"
            class="block py-2 px-3 rounded hover:bg-blue-700"
            @click="sidebarOpen = false"
          >
            Attendance
          </router-link>
          <router-link
            to="/exams"
            class="block py-2 px-3 rounded hover:bg-blue-700"
            @click="sidebarOpen = false"
          >
            Exams & Results
          </router-link>
          <router-link
            to="/settings"
            class="block py-2 px-3 rounded hover:bg-blue-700"
            @click="sidebarOpen = false"
          >
            Settings
          </router-link>
        </nav>

        <button
          @click="logout"
          class="bg-red-600 hover:bg-red-700 py-2 rounded mt-8"
        >
          Logout
        </button>
      </aside>

      <!-- Overlay -->
      <div
        v-if="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/40 z-20 md:hidden"
      ></div>

      <!-- Main content -->
      <main class="flex-1 p-6 overflow-auto md:ml-64">
        <slot />
      </main>
    </div>
  </div>
</template>
