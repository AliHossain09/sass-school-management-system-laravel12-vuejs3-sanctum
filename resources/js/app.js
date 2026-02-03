import './bootstrap';


import { createApp } from 'vue';
import router from './router'; 
import App from './App.vue';
// Create Vue app toaster
import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'

const app = createApp(App);

app.use(router);


// Register toast plugin
app.use(Toast, {
  position: 'top-right',
  timeout: 3000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
});
 
app.mount('#app');