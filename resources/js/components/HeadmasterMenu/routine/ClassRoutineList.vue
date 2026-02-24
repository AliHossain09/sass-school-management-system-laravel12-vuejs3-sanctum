<script setup lang="ts">
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { ref, onMounted } from 'vue'
import Swal from 'sweetalert2'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

/* ================= STATE ================= */
const routines = ref<any[]>([])
const classes = ref<any[]>([])
const sections = ref<any[]>([])
const subjects = ref<any[]>([])
const teachers = ref<any[]>([])
const loading = ref(false)
const activeForm = ref(false)
const editingRoutine = ref<any | null>(null)

const meta = ref({
    current_page: 1,
    from: 0,
    to: 0,
    total: 0,
    last_page: 0,
    per_page: 10,
})

const form = ref({
    class_id: '',
    section_id: '',
    subject_id: '',
    teacher_id: '',
    start_time: '',
    end_time: '',
    is_break: false,
    other_days: [] as string[],
    class_room: ''
})

/* ================= AUTH ================= */
const authHeader = () => {
    const token = localStorage.getItem('token')
    return token ? { Authorization: `Bearer ${token}` } : {}
}

/* ================= LOAD DATA ================= */
const loadRoutines = async (page = 1) => {
    try {
        loading.value = true
        const res = await axios.get('/api/class-routines', {
            headers: authHeader(),
            params: { page }
        })

        routines.value = res.data.data
        meta.value = {
            current_page: res.data.current_page,
            from: res.data.from,
            to: res.data.to,
            total: res.data.total,
            last_page: res.data.last_page,
            per_page: res.data.per_page,
        }

    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Failed to load routines')
    } finally {
        loading.value = false
    }
}

const loadDropdowns = async () => {
    try {
        const [c, s, sub, t] = await Promise.all([
            axios.get('/api/classes', { headers: authHeader() }),
            axios.get('/api/sections', { headers: authHeader() }),
            axios.get('/api/subjects', { headers: authHeader() }),
            axios.get('/api/teachers', { headers: authHeader() }),
        ])

        classes.value = c.data.data
        sections.value = s.data.data
        subjects.value = sub.data.data
        teachers.value = t.data.data

    } catch {
        toast.error('Failed to load dropdown data')
    }
}

onMounted(() => {
    loadRoutines()
    loadDropdowns()
})

/* ================= FORM ================= */
const resetForm = () => {
    form.value = {
        class_id: '',
        section_id: '',
        subject_id: '',
        teacher_id: '',
        start_time: '',
        end_time: '',
        is_break: false,
        other_days: [],
        class_room: ''
    }
    editingRoutine.value = null
}

const days = [
    'Saturday',
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday'
]

const openForm = (routine: any = null) => {
    if (routine) {
        editingRoutine.value = routine
        form.value = {
            class_id: routine.class_id,
            section_id: routine.section_id,
            subject_id: routine.subject_id,
            teacher_id: routine.teacher_id,
            start_time: routine.start_time,
            end_time: routine.end_time,
            is_break: routine.is_break,
            other_days: routine.other_days || [],
            class_room: routine.class_room || ''
        }
    } else {
        resetForm()
    }
    activeForm.value = true
}

const closeForm = () => {
    activeForm.value = false
    resetForm()
}

const submit = async () => {
    if (!form.value.class_id || !form.value.section_id || !form.value.subject_id) {
        toast.error('Class, Section & Subject required')
        return
    }

    try {
        loading.value = true

        if (editingRoutine.value) {
            await axios.put(`/api/class-routines/${editingRoutine.value.id}`, form.value, { headers: authHeader() })
            toast.success('Routine updated successfully')
        } else {
            await axios.post('/api/class-routines', form.value, { headers: authHeader() })
            toast.success('Routine created successfully')
        }

        activeForm.value = false
        loadRoutines(meta.value.current_page)

    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Failed to save routine')
    } finally {
        loading.value = false
    }
}

/* ================= DELETE ================= */
const deleteRoutine = async (id: number) => {
    const ok = await Swal.fire({
        title: 'Are you sure?',
        icon: 'warning',
        showCancelButton: true,
    })

    if (!ok.isConfirmed) return

    try {
        await axios.delete(`/api/class-routines/${id}`, { headers: authHeader() })
        toast.success('Routine deleted successfully')
        loadRoutines(meta.value.current_page)
    } catch (err: any) {
        toast.error(err.response?.data?.message || 'Delete failed')
    }
}
</script>

