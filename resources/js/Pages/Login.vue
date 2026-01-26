<template>
  <div class="container mx-auto mt-10">
    <h1 class="text-2xl font-bold mb-5">Login</h1>
    <form @submit.prevent="login">
      <input v-model="email" type="email" placeholder="Email" class="border p-2 w-full mb-3"/>
      <input v-model="password" type="password" placeholder="Password" class="border p-2 w-full mb-3"/>
      <button class="bg-blue-500 text-white px-4 py-2">Login</button>
    </form>
  </div>
</template>


<script>
import axios from 'axios';
import router from '../router';


export default {
  data() {
    return {
      email: '',
      password: '',
    };
  },
  methods: {
    async login() {
      try {
        const res = await axios.post('/api/login', {
          email: this.email,
          password: this.password,
        });
        localStorage.setItem('token', res.data.data.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${res.data.data.token}`;
        router.push('/dashboard');
      } catch (err) {
        alert(err.response.data.message || 'Login failed');
      }
    },
  },
};
</script>
