<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

defineProps({
  isOpen: Boolean
})

defineEmits(['close'])

const router = useRouter()

const showTeacherMenu = ref(false)
const showStudentMenu = ref(false)

const logout = () => {
  localStorage.clear()
  router.push('/login')
}
</script>



<template>
  <!-- Overlay -->
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/40 z-40 xl:hidden"
    @click="$emit('close')"
  ></div>

  <aside
   class="bg-blue-900 text-white w-64 p-4 flex flex-col justify-start xl:justify-between
           fixed xl:static z-50 h-screen transition-transform duration-300"
    :class="{
      '-translate-x-full xl:translate-x-0': !isOpen,
      'translate-x-0': isOpen
    }"
  >
    <!-- Close (mobile) -->
    <button class="xl:hidden text-right text-xl mb-4" @click="$emit('close')">
      ✕
    </button>

    <nav class="flex flex-col gap-2">

      <!-- Dashboard -->
      <router-link
        to="/headmaster-dashboard"
        class="px-3 py-2 rounded hover:bg-indigo-700"
        active-class="bg-indigo-600"
        @click="$emit('close')"
      >
        Dashboard
      </router-link>

      <!-- Classes -->
      <router-link
        to="/school-classes"
        class="px-3 py-2 rounded hover:bg-indigo-700"
        active-class="bg-indigo-600"
        @click="$emit('close')"
      >
        Classes
      </router-link>

      <!-- STUDENT INFO -->
      <button
        class="px-3 py-2 rounded hover:bg-indigo-700 text-left flex justify-between items-center"
        @click="showStudentMenu = !showStudentMenu"
      >
        Student Info
        <span>{{ showStudentMenu ? '▲' : '▼' }}</span>
      </button>

      <div v-if="showStudentMenu" class="ml-4 flex flex-col gap-1">
        <router-link
          to="/students-list"
          class="px-3 py-2 rounded hover:bg-indigo-600 text-sm"
          @click="$emit('close')"
        >
          Student List
        </router-link>

        <router-link
          to="/student-admission"
          class="px-3 py-2 rounded hover:bg-indigo-600 text-sm"
          @click="$emit('close')"
        >
          Student Admission
        </router-link>

        <router-link
          to="/student-attendance"
          class="px-3 py-2 rounded hover:bg-indigo-600 text-sm"
          @click="$emit('close')"
        >
          Student Attendance
        </router-link>
      </div>

      <!-- TEACHER INFO -->
      <button
        class="px-3 py-2 rounded hover:bg-indigo-700 text-left flex justify-between items-center"
        @click="showTeacherMenu = !showTeacherMenu"
      >
        Teacher Info
        <span>{{ showTeacherMenu ? '▲' : '▼' }}</span>
      </button>
      <div v-if="showTeacherMenu" class="ml-4 flex flex-col gap-1">
        <router-link
          to="/teachers-list"
          class="px-3 py-2 rounded hover:bg-indigo-600 text-sm"
          @click="$emit('close')"
        >
          Teacher List
        </router-link>

        <router-link
          to="/teachers-add"
          class="px-3 py-2 rounded hover:bg-indigo-600 text-sm"
          @click="$emit('close')"
        >
          Add Teacher
        </router-link>
    
        <router-link
          to="/teacher-attendance"
          class="px-3 py-2 rounded hover:bg-indigo-600 text-sm"
          @click="$emit('close')"
        >
          Teacher Attendance
        </router-link>
      </div>

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

