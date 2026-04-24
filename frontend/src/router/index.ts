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
      path: '/rivers',
      name: 'rivers',
      component: () => import('../views/RiversView.vue'),
      meta: {
        requiresAuth: true,
      },
    }
  ],
})
router.beforeEach((to) => {
  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)
  const token = localStorage.getItem('auth_token')

  if (requiresAuth && !token) {
    return { name: 'login' }
  }
})

export default router
