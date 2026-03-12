<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const fullPath = computed(() => String(route.fullPath || ''))
</script>

<template>
  <div class="min-h-[60vh] flex items-center justify-center p-6">
    <div class="w-full max-w-xl bg-white shadow rounded p-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Page not found</h1>
      <p class="text-gray-700 mb-4">
        No route matches:
        <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ fullPath }}</span>
      </p>

      <div class="flex flex-wrap gap-2">
        <button
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          @click="router.push('/headmaster-dashboard')"
        >
          Go to Dashboard
        </button>
        <button class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800" @click="router.back()">
          Go Back
        </button>
        <button class="border px-4 py-2 rounded hover:bg-gray-50" @click="router.push('/login')">
          Login
        </button>
      </div>

      <div class="mt-6 text-sm text-gray-600">
        <p class="font-semibold mb-1">Valid leaves routes:</p>
        <ul class="list-disc ml-5">
          <li><span class="font-mono">/leave-types</span></li>
          <li><span class="font-mono">/leave-requests</span></li>
        </ul>
      </div>
    </div>
  </div>
</template>

