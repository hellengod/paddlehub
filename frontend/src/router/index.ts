import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: LoginView,
    },
    {
      path:'/rivers',
      name:'rivers',
      component: () => import('../views/RiversView.vue'),
    }
  ],
})

export default router
