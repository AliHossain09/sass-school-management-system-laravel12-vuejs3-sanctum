<script setup>
import { ref, onMounted } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import axios from 'axios'

const calendarOptions = ref({
  plugins: [dayGridPlugin],
  initialView: 'dayGridMonth',
  height: 'auto',
  events: []
})

const loadEvents = async () => {
  const res = await axios.get('/api/events/calendar', {
    headers: {
      Authorization: `Bearer ${localStorage.getItem('token')}`
    }
  })

  // FIX HERE
  calendarOptions.value.events = res.data.map(e => ({
    title: e.title,
    start: e.start_date,
    end: e.end_date
  }))
}

onMounted(loadEvents)
</script>

<template>
  <FullCalendar :options="calendarOptions" />
</template>
