<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  FaArchway,
  FaSchool,
  FaUserGraduate,
  FaUserTie,
  FaBorderAll,
  FaAddressBook,
  FaCalendar,
  FaRegCommentDots,
  FaWpforms,
} from 'vue-icons-plus/fa'

defineProps({
  isOpen: Boolean,
})

defineEmits(['close'])

const router = useRouter()

const showTeacherMenu = ref(false)
const showStudentMenu = ref(false)
const showLeavesMenu = ref(true)
const showExaminationMenu = ref(false)

const logout = () => {
  localStorage.clear()
  router.push('/login')
}
</script>

<template>
  <!-- Overlay (mobile) -->
  <div v-if="isOpen" class="fixed inset-0 bg-black/40 z-40 xl:hidden" @click="$emit('close')"></div>

  <!-- SIDEBAR -->
  <aside
    class="bg-blue-900 text-white w-64 flex flex-col fixed top-16 left-0 z-50 h-[calc(100vh-4rem)] transition-transform duration-300 overflow-y-auto"
    :class="{
      '-translate-x-full xl:translate-x-0': !isOpen,
      'translate-x-0': isOpen,
    }"
  >
    <!-- Close button (mobile) -->
    <button class="xl:hidden text-right text-xl p-4" @click="$emit('close')">&times;</button>

    <!-- NAV -->
    <nav class="flex flex-col gap-2 px-4 pb-4">
      <router-link
        to="/headmaster-dashboard"
        class="px-3 py-2 rounded hover:bg-indigo-700 flex items-center gap-2"
        active-class="bg-indigo-600"
        @click="$emit('close')"
      >
        <FaSchool class="w-5 h-5" />
        Dashboard
      </router-link>

      <router-link
        to="/school-classes"
        class="px-3 py-2 rounded hover:bg-indigo-700 flex items-center gap-2"
        active-class="bg-indigo-600"
        @click="$emit('close')"
      >
        <FaArchway class="w-5 h-5" />
        Classes
      </router-link>

      <router-link
        to="/sections"
        class="px-3 py-2 rounded hover:bg-indigo-700 flex items-center gap-2"
        active-class="bg-indigo-600"
        @click="$emit('close')"
      >
        <FaBorderAll class="w-5 h-5" />
        Sections
      </router-link>

      <router-link
        to="/subjects"
        class="px-3 py-2 rounded hover:bg-indigo-700 flex items-center gap-2"
        active-class="bg-indigo-600"
        @click="$emit('close')"
      >
        <FaAddressBook class="w-5 h-5" />
        Subjects
      </router-link>

      <!-- STUDENT -->
      <button
        class="px-3 py-2 rounded hover:bg-indigo-700 flex items-center justify-between"
        @click="showStudentMenu = !showStudentMenu"
      >
        <span class="flex items-center gap-2">
          <FaUserGraduate class="w-5 h-5" />
          Student Info
        </span>
        {{ showStudentMenu ? '\u25B2' : '\u25BC' }}
      </button>

      <div v-if="showStudentMenu" class="ml-6 flex flex-col gap-1">
        <router-link to="/students-list" class="text-sm hover:bg-indigo-600 px-3 py-2 rounded" @click="$emit('close')"
          >Student List</router-link
        >
        <router-link
          to="/student-admission"
          class="text-sm hover:bg-indigo-600 px-3 py-2 rounded"
          @click="$emit('close')"
          >Student Admission</router-link
        >
        <router-link
          to="/student-attendance"
          class="text-sm hover:bg-indigo-600 px-3 py-2 rounded"
          @click="$emit('close')"
          >Student Attendance</router-link
        >
      </div>

      <!-- TEACHER -->
      <button
        class="px-3 py-2 rounded hover:bg-indigo-700 flex items-center justify-between"
        @click="showTeacherMenu = !showTeacherMenu"
      >
        <span class="flex items-center gap-2">
          <FaUserTie class="w-5 h-5" />
          Teacher Info
        </span>
        {{ showTeacherMenu ? '\u25B2' : '\u25BC' }}
      </button>

      <div v-if="showTeacherMenu" class="ml-6 flex flex-col gap-1">
        <router-link to="/teachers-list" class="text-sm hover:bg-indigo-600 px-3 py-2 rounded" @click="$emit('close')"
          >Teacher List</router-link
        >
        <router-link to="/teachers-add" class="text-sm hover:bg-indigo-600 px-3 py-2 rounded" @click="$emit('close')"
          >Add Teacher</router-link
        >
        <router-link
          to="/teacher-attendance"
          class="text-sm hover:bg-indigo-600 px-3 py-2 rounded"
          @click="$emit('close')"
          >Teacher Attendance</router-link
        >
      </div>

      <!-- LEAVES -->
      <button class="px-3 py-2 rounded hover:bg-indigo-700 flex items-center justify-between" @click="showLeavesMenu = !showLeavesMenu">
        <span class="flex items-center gap-2">
          <FaWpforms class="w-5 h-5" />
          Leaves
        </span>
        {{ showLeavesMenu ? '\u25B2' : '\u25BC' }}
      </button>

      <div v-if="showLeavesMenu" class="ml-6 flex flex-col gap-1">
        <router-link to="/leave-types" class="text-sm hover:bg-indigo-600 px-3 py-2 rounded" @click="$emit('close')"
          >Leave Type</router-link
        >
        <router-link to="/leave-requests" class="text-sm hover:bg-indigo-600 px-3 py-2 rounded" @click="$emit('close')"
          >Leave Request</router-link
        >
      </div>

      <!-- EXAMINATION -->
      <button
        class="px-3 py-2 rounded hover:bg-indigo-700 flex items-center justify-between"
        @click="showExaminationMenu = !showExaminationMenu"
      >
        <span class="flex items-center gap-2">
          <FaWpforms class="w-5 h-5" />
          Examination
        </span>
        {{ showExaminationMenu ? '\u25B2' : '\u25BC' }}
      </button>

      <div v-if="showExaminationMenu" class="ml-6 flex flex-col gap-1">
        <router-link
          to="/examination-names"
          class="text-sm hover:bg-indigo-600 px-3 py-2 rounded"
          @click="$emit('close')"
          >Examination Name</router-link
        >
        <router-link to="/exam-grades" class="text-sm hover:bg-indigo-600 px-3 py-2 rounded" @click="$emit('close')"
          >Grade</router-link
        >
        <router-link to="/exam-marks" class="text-sm hover:bg-indigo-600 px-3 py-2 rounded" @click="$emit('close')"
          >Mark</router-link
        >
        <router-link to="/exam-seat-plan" class="text-sm hover:bg-indigo-600 px-3 py-2 rounded" @click="$emit('close')"
          >Seat Plan</router-link
        >
        <router-link to="/exam-schedules" class="text-sm hover:bg-indigo-600 px-3 py-2 rounded" @click="$emit('close')"
          >Exam Schedule</router-link
        >
        <router-link to="/exam-promotion" class="text-sm hover:bg-indigo-600 px-3 py-2 rounded" @click="$emit('close')"
          >Promotion</router-link
        >
        <router-link to="/send-exam-mark" class="text-sm hover:bg-indigo-600 px-3 py-2 rounded" @click="$emit('close')"
          >Send exam mark</router-link
        >
        <router-link to="/exam-result" class="text-sm hover:bg-indigo-600 px-3 py-2 rounded" @click="$emit('close')"
          >Exam Result</router-link
        >
      </div>

      <router-link to="/class-routine" class="px-3 py-2 rounded hover:bg-indigo-700 flex items-center gap-2" active-class="bg-indigo-600">
        <FaWpforms class="w-5 h-5" />
        Routine
      </router-link>

      <router-link to="/notices" class="px-3 py-2 rounded hover:bg-indigo-700 flex items-center gap-2" active-class="bg-indigo-600">
        <FaRegCommentDots class="w-5 h-5" />
        Notices
      </router-link>

      <router-link to="/events-list" class="px-3 py-2 rounded hover:bg-indigo-700 flex items-center gap-2" active-class="bg-indigo-600">
        <FaCalendar class="w-5 h-5" />
        Events
      </router-link>

      <router-link to="/academic-year" class="px-3 py-2 rounded hover:bg-indigo-700 flex items-center gap-2" active-class="bg-indigo-600">
        <FaUserGraduate class="w-5 h-5" />
        Academic Year
      </router-link>
    </nav>

    <!-- LOGOUT -->
    <div class="p-4 mt-auto">
      <button @click="logout" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded">Logout</button>
    </div>
  </aside>
</template>
