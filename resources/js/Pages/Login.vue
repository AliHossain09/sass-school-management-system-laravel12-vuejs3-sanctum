<template>
  <div class="relative min-h-screen flex items-center justify-center overflow-hidden">

    <!-- Background Image -->
    <div class="absolute inset-0 -z-20">
      <img
        src="https://schoolmanagement.co.in/assets/17948.jpg"
        alt="School Background"
        class="w-full h-full object-cover blur-sm scale-110"
      />
    </div>

    <!-- Gradient Overlay -->
    <div class="absolute inset-0 -z-10 bg-gradient-to-br
      from-indigo-900/50 via-purple-900/40 to-black/50">
    </div>

    <!-- Login Card -->
    <div class="max-w-xl w-full mx-4 p-10 rounded-2xl
      bg-white/10 backdrop-blur-md
      shadow-[0_20px_50px_rgba(0,0,0,0.25)]">

      <!-- Title -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-white">
          School Management System
        </h1>
        <p class="text-gray-300 mt-2">
          Login to your account
        </p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="login">

        <!-- Email -->
        <div class="relative mt-6">
          <input
            v-model="email"
            type="text"
            placeholder="Email or Phone"
            autocomplete="off"
            class="peer w-full bg-transparent border-b-2 border-gray-400 px-0 py-2
            text-white placeholder:text-transparent
            focus:border-indigo-500 focus:outline-none"
          />
          <label
            class="pointer-events-none absolute top-0 left-0 -translate-y-1/2
            text-sm text-white transition-all
            peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base
            peer-placeholder-shown:text-gray-300
            peer-focus:top-0 peer-focus:text-sm peer-focus:text-white">
            Email or Phone
          </label>
        </div>

        <!-- Password -->
        <div class="relative mt-8">
          <input
            v-model="password"
            type="password"
            placeholder="Password"
            autocomplete="new-password"
            class="peer w-full bg-transparent border-b-2 border-gray-400 px-0 py-2
            text-white placeholder:text-transparent
            focus:border-indigo-500 focus:outline-none"
          />
          <label
            class="pointer-events-none absolute top-0 left-0 -translate-y-1/2
            text-sm text-white transition-all
            peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base
            peer-placeholder-shown:text-gray-300
            peer-focus:top-0 peer-focus:text-sm peer-focus:text-white">
            Password / PIN
          </label>
        </div>

        <!-- Button -->
        <div class="flex justify-center mt-10">
          <button
            type="submit"
            class="text-white bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600
            hover:scale-105 transition-transform
            focus:ring-4 focus:ring-indigo-300
            font-medium rounded-lg text-sm px-12 py-3">
            Login
          </button>
        </div>
      </form>

      <!-- Footer -->
      <p class="text-center text-sm text-gray-300 mt-8">
        © 2025 School Management System
      </p>
    </div>

    <!-- Success Popup -->
    <div
      v-if="success"
      class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
      <div class="bg-white rounded-xl p-8 text-center shadow-2xl animate-pulse">
        <h2 class="text-2xl font-bold text-indigo-600">
          Welcome to Your School
        </h2>
        <p class="text-gray-600 mt-2">
          School Management System
        </p>
      </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios'
import router from '../router'

export default {
  name: 'Login',
  data() {
    return {
      email: '',
      password: '',
      success: false,
    }
  },
  methods: {
    async login() {
      try {
        const res = await axios.post('/api/login', {
          email: this.email,
          password: this.password,
        })

        // Destructure data
        const { user, token } = res.data.data

        // Save token and set header
        localStorage.setItem('token', token)
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

        // Show success popup
        this.success = true

        // Redirect based on role
        setTimeout(() => {
          if (user.role === 'master' || user.role === 'master_admin') router.push('/master-dashboard')
          else if (user.role === 'headmaster') router.push('/headmaster-dashboard')
          else if (user.role === 'teacher') router.push('/teacher-dashboard')
          else router.push('/student-dashboard')
        }, 800)

      } catch (err) {
        alert(err.response?.data?.message || 'Login failed')
      }
    }
  }
}
</script>
