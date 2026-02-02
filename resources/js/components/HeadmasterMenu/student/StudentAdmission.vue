<script setup>
import { reactive, ref } from 'vue'
import axios from 'axios'
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false)
const error = ref('')

const photoPreview = ref(null)

/* ---------------- form ---------------- */
const form = reactive({
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

/* ---------------- auth ---------------- */
const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`
})

/* ---------------- photo ---------------- */
const onPhotoChange = (e) => {
  const file = e.target.files[0]
  if (!file) return
  form.photo = file

  const reader = new FileReader()
  reader.onload = () => (photoPreview.value = reader.result)
  reader.readAsDataURL(file)
}

/* ---------------- submit ---------------- */
const submit = async () => {
  error.value = ''

  if (!form.first_name || !form.last_name || !form.gender || !form.class_id) {
    toast.error('Please fill all required fields')
    return
  }

  const data = new FormData()
  Object.entries(form).forEach(([k, v]) => {
    if (v !== null && v !== '') data.append(k, v)
  })

  try {
    loading.value = true
    await axios.post('/api/students', data, {
      headers: { ...authHeader(), 'Content-Type': 'multipart/form-data' }
    })

    toast.success('Student admitted successfully')

    Object.keys(form).forEach(k => (form[k] = ''))
    form.photo = null
    photoPreview.value = null
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to admit student')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <HeadmasterLayout>
    <!-- breadcrumb -->
    <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
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
            <h2 class="font-semibold mb-6 border-b-2 border-gray-300">BASIC INFORMATION</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="first_name">First Name *</label>
                <input id="first_name" v-model="form.first_name" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="last_name">Last Name *</label>
                <input id="last_name" v-model="form.last_name" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="student_code">Student Code *</label>
                <input id="student_code" v-model="form.student_code" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="academic_year">Academic Year *</label>
                <input id="academic_year" v-model="form.academic_year" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="gender">Gender *</label>
                <select id="gender" v-model="form.gender" class="input">
                  <option value="">Select Gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="dob">Date of Birth</label>
                <input type="date" id="dob" v-model="form.dob" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="religion">Religion</label>
                <input id="religion" v-model="form.religion" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="nationality">Nationality</label>
                <input id="nationality" v-model="form.nationality" class="input" />
              </div>
            </div>
          </section>

          <!-- ACADEMIC INFO -->
          <section class="bg-gray-100 p-4 rounded shadow-sm">
            <h2 class="font-semibold mb-6 border-b-2 border-gray-300">ACADEMIC INFORMATION</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="class_id">Class ID *</label>
                <input id="class_id" v-model="form.class_id" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="section_id">Section ID</label>
                <input id="section_id" v-model="form.section_id" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="shift">Shift</label>
                <select id="shift" v-model="form.shift" class="input">
                  <option value="">Select Shift</option>
                  <option value="morning">Morning</option>
                  <option value="afternoon">Afternoon</option>
                  <option value="day">Day</option>
                  <option value="evening">Evening</option>
                </select>
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="roll_number">Roll Number</label>
                <input id="roll_number" v-model="form.roll_number" class="input" />
              </div>

              <div class="flex flex-col md:col-span-2">
                <label class="mb-1 text-gray-600 font-medium" for="id_card_number">ID Card Number</label>
                <input id="id_card_number" v-model="form.id_card_number" class="input" />
              </div>

              <div class="flex flex-col md:col-span-2">
                <label class="mb-1 text-gray-600 font-medium" for="board_registration_number">Board Registration Number</label>
                <input id="board_registration_number" v-model="form.board_registration_number" class="input" />
              </div>
            </div>
          </section>

          <!-- GUARDIAN INFO -->
          <section class="bg-gray-100 p-4 rounded shadow-sm">
            <h2 class="font-semibold mb-6 border-b-2 border-gray-300">GUARDIAN INFORMATION</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="father_name">Father Name</label>
                <input id="father_name" v-model="form.father_name" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="father_phone">Father Phone</label>
                <input id="father_phone" v-model="form.father_phone" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="mother_name">Mother Name</label>
                <input id="mother_name" v-model="form.mother_name" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="mother_phone">Mother Phone</label>
                <input id="mother_phone" v-model="form.mother_phone" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="local_guardian_name">Local Guardian Name</label>
                <input id="local_guardian_name" v-model="form.local_guardian_name" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="local_guardian_phone">Local Guardian Phone</label>
                <input id="local_guardian_phone" v-model="form.local_guardian_phone" class="input" />
              </div>

              <div class="flex flex-col md:col-span-2">
                <label class="mb-1 text-gray-600 font-medium" for="local_guardian_relationship">Guardian Relationship</label>
                <input id="local_guardian_relationship" v-model="form.local_guardian_relationship" class="input" />
              </div>
            </div>
          </section>

          <!-- CONTACT INFO -->
          <section class="bg-gray-100 p-4 rounded shadow-sm">
            <h2 class="font-semibold mb-6 border-b-2 border-gray-300">CONTACT INFORMATION</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="phone">Phone</label>
                <input id="phone" v-model="form.phone" class="input" />
              </div>

              <div class="flex flex-col ">
                <label class="mb-1 text-gray-600 font-medium" for="email">Email</label>
                <input id="email" v-model="form.email" class="input" />
              </div>

              <div class="flex flex-col md:col-span-2">
                <label class="mb-1 text-gray-600 font-medium" for="present_address">Present Address</label>
                <textarea id="present_address" v-model="form.present_address" class="input"></textarea>
              </div>

              <div class="flex flex-col md:col-span-2">
                <label class="mb-1 text-gray-600 font-medium" for="permanent_address">Permanent Address</label>
                <textarea id="permanent_address" v-model="form.permanent_address" class="input md:h-28"></textarea>
              </div>
            </div>
          </section>

          <!-- ACCESS INFO -->
          <section class="bg-gray-100 p-4 rounded shadow-sm">
            <h2 class="font-semibold mb-6 border-b-2 border-gray-300">ACCESS INFORMATION</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="username">Username *</label>
                <input id="username" v-model="form.username" class="input" />
              </div>

              <div class="flex flex-col">
                <label class="mb-1 text-gray-600 font-medium" for="password">Password *</label>
                <input id="password" type="password" v-model="form.password" class="input" />
              </div>
            </div>
          </section>

          <!-- PHOTO -->
          <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-4">
            <input
              type="file"
              @change="onPhotoChange"
              class="md:h-48 w-full border-2 border-dashed border-green-300 rounded-lg p-4"
            />
            <img v-if="photoPreview" :src="photoPreview" class="md:h-40 mt-4 ml-2 rounded border" />
          </div>

          <button class="btn-primary w-full col-span-2" :disabled="loading">
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
