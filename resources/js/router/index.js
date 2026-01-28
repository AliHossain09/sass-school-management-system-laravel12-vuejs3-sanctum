import { createRouter, createWebHistory } from 'vue-router';
import Login from '../Pages/Login.vue';
import Register from '../Pages/Register.vue';
import MasterDashboard from '../Pages/dashboard/MasterDashboard.vue';
import HeadmasterDashboard from '../Pages/dashboard/HeadmasterDashboard.vue';
import TeacherDashboard from '../Pages/dashboard/TeacherDashboard.vue';
import StudentDashboard from '../Pages/dashboard/StudentDashboard.vue';
import ManageSchools from '../components/MasterAdminMenu/ManageSchools.vue';
import axios from 'axios';

const requireAuth = (to, from, next) => {
  const token = localStorage.getItem('token')
  if (!token) return next('/login')
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  next()
}

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  { path: '/register', component: Register },

  { path: '/master-dashboard', component: MasterDashboard, beforeEnter: requireAuth },
  { path: '/headmaster-dashboard', component: HeadmasterDashboard, beforeEnter: requireAuth },
  { path: '/teacher-dashboard', component: TeacherDashboard, beforeEnter: requireAuth },
  { path: '/student-dashboard', component: StudentDashboard, beforeEnter: requireAuth },
  { path: '/manage-schools', component: ManageSchools, beforeEnter: requireAuth },

]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
