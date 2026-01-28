<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const sidebarOpen = ref(false)

const toggleSidebar = () => sidebarOpen.value = !sidebarOpen.value
const logout = () => {
  localStorage.clear()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <!-- Navbar -->
    <header class="bg-white shadow flex justify-between px-4 py-3">
      <button @click="toggleSidebar" class="md:hidden">☰</button>
      <h1 class="font-bold">Headmaster</h1>
      <button @click="logout" class="bg-red-500 text-white px-3 py-1 rounded">Logout</button>
    </header>

    <div class="flex flex-1">
      <!-- Sidebar -->
      <aside
        :class="[
          'bg-indigo-900 text-white w-64 p-4 fixed md:relative',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        ]"
      >
        <nav class="flex flex-col gap-2">
          <router-link to="/headmaster-dashboard">Dashboard</router-link>
          <router-link to="/teachers">Teachers</router-link>
          <router-link to="/students">Students</router-link>
        </nav>
      </aside>

      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
