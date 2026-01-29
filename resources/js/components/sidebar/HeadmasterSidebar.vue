<script setup>
import { useRouter } from 'vue-router'

const props = defineProps({
  isOpen: Boolean
})

const emit = defineEmits(['close'])

const router = useRouter()

const logout = () => {
  localStorage.clear()
  router.push('/login')
}
</script>


<template>
  <!-- Overlay (mobile only) -->
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/40 z-40 xl:hidden"
    @click="$emit('close')"
  ></div>

  <!-- Sidebar -->
  <aside
    class="bg-blue-900 text-white w-64 p-4 flex flex-col justify-start xl:justify-between
           fixed xl:static z-50 h-screen transition-transform duration-300"
    :class="{
      '-translate-x-full xl:translate-x-0': !isOpen,
      'translate-x-0': isOpen
    }"
  >
    <!-- Close button (mobile only) -->
    <button
      class="xl:hidden text-right text-xl mb-4"
      @click="$emit('close')"
    >
      ✕
    </button>

    <!-- MENU -->
    <nav class="flex flex-col gap-2">
      <router-link
        to="/headmaster-dashboard"
        class="px-3 py-2 rounded hover:bg-indigo-700"
        active-class="bg-indigo-600"
        @click="$emit('close')"
      >
        Dashboard
      </router-link>
      
      <router-link
        to="/manage-teachers"
        class="px-3 py-2 rounded hover:bg-indigo-700"
        active-class="bg-indigo-600"
        @click="$emit('close')"
      >
        Teachers Info
      </router-link>

      <router-link
        to="/manage-students"
        class="px-3 py-2 rounded hover:bg-indigo-700"
        active-class="bg-indigo-600"
        @click="$emit('close')"
      >
        Students Info
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
