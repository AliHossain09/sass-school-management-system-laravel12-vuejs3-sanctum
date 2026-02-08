<script setup>
import TeacherLayout from '../../layouts/TeacherLayout.vue'
import EventCalendar from '../../components/HeadmasterMenu/events/EventCalendar.vue'
</script>

<template>
  <TeacherLayout>
    <EventCalendar />
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 ">
      <div class="bg-yellow-100 p-5 rounded shadow">
        <p class="text-gray-500">TeacherLayout Schools</p>
        <p class="text-3xl font-bold">12</p>
      </div>

      <div class="bg-green-200 p-5 rounded shadow">
        <p class="text-gray-500">TeacherLayout Teachers</p>
        <p class="text-3xl font-bold">240</p>
      </div>

      <div class="bg-gray-400 p-5 rounded shadow">
        <p class="text-gray-500">TeacherLayout Students</p>
        <p class="text-3xl font-bold">240</p>
      </div>
    </div>
<br>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-green-100 p-5 rounded shadow">
        <p class="text-gray-500">TeacherLayout Teachers</p>
        <p class="text-3xl font-bold">240</p>
      </div>

      <div class="bg-indigo-300 p-5 rounded shadow">
        <p class="text-gray-500">TeacherLayout Teachers</p>
        <p class="text-3xl font-bold">240</p>
      </div>

      <div class="bg-orange-200 p-5 rounded shadow">
        <p class="text-gray-500">TeacherLayout Schools</p>
        <p class="text-3xl font-bold">12</p>
      </div>
    </div>
<br>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 ">
      <div class="bg-yellow-100 p-5 rounded shadow">
        <p class="text-gray-500">TeacherLayout Schools</p>
        <p class="text-3xl font-bold">12</p>
      </div>

      <div class="bg-green-200 p-5 rounded shadow">
        <p class="text-gray-500">TeacherLayout Teachers</p>
        <p class="text-3xl font-bold">240</p>
      </div>

      <div class="bg-red-800 p-5 rounded shadow">
        <p class="text-white">TeacherLayout Students</p>
        <p class="text-3xl text-white font-bold">240</p>
      </div>
    </div>
<br>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-orange-200 p-5 rounded shadow">
        <p class="text-gray-500">TeacherLayout Schools</p>
        <p class="text-3xl font-bold">12</p>
      </div>

      <div class="bg-indigo-300 p-5 rounded shadow">
        <p class="text-gray-500">TeacherLayout Teachers</p>
        <p class="text-3xl font-bold">240</p>
      </div>

      <div class="bg-green-100 p-5 rounded shadow">
        <p class="text-gray-500">TeacherLayout Teachers</p>
        <p class="text-3xl font-bold">240</p>
      </div>
    </div>
  </TeacherLayout>
</template>
