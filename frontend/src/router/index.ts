import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import useAuth from '@/composable/useAuth'

const {validateToken} = useAuth();
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
router.beforeEach(async (to) => {
  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)
  const token = await validateToken()
  const isLoginRoute = to.name === 'login'

  if (requiresAuth && !token) {
    return { name: 'login' }
  }  
  
  if (isLoginRoute && token) {
    return { name: 'rivers' }
  }
})


export default router
