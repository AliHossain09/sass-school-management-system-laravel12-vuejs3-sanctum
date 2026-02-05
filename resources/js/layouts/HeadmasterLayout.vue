<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import HeadmasterSidebar from '../components/sidebar/HeadmasterSidebar.vue'

const isSidebarOpen = ref(false)
const router = useRouter()

const logout = () => {
  localStorage.clear()
  router.push('/login')
}
</script>

<template>
  <div class="h-screen overflow-hidden">
    <!-- NAVBAR -->
    <header
      class="fixed top-0 left-0 right-0 h-16 bg-blue-900 shadow flex items-center justify-between px-6 z-50"
    >
      <div class="flex items-center gap-3">
        <button
          class="xl:hidden text-2xl text-white"
          @click="isSidebarOpen = true"
        >
          ☰
        </button>

        <h1 class="font-bold text-2xl text-white">
          Headmaster Dashboard
        </h1>
      </div>

      <button
        @click="logout"
        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded"
      >
        Logout
      </button>
    </header>

    <!-- BODY -->
    <div class="flex pt-16">
      <!-- SIDEBAR -->
      <HeadmasterSidebar
        :isOpen="isSidebarOpen"
        @close="isSidebarOpen = false"
      />

      <!-- MAIN CONTENT -->
      <main
        :class="isSidebarOpen ? 'pointer-events-none' : ''"
        class="flex-1 p-6 bg-gray-50 overflow-y-auto ml-0 xl:ml-64 h-[calc(100vh-4rem)]"
      >
        <slot />
      </main>
    </div>
  </div>
</template>


