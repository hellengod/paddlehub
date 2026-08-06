import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import {useAuth} from './composables/useAuth.ts'

const app = createApp(App)
const { initializeAuth } = useAuth()

void initializeAuth()

app.use(router)

app.mount('#app')
