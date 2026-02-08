import { createRouter, createWebHistory } from 'vue-router';
import Login from '../Pages/Login.vue';
import Register from '../Pages/Register.vue';

//.................................Mater Admin Dashboard....................................
import MasterDashboard from '../Pages/dashboard/MasterDashboard.vue';
import ManageSchools from '../components/MasterAdminMenu/ManageSchools.vue';

//.................................HeadMater Admin Dashboard................................
import HeadmasterDashboard from '../Pages/dashboard/HeadmasterDashboard.vue';
// Classes
import SchoolClasses from '../components/HeadmasterMenu/class/SchoolClasses.vue';
//Section Routes
import Section from '../components/HeadmasterMenu/section/Section.vue';
// Subject Routes
import SubjectList from '../components/HeadmasterMenu/subject/SubjectList.vue';
// Student Routes
import StudentAdmission from '../components/HeadmasterMenu/student/StudentAdmission.vue';
import StudentList from '../components/HeadmasterMenu/student/StudentList.vue';
import StudentAttendance from '../components/HeadmasterMenu/student/StudentAttendance.vue';
//teacher routes
import AddTeacher from '../components/HeadmasterMenu/teacher/AddTeacher.vue';
import TeacherList from '../components/HeadmasterMenu/teacher/TeacherList.vue';
import TeacherAttendance from '../components/HeadmasterMenu/teacher/TeacherAttendance.vue';
//Notices
import NoticeList from '../components/HeadmasterMenu/notices/NoticeList.vue';
//events
import EventsList from '../components/HeadmasterMenu/events/EventList.vue';
import TeacherEventsCalender from '../Pages/teacher/TeacherEvents.vue';
import StudentEventsCalender from '../Pages/student/StudentEvents.vue';
//AcademicYear
import AcademicYear from '../components/HeadmasterMenu/academicYear/AcademicYear.vue';


//.................................Teacher Dashboard.......................................
import TeacherDashboard from '../Pages/dashboard/TeacherDashboard.vue';

//.................................Student Dashboard.......................................
import StudentDashboard from '../Pages/dashboard/StudentDashboard.vue';


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

  //.................................Mater Admin Dashboard....................................
  { path: '/master-dashboard', component: MasterDashboard, beforeEnter: requireAuth },
  { path: '/manage-schools', component: ManageSchools, beforeEnter: requireAuth },

  //.................................HeadMater Admin Dashboard................................
  { path: '/headmaster-dashboard', component: HeadmasterDashboard, beforeEnter: requireAuth },
  // Classes
  { path: '/school-classes', component: SchoolClasses, beforeEnter: requireAuth },
  //Section Routes
  { path: '/sections', component: Section, beforeEnter: requireAuth },
  // Subject Routes
  { path: '/subjects', component: SubjectList, beforeEnter: requireAuth },
  // Student Routes
  { path: '/student-admission', component: StudentAdmission, beforeEnter: requireAuth },
  { path: '/students-list', component: StudentList, beforeEnter: requireAuth },
  { path: '/student-attendance', component: StudentAttendance, beforeEnter: requireAuth },
  //teacher routes
  { path: '/teachers-add', component: AddTeacher, beforeEnter: requireAuth },
  { path: '/teachers-list', component: TeacherList, beforeEnter: requireAuth },
  { path: '/teacher-attendance', component: TeacherAttendance, beforeEnter: requireAuth },
  //Notices
  { path: '/notices', component: NoticeList, beforeEnter: requireAuth },
  //Events
  { path: '/events-list', component: EventsList, beforeEnter: requireAuth },
  { path: '/events-calender-teacher', component: TeacherEventsCalender, beforeEnter: requireAuth },
  { path: '/events-calender-student', component: StudentEventsCalender, beforeEnter: requireAuth },


//   {
//   path: '/events-list',
//   component: () => {
//     const role = localStorage.getItem('role')

//     if (role === 'headmaster') {
//       return import('../components/HeadmasterMenu/events/EventList.vue')
//     }

//     if (role === 'teacher') {
//       return import('../Pages/teacher/TeacherEvents.vue')
//     }

//     return import('../Pages/student/StudentEvents.vue')
//   },
//   beforeEnter: requireAuth
// },


  //AcademicYear
  { path: '/academic-year', component: AcademicYear, beforeEnter: requireAuth },

  //.................................Teacher Dashboard........................................
  { path: '/teacher-dashboard', component: TeacherDashboard, beforeEnter: requireAuth },

  //.................................Student Dashboard........................................
  { path: '/student-dashboard', component: StudentDashboard, beforeEnter: requireAuth },


]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
