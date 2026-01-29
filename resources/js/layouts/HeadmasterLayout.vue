<script setup>
import HeadmasterSidebar from '../components/sidebar/HeadmasterSidebar.vue'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isSidebarOpen = ref(false)

const logout = () => {
  localStorage.clear()
  router.push('/login')
}
</script>


<template>
  <div class="min-h-screen flex flex-col">
    <!-- NAVBAR -->
    <header class="bg-blue-900 shadow flex items-center justify-between px-6 py-3">
      <div class="flex items-center gap-3">
        <!--  Burger menu (only small screen) -->
        <button
          class="xl:hidden text-2xl text-white"
          @click="isSidebarOpen = true"
        >
          ☰
        </button>

        <h1 class="font-bold text-lg text-white">Headmaster Admin</h1>
      </div>

      <button
        @click="logout"
        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded"
      >
        Logout
      </button>
    </header>

    <!-- BODY -->
    <div class="flex flex-1">
      <!-- Sidebar -->
      <HeadmasterSidebar
        :isOpen="isSidebarOpen"
        @close="isSidebarOpen = false"
      />

      <!-- Main content -->
      <main class="flex-1 p-6 bg-gray-50 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>
