<template>
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <StudentSidebar
      :isOpen="isSidebarOpen"
      @close="isSidebarOpen = false"
      class="flex-none"
    />

    <!-- Main content area -->
    <div class="flex-1 flex flex-col overflow-hidden">
      
      <!-- NAVBAR -->
      <header class="bg-blue-900 shadow flex items-center justify-between px-6 py-3 flex-none sticky top-0 z-20">
        <div class="flex items-center gap-3">
          <!-- Burger menu (only small screen) -->
          <button
            class="xl:hidden text-2xl text-white"
            @click="isSidebarOpen = true"
          >
            ☰
          </button>

          <h1 class="font-bold text-lg text-white">Student Dashboard</h1>
        </div>

        <button
          @click="logout"
          class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded"
        >
          Logout
        </button>
      </header>

      <!-- Scrollable content -->
      <main class="flex-1 overflow-auto p-6 bg-gray-50">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import StudentSidebar from '../components/sidebar/StudentSidebar.vue'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isSidebarOpen = ref(false)

const logout = () => {
  localStorage.clear()
  router.push('/login')
}
</script>
