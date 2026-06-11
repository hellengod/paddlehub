import { createRouter, createWebHistory } from 'vue-router'
import AuthView from '@/views/AuthView.vue'
import useAuth from '@/composable/useAuth'

const { validateToken } = useAuth();
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: AuthView,
      meta: {
        authMode: 'login',
        guestOnly: true,
      },
    },
    {
      path: '/cadastro',
      name: 'register',
      component: AuthView,
      meta: {
        authMode: 'register',
        guestOnly: true,
      },
    },
    {
      path: '/home',
      name: 'home',
      component: () => import('../views/HomeView.vue'),
      meta: {
        requiresAuth: true,
      },
    }
  ],
})
router.beforeEach(async (to) => {
  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)
  const guestOnly = to.matched.some((record) => record.meta.guestOnly)
  const isAuthenticated = await validateToken()

  if (requiresAuth && !isAuthenticated) {
    return { name: 'login' }
  }

  if (guestOnly && isAuthenticated) {
    return { name: 'home' }
  }
})



export default router
