import { createRouter, createWebHistory } from 'vue-router';
import Login from '../Pages/Login.vue';
import Register from '../Pages/Register.vue';

//.................................Mater Admin Dashboard....................................
import MasterDashboard from '../Pages/dashboard/MasterDashboard.vue';
//Create school
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
//AcademicYear
import AcademicYear from '../components/HeadmasterMenu/academicYear/AcademicYear.vue';
//Class Routine
import ClassRoutine from '../components/HeadmasterMenu/routine/ClassRoutineList.vue';


//.................................Teacher Dashboard.......................................
import TeacherDashboard from '../Pages/dashboard/TeacherDashboard.vue';
//events
import TeacherEventsCalender from '../components/TeacherMenu/events/TeacherEvents.vue';
//notices
import TeacherNotice from '../components/TeacherMenu/notice/TeacherNotice.vue';
//Routine
import TeacherRoutine from '../components/TeacherMenu/routine/TeacherRoutine.vue';

//.................................Student Dashboard.......................................
import StudentDashboard from '../Pages/dashboard/StudentDashboard.vue';
//events
import StudentEventsCalender from '../components/StudentMenu/events/StudentEvents.vue';
//notices
import StudentNotice from '../components/StudentMenu/notice/StudentNotice.vue';
//Routine
import StudentRoutine from '../components/StudentMenu/routine/StudentRoutine.vue';


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
  //AcademicYear
  { path: '/academic-year', component: AcademicYear, beforeEnter: requireAuth },
  //Class Routine
  { path: '/class-routine', component: ClassRoutine, beforeEnter: requireAuth },
  
  //.................................Teacher Dashboard........................................
  { path: '/teacher-dashboard', component: TeacherDashboard, beforeEnter: requireAuth },
  { path: '/events-calender-teacher', component: TeacherEventsCalender, beforeEnter: requireAuth },
  { path: '/notices-teacher', component: TeacherNotice, beforeEnter: requireAuth },
  { path: '/routine-teacher', component: TeacherRoutine, beforeEnter: requireAuth },

  //.................................Student Dashboard........................................
  { path: '/student-dashboard', component: StudentDashboard, beforeEnter: requireAuth },
  { path: '/events-calender-student', component: StudentEventsCalender, beforeEnter: requireAuth },
  { path: '/notices-student', component: StudentNotice, beforeEnter: requireAuth },
  { path: '/routine-student', component: StudentRoutine, beforeEnter: requireAuth },


]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
