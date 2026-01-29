<script setup>
import { reactive, ref } from 'vue'
import axios from 'axios'
import HeadmasterLayout from '../../../layouts/HeadmasterLayout.vue'

const loading = ref(false)
const error = ref('')

// Reactive form state
const form = reactive({
  first_name: '',
  last_name: '',
  gender: '',
  dob: '',
  subjects: [],
  class_assigned: '',
  joining_date: '',
  grade: '',
  employment_type: 'full-time',
  department: '',
  phone: '',
  email: '',
  address: '',
  emergency_contact: '',
  qualification: '',
  experience: '',
  salary: '',
  photo: null,
})

const subjectsInput = ref('')
const photoPreview = ref(null)

// Handle file input change and preview
const onPhotoChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.photo = file
    const reader = new FileReader()
    reader.onload = () => {
      photoPreview.value = reader.result
    }
    reader.readAsDataURL(file)
  }
}

const authHeader = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`
})

const submit = async () => {
  error.value = ''

  if (!form.first_name || !form.last_name || !form.gender) {
    error.value = 'First name, last name, and gender are required.'
    return
  }

  const data = new FormData()
  data.append('first_name', form.first_name)
  data.append('last_name', form.last_name)
  data.append('gender', form.gender)
  if(form.dob) data.append('dob', form.dob)
  if(subjectsInput.value.trim()) data.append('subjects', JSON.stringify(subjectsInput.value.split(',').map(s => s.trim())))
  if(form.class_assigned) data.append('class_assigned', form.class_assigned)
  if(form.joining_date) data.append('joining_date', form.joining_date)
  if(form.grade) data.append('grade', form.grade)
  data.append('employment_type', form.employment_type)
  if(form.department) data.append('department', form.department)
  if(form.phone) data.append('phone', form.phone)
  if(form.email) data.append('email', form.email)
  if(form.address) data.append('address', form.address)
  if(form.emergency_contact) data.append('emergency_contact', form.emergency_contact)
  if(form.qualification) data.append('qualification', form.qualification)
  if(form.experience) data.append('experience', form.experience)
  if(form.salary) data.append('salary', form.salary)
  if(form.photo) data.append('photo', form.photo)

  try {
    loading.value = true
    await axios.post('/api/teachers', data, {
      headers: { ...authHeader(), 'Content-Type': 'multipart/form-data' }
    })
    alert('Teacher created successfully!')

    // Reset form
    Object.keys(form).forEach(k => form[k] = (k === 'employment_type') ? 'full-time' : '')
    subjectsInput.value = ''
    photoPreview.value = null
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create teacher.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <HeadmasterLayout>
    <div class="flex shadow-xl rounded mb-6 p-4 bg-white gap-2">
      <p class="text-gray-700">Head Master Admin ></p>
      <p class="text-gray-700">Teacher ></p> 
      <p class="text-gray-700">Add Teacher</p> 
    </div>

    <div class="p-6 shadow-2xl bg-gray-50 rounded">
      <h1 class="text-2xl font-bold mb-6">Add Teacher</h1>
      <p v-if="error" class="text-red-600 mb-4">{{ error }}</p>

      <form @submit.prevent="submit" class="space-y-6">

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Information -->
        <section class="p-4 rounded shadow-sm bg-gray-100">
          <h2 class="font-semibold mb-8 border-b-2 border-gray-300">BASIC INFORMATION</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="first_name" class="block mb-1">First Name *</label>
              <input v-model="form.first_name" id="first_name" class="input col-span-1" />
            </div>
            <div>
              <label for="last_name" class="block mb-1">Last Name *</label>
              <input v-model="form.last_name" id="last_name" class="input col-span-1" />
            </div>
            <div>
              <label for="gender" class="block mb-1">Gender *</label>
              <select v-model="form.gender" id="gender" class="input col-span-1">
                <option value="">Select Gender *</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div>
              <label for="dob" class="block mb-1">Date of Birth</label>
              <input type="date" v-model="form.dob" id="dob" class="input col-span-1" />
            </div>
          </div>
        </section>

        <!-- Professional Details -->
        <section class="bg-gray-100 p-4 rounded shadow-sm">
          <h2 class="font-semibold mb-8 border-b-2 border-gray-300">PROFESSIONAL DETAILS</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label for="subjects" class="block mb-1">Subjects (comma separated)</label>
              <input v-model="subjectsInput" id="subjects" class="input col-span-1" />
            </div>
            <div>
              <label for="class_assigned" class="block mb-1">Class Assigned</label>
              <input v-model="form.class_assigned" id="class_assigned" class="input col-span-1" />
            </div>
            <div>
              <label for="joining_date" class="block mb-1">Joining Date</label>
              <input v-model="form.joining_date" type="date" id="joining_date" class="input col-span-1" />
            </div>
            <div>
              <label for="grade" class="block mb-1">Grade</label>
              <input v-model="form.grade" id="grade" class="input col-span-1" />
            </div>
            <div>
              <label for="employment_type" class="block mb-1">Employment Type</label>
              <select v-model="form.employment_type" id="employment_type" class="input col-span-1">
                <option value="full-time">Full-time</option>
                <option value="part-time">Part-time</option>
              </select>
            </div>
            <div>
              <label for="department" class="block mb-1">Department</label>
              <input v-model="form.department" id="department" class="input col-span-1" />
            </div>
          </div>
        </section>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
         <!-- Contact Information -->
        <section class="bg-gray-100 p-4 rounded shadow-sm">
          <h2 class="font-semibold mb-8 border-b-2 border-gray-300">CONTACT INFORMATION</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="phone" class="block mb-1">Phone</label>
              <input v-model="form.phone" id="phone" class="input col-span-1" />
            </div>
            <div>
              <label for="email" class="block mb-1">Email</label>
              <input v-model="form.email" type="email" id="email" class="input col-span-1" />
            </div>
            <div class="md:col-span-2">
              <label for="address" class="block mb-1">Address</label>
              <textarea v-model="form.address" id="address" class="input col-span-1 md:col-span-2"></textarea>
            </div>
          </div>
        </section>

        <!-- Other Details -->
        <section class="bg-gray-100 p-4 rounded shadow-sm">
          <h2 class="font-semibold mb-8 border-b-2 border-gray-300">OTHER DETAILS</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="emergency_contact" class="block mb-1">Emergency Contact</label>
              <input v-model="form.emergency_contact" id="emergency_contact" class="input col-span-1" />
            </div>
            <div>
              <label for="qualification" class="block mb-1">Qualification</label>
              <input v-model="form.qualification" id="qualification" class="input col-span-1" />
            </div>
            <div>
              <label for="experience" class="block mb-1">Experience (years)</label>
              <input v-model="form.experience" type="number" id="experience" class="input col-span-1" />
            </div>
            <div>
              <label for="salary" class="block mb-1">Salary</label>
              <input v-model="form.salary" type="number" step="0.01" id="salary" class="input col-span-1" />
            </div>
          </div>
        </section>

</div>

        <!-- Photo Upload -->
        <div class="w-full grid place-items-center">
          <input type="file" @change="onPhotoChange" accept="image/*" class="col-span-2 h-40 border-2 border-dashed border-green-300 rounded-lg p-4" />
          <div v-if="photoPreview" class="mt-4">
            <p class="mb-1">Preview:</p>
            <img :src="photoPreview" alt="Preview" class="h-40 rounded border" />
          </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary w-full" :disabled="loading">
          {{ loading ? 'Saving...' : 'Save Teacher' }}
        </button>
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
  background-color: #2563eb;
  color: white;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  border: none;
}
.btn-primary:disabled {
  background-color: #a5b4fc;
  cursor: not-allowed;
}
</style>
