import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import axios from 'axios'

axios.defaults.baseURL = 'http://localhost/licitaciones/backend/public/';

const app = createApp(App)
app.config.globalProperties.$storageUrl = 'http://localhost/licitaciones/backend/public/'
app.mount('#app')
