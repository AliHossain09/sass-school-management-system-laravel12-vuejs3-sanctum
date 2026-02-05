<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import axios from 'axios'
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { useToast } from 'vue-toastification'

/* ---------------- basic ---------------- */
const toast = useToast()
const loading = ref(false)
const error = ref('')
const photoPreview = ref<string | null>(null)

/* ---------------- form (TYPED) ---------------- */
const form = reactive<{
  student_code: string
  academic_year: string
  first_name: string
  last_name: string
  gender: string
  dob: string
  religion: string
  nationality: string
  phone: string
  email: string
  present_address: string
  permanent_address: string
  father_name: string
  father_phone: string
  mother_name: string
  mother_phone: string
  local_guardian_name: string
  local_guardian_phone: string
  local_guardian_relationship: string
  class_id: string
  section_id: string
  shift: string
  roll_number: string
  id_card_number: string
  board_registration_number: string
  elective_subject_id: string
  extra_curricular: string
  description: string
  username: string
  password: string
  photo: File | null
}>({
  student_code: '',
  academic_year: '',
  first_name: '',
  last_name: '',
  gender: '',
  dob: '',
  religion: '',
  nationality: '',
  phone: '',
  email: '',
  present_address: '',
  permanent_address: '',
  father_name: '',
  father_phone: '',
  mother_name: '',
  mother_phone: '',
  local_guardian_name: '',
  local_guardian_phone: '',
  local_guardian_relationship: '',
  class_id: '',
  section_id: '',
  shift: '',
  roll_number: '',
  id_card_number: '',
  board_registration_number: '',
  elective_subject_id: '',
  extra_curricular: '',
  description: '',
  username: '',
  password: '',
  photo: null
})

/* ---------------- dropdowns ---------------- */
const classes = ref<any[]>([])
const sections = ref<any[]>([])
const electiveSubjects = ref<any[]>([])

// const electiveSubjects = ref<any[]>([
//   { id: '1', name: 'Biology' },
//   { id: '2', name: 'Computer Science' },
//   { id: '3', name: 'Economics' },
//   { id: '4', name: 'History' },
//   { id: '5', name: 'Physics' },
//   { id: '6', name: 'Chemistry' }
// ])