<template>
    <HeadmasterLayout>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Class Routine</h1>
            <button @click="openForm()" class="bg-blue-600 text-white px-5 py-2 rounded">
                Create Routine
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto bg-white shadow rounded">
            <table class="min-w-full">
                <thead class="bg-blue-200">
                    <tr>
                        <th class="px-4 py-2">Class</th>
                        <th class="px-4 py-2">Section</th>
                        <th class="px-4 py-2">Subject</th>
                        <th class="px-4 py-2">Teacher</th>
                        <th class="px-4 py-2">Time</th>
                        <th class="px-4 py-2">Room</th>
                        <th class="px-4 py-2">Days</th>
                        <th class="px-4 py-2 text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="(r, index) in routines" :key="r.id"
                        :class="[index % 2 === 0 ? 'bg-white' : 'bg-gray-100', 'border-b']">
                        <td class="px-4 py-2">{{ r.school_class?.name }}</td>
                        <td class="px-4 py-2">{{ r.section?.name }}</td>
                        <td class="px-4 py-2">{{ r.subject?.name }}</td>
                        <td class="px-4 py-2">{{ r.teacher?.first_name }}</td>
                        <td class="px-4 py-2">{{ r.start_time }} - {{ r.end_time }}</td>
                        <td class="px-4 py-2">{{ r.class_room }}</td>
                        <td class="px-4 py-2">{{ r.other_days?.join(', ') }}</td>
                        <td class="px-4 py-2 flex gap-2 justify-center">
                            <button @click="openForm(r)"
                                class="bg-blue-600 text-white px-3 py-1 rounded text-sm">Edit</button>
                            <button @click="deleteRoutine(r.id)"
                                class="bg-red-600 text-white px-3 py-1 rounded text-sm">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="activeForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded w-full max-w-2xl relative">
                <!-- Close Button -->
                <button @click="closeForm"
                    class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl font-bold">
                    &times;
                </button>

                <h3 class="text-xl font-semibold mb-4">
                    {{ editingRoutine ? 'Edit Routine' : 'Create Routine' }}
                </h3>

                <form @submit.prevent="submit" class="bg-white shadow-md rounded-lg p-6 space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Class -->
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">Class *</label>
                            <select v-model="form.class_id"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-black focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Class</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                            </select>
                        </div>

                        <!-- Section -->
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">Section *</label>
                            <select v-model="form.section_id"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-black focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Section</option>
                                <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">Subject *</label>
                            <select v-model="form.subject_id"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-black focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Subject</option>
                                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>

                        <!-- Teacher -->
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">Teacher</label>
                            <select v-model="form.teacher_id"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-black focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Teacher</option>
                                <option v-for="t in teachers" :key="t.id" :value="t.id">
                                    {{ t.first_name }} {{ t.last_name }}
                                </option>
                            </select>
                        </div>

                        <!-- Start Time -->
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">Start Time *</label>
                            <input type="time" v-model="form.start_time"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-black focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <!-- End Time -->
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">End Time *</label>
                            <input type="time" v-model="form.end_time"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-black focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <!-- Class Room -->
                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium text-gray-700">Class Room</label>
                            <input v-model="form.class_room" placeholder="Class Room"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-black focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <!-- Days -->
                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium text-gray-700">Select Days *</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label v-for="day in days" :key="day" class="flex items-center gap-2">
                                    <input type="checkbox" :value="day" v-model="form.other_days"
                                        class="h-4 w-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500" />
                                    <span class="text-gray-700">{{ day }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Is Break -->
                        <div class="md:col-span-2 flex items-center gap-2">
                            <input type="checkbox" v-model="form.is_break"
                                class="h-4 w-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500" />
                            <label class="font-medium text-gray-700">Is Break</label>
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md font-medium transition duration-200"
                        :disabled="loading">
                        {{ editingRoutine ? 'Update Routine' : loading ? 'Saving...' : 'Save Routine' }}
                    </button>

                </form>


            </div>
        </div>

    </HeadmasterLayout>
</template>