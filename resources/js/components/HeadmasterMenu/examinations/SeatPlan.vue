<script setup lang="ts">
import { ref } from 'vue'
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'

interface TableRow {
  id: number
  name: string
  leftRoll: string
  rightRoll: string
}

interface Room {
  id: number
  number: string
  tables: TableRow[]
  editing: boolean
  editValue: string
}

let roomIdCounter = 0
let tableIdCounter = 0

const roomInput = ref('')
const rooms = ref<Room[]>([])

const makeTable = (index: number): TableRow => ({
  id: tableIdCounter++,
  name: `Table ${index}`,
  leftRoll: '',
  rightRoll: '',
})

const addRoom = () => {
  const num = roomInput.value.trim()
  if (!num) return
  rooms.value.push({
    id: roomIdCounter++,
    number: num,
    tables: [makeTable(1), makeTable(2), makeTable(3)],
    editing: false,
    editValue: num,
  })
  roomInput.value = ''
}

const addTable = (room: Room) => {
  const nextIndex = room.tables.length + 1
  room.tables.push(makeTable(nextIndex))
}

const removeTable = (room: Room, tableId: number) => {
  room.tables = room.tables.filter(t => t.id !== tableId)
}

const startEdit = (room: Room) => {
  room.editValue = room.number
  room.editing = true
}

const saveEdit = (room: Room) => {
  const val = room.editValue.trim()
  if (val) room.number = val
  room.editing = false
}

const deleteRoom = (roomId: number) => {
  rooms.value = rooms.value.filter(r => r.id !== roomId)
}
</script>

<template>
  <HeadmasterLayout>
    <!-- Breadcrumb -->
    <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
      <p class="text-gray-700">Headmaster &gt;</p>
      <p class="text-gray-700">Examination &gt;</p>
      <p class="text-gray-700">Seat Plan</p>
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">
      <h1 class="text-3xl font-bold underline text-center my-6">Exam Hall Seat Plan</h1>

      <!-- Add Room Input -->
      <div class="text-center mb-6">
        <input
          v-model="roomInput"
          type="text"
          placeholder="Enter Room Number"
          class="border p-2 rounded"
          @keydown.enter.prevent="addRoom"
        />
        <button @click="addRoom" class="bg-blue-500 text-white px-4 py-2 rounded ml-2">
          Add Room
        </button>
      </div>

      <!-- Empty state -->
      <div
        v-if="rooms.length === 0"
        class="text-center text-gray-500 py-12 bg-white rounded border border-dashed border-gray-300"
      >
        Enter a room number above and click <strong>Add Room</strong> to start building the seat plan.
      </div>

      <!-- Room Cards -->
      <div v-for="room in rooms" :key="room.id" class="bg-white shadow-lg mb-8 rounded-xl">

        <!-- Room Header -->
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b border-gray-200">
          <div class="flex items-center gap-2">
            <template v-if="!room.editing">
              <h2 class="text-2xl font-bold">Room {{ room.number }}</h2>
              <button
                @click="startEdit(room)"
                title="Edit room number"
                class="text-blue-500 hover:text-blue-700 text-sm px-2 py-1 rounded hover:bg-blue-50 transition"
              >✏️ Edit</button>
            </template>
            <template v-else>
              <input
                v-model="room.editValue"
                class="border rounded px-2 py-1 text-xl font-bold w-32"
                @keydown.enter.prevent="saveEdit(room)"
                autofocus
              />
              <button
                @click="saveEdit(room)"
                class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 transition"
              >Save</button>
              <button
                @click="room.editing = false"
                class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm hover:bg-gray-300 transition"
              >Cancel</button>
            </template>
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="addTable(room)"
              class="bg-indigo-500 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-600 transition flex items-center gap-1"
            >
              ➕ Add Table
            </button>
            <button
              @click="deleteRoom(room.id)"
              title="Delete room"
              class="bg-red-500 text-white px-3 py-1.5 rounded text-sm hover:bg-red-600 transition"
            >🗑️ Delete Room</button>
          </div>
        </div>

        <!-- Tables Grid -->
        <div class="flex flex-wrap justify-center gap-6 p-6">

          <div
            v-for="table in room.tables"
            :key="table.id"
            class="relative w-[650px] h-[150px] border-8 border-gray-400 bg-gray-200 rounded-lg overflow-hidden"
          >
            <!-- Table label - inside box at top center -->
            <div class="absolute top-2 left-1/2 -translate-x-1/2 bg-white px-2 py-0.5 text-sm font-bold border border-gray-400 rounded whitespace-nowrap z-10">
              {{ table.name }}
            </div>

            <!-- Left roll input -->
            <div class="absolute left-0 top-0 w-1/2 h-full flex items-center justify-start pl-4">
              <input
                v-model="table.leftRoll"
                type="text"
                placeholder="Roll"
                class="w-28 text-2xl font-bold bg-transparent border-b-2 border-gray-500 focus:outline-none focus:border-blue-500 text-center placeholder-gray-400 transition"
              />
            </div>

            <!-- Center divider -->
            <div class="absolute left-1/2 top-1/4 h-1/2 border-r-2 border-gray-400 -translate-x-1/2"></div>

            <!-- Right roll input -->
            <div class="absolute right-0 top-0 w-1/2 h-full flex items-center justify-end pr-4">
              <input
                v-model="table.rightRoll"
                type="text"
                placeholder="Roll"
                class="w-28 text-2xl font-bold bg-transparent border-b-2 border-gray-500 focus:outline-none focus:border-blue-500 text-center placeholder-gray-400 transition"
              />
            </div>

            <!-- Remove button -->
            <button
              @click="removeTable(room, table.id)"
              title="Remove table"
              class="absolute top-1 right-1 bg-red-400 hover:bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center leading-none transition z-20"
            >×</button>
          </div>

        </div>
      </div>
    </div>
  </HeadmasterLayout>
</template>