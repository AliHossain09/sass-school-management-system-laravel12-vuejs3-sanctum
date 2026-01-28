<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()

const logout = () => {
  localStorage.clear()
  router.push('/login')
}
</script>

<template>
  <aside class="bg-slate-900 text-white w-64 p-4 flex flex-col justify-between">
    <!-- MENU -->
    <nav class="flex flex-col gap-2">
      <router-link
        to="/master-dashboard"
        class="px-3 py-2 rounded hover:bg-indigo-700"
        active-class="bg-indigo-600"
      >
        Dashboard
      </router-link>

      <router-link
        to="/manage-schools"
        class="px-3 py-2 rounded hover:bg-indigo-700"
        active-class="bg-indigo-600"
      >
        Manage Schools
      </router-link>
    </nav>

    <!-- LOGOUT -->
    <button
      @click="logout"
      class="bg-red-500 hover:bg-red-600 text-white py-2 rounded mt-6"
    >
      Logout
    </button>
  </aside>
</template>
