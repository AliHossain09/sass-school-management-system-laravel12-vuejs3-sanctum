import { createRouter, createWebHistory } from 'vue-router';
import Login from '../Pages/Login.vue';
import Register from '../Pages/Register.vue';
import Dashboard from '../Pages/Dashboard.vue';
import axios from 'axios';


// Protect dashboard route
const requireAuth = async (to, from, next) => {
    try {
        await axios.get('/api/user'); // call Laravel auth endpoint
        next();
    } catch (error) {
        next('/login');
    }
};


const routes = [
    { path: '/', redirect: '/login' },
    { path: '/login', component: Login },
    { path: '/register', component: Register },
    { path: '/dashboard', component: Dashboard, beforeEnter: requireAuth },
];


const router = createRouter({
    history: createWebHistory(),
    routes,
});


export default router; 