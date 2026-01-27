<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const sidebarOpen = ref(false)

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const logout = async () => {
  localStorage.clear()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <!-- Navbar -->
    <header class="bg-white shadow flex items-center justify-between px-4 py-3 md:px-6">
      <button @click="toggleSidebar" class="md:hidden text-gray-700 focus:outline-none">
        <!-- Hamburger icon -->
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <h1 class="text-xl font-bold">Master Admin Dashboard</h1>

      <button
        @click="logout"
        class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded"
      >
        Logout
      </button>
    </header>

    <div class="flex flex-1 overflow-hidden">
      <!-- Sidebar -->
      <aside
        :class="[
          'bg-slate-900 text-white w-64 p-4 flex flex-col justify-between fixed inset-y-0 left-0 transform md:relative md:translate-x-0 transition-transform duration-200 ease-in-out z-30',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        ]"
      >
        <div>
          <h2 class="text-xl font-bold mb-6">Master Admin</h2>
          <nav class="flex flex-col gap-2">
            <router-link
              to="/master-dashboard"
              class="block py-2 px-3 rounded hover:bg-indigo-700"
              @click="closeSidebar"
            >
              Dashboard
            </router-link>
            <router-link
              to="/manage-schools"
              class="block py-2 px-3 rounded hover:bg-indigo-700"
              @click="closeSidebar"
            >
              Manage Schools
            </router-link>
            <router-link
              to="/headmaster-dashboard"
              class="block py-2 px-3 rounded hover:bg-indigo-700"
              @click="closeSidebar"
            >
              Headmaster Dashboard
            </router-link>
          </nav>
        </div>

        <button
          @click="logout"
          class="bg-red-500 hover:bg-red-600 text-white py-2 rounded mt-8"
        >
          Logout
        </button>
      </aside>

      <!-- Overlay for small screens when sidebar is open -->
      <div
        v-if="sidebarOpen"
        @click="closeSidebar"
        class="fixed inset-0 bg-black/40 z-20 md:hidden"
      ></div>

      <!-- Main content area -->
      <main class="flex-1 p-6 overflow-auto md:ml-64">
        <slot />
      </main>
    </div>
  </div>
</template>
