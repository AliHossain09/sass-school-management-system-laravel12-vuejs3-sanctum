import { createRouter, createWebHistory } from 'vue-router';
import Login from '../Pages/Login.vue';
import Register from '../Pages/Register.vue';
import MasterDashboard from '../Pages/dashboard/MasterDashboard.vue';
import HeadmasterDashboard from '../Pages/dashboard/HeadmasterDashboard.vue';
import TeacherDashboard from '../Pages/dashboard/TeacherDashboard.vue';
import StudentDashboard from '../Pages/dashboard/StudentDashboard.vue';
import ManageSchools from '../components/MasterAdminMenu/ManageSchools.vue';
// Classes
import SchoolClasses from '../components/HeadmasterMenu/class/SchoolClasses.vue';
//Section Routes
import Section from '../components/HeadmasterMenu/section/Section.vue';
// Student Routes
import StudentAdmission from '../components/HeadmasterMenu/student/StudentAdmission.vue';
import StudentList from '../components/HeadmasterMenu/student/StudentList.vue';
import StudentAttendance from '../components/HeadmasterMenu/student/StudentAttendance.vue';
//teacher routes
import AddTeacher from '../components/HeadmasterMenu/teacher/AddTeacher.vue';
import TeacherList from '../components/HeadmasterMenu/teacher/TeacherList.vue';
import TeacherAttendance from '../components/HeadmasterMenu/teacher/TeacherAttendance.vue';
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
  // Classes
  { path: '/school-classes', component: SchoolClasses, beforeEnter: requireAuth },
  //Section Routes
  { path: '/sections', component: Section, beforeEnter: requireAuth },
  // Student Routes
  { path: '/student-admission', component: StudentAdmission, beforeEnter: requireAuth },
  { path: '/students-list', component: StudentList, beforeEnter: requireAuth },
  { path: '/student-attendance', component: StudentAttendance, beforeEnter: requireAuth },
  //teacher routes
  { path: '/teachers-add', component: AddTeacher, beforeEnter: requireAuth },
  { path: '/teachers-list', component: TeacherList, beforeEnter: requireAuth },
  { path: '/teacher-attendance', component: TeacherAttendance, beforeEnter: requireAuth },

]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
