<script setup>
import MasterSidebar from '@/components/sidebar/MasterSidebar.vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const logout = () => {
  localStorage.clear()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <!--  NAVBAR -->
    <header class="bg-white shadow flex items-center justify-between px-6 py-3">
      <div class="flex items-center gap-2">
        <!-- <img src="@/assets/logo.png" class="h-8 w-8" /> -->
        <h1 class="font-bold text-lg">Master Admin</h1>
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
      <MasterSidebar />
      <main class="flex-1 p-6 bg-gray-50 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>
