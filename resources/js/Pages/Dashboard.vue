<template>
  <div class="container mx-auto mt-10">
    <h1 class="text-2xl font-bold mb-5">Dashboard</h1>
    <p>Welcome, {{ user.name }}</p>
    <button @click="logout" class="bg-red-500 text-white px-4 py-2 mt-5">Logout</button>
  </div>
</template>


<script>
import axios from 'axios';
import router from '../router';


export default {
  data() {
    return {
      user: {},
    };
  },
  async created() {
    try {
      const res = await axios.get('/api/user');
      this.user = res.data;
    } catch (err) {
      router.push('/login');
    }
  },
  methods: {
    async logout() {
      try {
        await axios.post('/api/logout');
        localStorage.removeItem('token');
        router.push('/login');
      } catch (err) {
        console.error(err);
      }
    },
  },
};
</script>
