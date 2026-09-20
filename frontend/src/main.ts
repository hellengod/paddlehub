import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import {useAuth} from './composables/useAuth.ts'
import { setUnauthorizedHandler } from './services/apiClient.ts'

const app = createApp(App)
const { initializeAuth, handleUnauthorized } = useAuth()

setUnauthorizedHandler(() => {
  handleUnauthorized()

  if (router.currentRoute.value.matched.some((record) => record.meta.requiresAuth)) {
    void router.replace({ name: 'login' })
  }
})

void initializeAuth()

app.use(router)

app.mount('#app')
