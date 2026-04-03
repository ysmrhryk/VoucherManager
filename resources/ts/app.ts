import './bootstrap';
import '../scss/app.scss'
import '../css/app.css'
import * as bootstrap from 'bootstrap'
import { createApp } from 'vue'
import router from "./router"
import App from '../vue/App.vue'
import { createPinia } from 'pinia'

const app = createApp(App)
app.use(router).use(createPinia()).mount("#app")