/* ---------------- auth header ---------------- */
const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`
})


/* ---------------- load classes ---------------- */
const loadClasses = async () => {
  try {
    const res = await axios.get('/api/classes', {
      headers: authHeader()
    })
    classes.value = res.data.data   // important
  } catch (err) {
    console.error(err)
    toast.error('Failed to load classes')
  }
}

/* ---------------- load sections ---------------- */
const loadSections = async () => {
  try {
    const res = await axios.get('/api/sections', {
      headers: authHeader()
    })
    sections.value = res.data.data  // important
  } catch (err) {
    console.error(err)
    toast.error('Failed to load sections')
  }
}
/* ---------------- load elective subjects ---------------- */
const loadElectiveSubjects = async () => {
  try {
    const res = await axios.get('/subjects', {
      headers: authHeader()
    })
    electiveSubjects.value = res.data
  } catch (err) {
    toast.error('Failed to load elective subjects')
  }
}


/* ---------------- photo preview ---------------- */
const onPhotoChange = (e: Event) => {
  const target = e.target as HTMLInputElement

  if (target.files && target.files[0]) {
    form.photo = target.files[0]

    const reader = new FileReader()
    reader.onload = () => {
      photoPreview.value = reader.result as string
    }
    reader.readAsDataURL(target.files[0])
  }
}

/* ---------------- submit ---------------- */
const submit = async () => {
  error.value = ''

  if (!form.first_name || !form.last_name || !form.gender || !form.class_id || !form.username || !form.password) {
    toast.error('Please fill all required fields')
    return
  }

  const data = new FormData()
  Object.entries(form).forEach(([key, value]) => {
    if (value !== null && value !== '') {
      data.append(key, value as any)
    }
  })

  try {
    loading.value = true

    await axios.post('/api/students', data, {
      headers: {
        ...authHeader(),
        'Content-Type': 'multipart/form-data'
      }
    })

    toast.success('Student admitted successfully')

    Object.keys(form).forEach((key) => {
      // @ts-ignore
      form[key] = ''
    })

    form.photo = null
    photoPreview.value = null

  } catch (e: any) {
    toast.error(e.response?.data?.message || 'Failed to admit student')
  } finally {
    loading.value = false
  }
}


/* ---------------- mounted ---------------- */
onMounted(() => {
  loadClasses()
  loadSections()
  loadElectiveSubjects()
})
</script>



<template>
  <HeadmasterLayout>
    <!-- breadcrumb -->
    <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2 flex-wrap">
      <p class="text-gray-700">Head Master Admin ></p>
      <p class="text-gray-700">Student ></p>
      <p class="text-gray-700">Add Student</p>
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">
      <h1 class="text-2xl font-bold mb-6">Add Student</h1>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- BASIC INFO -->
          <section class="bg-gray-100 p-4 rounded shadow-sm">
            <h2 class="font-semibold mb-6 border-b-2 border-gray-300">
              BASIC INFORMATION
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">First Name *</label>
                <input v-model="form.first_name" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Last Name *</label>
                <input v-model="form.last_name" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Student Code *</label>
                <input v-model="form.student_code" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Academic Year *</label>
                <input v-model="form.academic_year" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Gender *</label>
                <select v-model="form.gender" class="input">
                  <option value="">Select Gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Date of Birth</label>
                <input type="date" v-model="form.dob" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Religion</label>
                <input v-model="form.religion" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Nationality</label>
                <input v-model="form.nationality" class="input" />
              </div>
            </div>
          </section>

          <!-- ACADEMIC INFO -->
          <section class="bg-gray-100 p-4 rounded shadow-sm">
            <h2 class="font-semibold mb-6 border-b-2 border-gray-300">
              ACADEMIC INFORMATION
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Class *</label>
                <select v-model="form.class_id" class="input">
                  <option value="">Select Class</option>
                  <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                    {{ cls.name }}
                  </option>
                </select>
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Section</label>
                <select v-model="form.section_id" class="input">
                  <option value="">Select Section</option>
                  <option v-for="sec in sections" :key="sec.id" :value="sec.id">
                    {{ sec.name }}
                  </option>
                </select>
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Shift</label>
                <select v-model="form.shift" class="input">
                  <option value="">Select Shift</option>
                  <option value="morning">Morning</option>
                  <option value="afternoon">Afternoon</option>
                  <option value="day">Day</option>
                  <option value="evening">Evening</option>
                </select>
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Roll Number</label>
                <input v-model="form.roll_number" class="input" />
              </div>

              <div class="flex flex-col md:col-span-2">
                <label class="mb-1 text-gray-600 font-medium">ID Card Number</label>
                <input v-model="form.id_card_number" class="input" />
              </div>

              <div class="flex flex-col md:col-span-2">
                <label class="mb-1 text-gray-600 font-medium">
                  Board Registration Number
                </label>
                <input v-model="form.board_registration_number" class="input" />
              </div>
            </div>

            <div v-if="[9, 10, 11, 12].includes(Number(form.class_id))" class="flex flex-col mt-4">
              <label class="mb-1 text-gray-600 font-medium">
                Elective Subject
              </label>
              <select v-model="form.elective_subject_id" class="input">
                <option value="">Select Elective Subject</option>
                <option v-for="sub in electiveSubjects" :key="sub.id" :value="sub.id">
                  {{ sub.name }}
                </option>
              </select>
            </div>
          </section>

          <!-- GUARDIAN INFO -->
          <section class="bg-gray-100 p-4 rounded shadow-sm">
            <h2 class="font-semibold mb-6 border-b-2 border-gray-300">
              GUARDIAN INFORMATION
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Father Name</label>
                <input v-model="form.father_name" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Father Phone</label>
                <input v-model="form.father_phone" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Mother Name</label>
                <input v-model="form.mother_name" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Mother Phone</label>
                <input v-model="form.mother_phone" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">
                  Local Guardian Name
                </label>
                <input v-model="form.local_guardian_name" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">
                  Local Guardian Phone
                </label>
                <input v-model="form.local_guardian_phone" class="input" />
              </div>

              <div class="flex flex-col md:col-span-2">
                <label class="mb-1 text-gray-600 font-medium">
                  Guardian Relationship
                </label>
                <input v-model="form.local_guardian_relationship" class="input" />
              </div>
            </div>
          </section>

          <!-- CONTACT INFO -->
          <section class="bg-gray-100 p-4 rounded shadow-sm">
            <h2 class="font-semibold mb-6 border-b-2 border-gray-300">
              CONTACT INFORMATION
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Phone</label>
                <input v-model="form.phone" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Email</label>
                <input v-model="form.email" class="input" />
              </div>

              <div class="flex flex-col md:col-span-2">
                <label class="mb-1 text-gray-600 font-medium">
                  Present Address
                </label>
                <textarea v-model="form.present_address" class="input"></textarea>
              </div>

              <div class="flex flex-col md:col-span-2">
                <label class="mb-1 text-gray-600 font-medium">
                  Permanent Address
                </label>
                <textarea v-model="form.permanent_address" class="input md:h-28"></textarea>
              </div>
            </div>
          </section>

          <!-- ACCESS INFO -->
          <section class="bg-gray-100 p-4 rounded shadow-sm">
            <h2 class="font-semibold mb-6 border-b-2 border-gray-300">
              ACCESS INFORMATION
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Username *</label>
                <input v-model="form.username" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium">Password *</label>
                <input type="password" v-model="form.password" class="input" />
              </div>
            </div>
          </section>

          <!-- PHOTO (RIGHT SIDE, SAME ROW) -->
          <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="file" @change="onPhotoChange" class="md:h-48 w-full border-2 border-dashed border-green-300 rounded-lg p-4" /> 
            <img v-if="photoPreview" :src="photoPreview" class="md:h-40 mt-4 ml-2 rounded border" />
          </div>

          <!-- SAVE -->
          <button class="btn-primary w-full md:col-span-2" :disabled="loading">
            {{ loading ? 'Saving...' : 'Save Student' }}
          </button>


        </div>
      </form>
    </div>
  </HeadmasterLayout>
</template>


<style scoped>
.input {
  border: 1px solid #ccc;
  padding: 8px 12px;
  border-radius: 4px;
  width: 100%;
}

.btn-primary {
  background: #2563eb;
  color: white;
  padding: 12px;
  border-radius: 6px;
}

.btn-primary:disabled {
  background: #a5b4fc;
}
</style>
