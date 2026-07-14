import { createRouter, createWebHistory } from 'vue-router'
import AuthView from '@/views/AuthView.vue'
import useAuth from '@/composable/useAuth'

const { authState, initializeAuth } = useAuth();
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
      path: '/app',
      name: 'app',
      component: () => import('@/components/layouts/AppLayout.vue'),
      children: [
        {
          path: '/home',
          name: 'home',
          component: () => import('../views/HomeView.vue'),
          meta: {
            requiresAuth: true,
          },
        },
        {
          path: '/mapa',
          name: 'mapa',
          component: () => import('../views/MapView.vue'),
          meta: {
            requiresAuth: true,
          },
        },
        {
          path: '/eventos',
          name: 'eventos',
          component: () => import('../views/EventView.vue'),
          meta: {
            requiresAuth: true,
          },
        },
        {
          path: '/rios',
          name: 'rios',
          component: () => import('../views/RiverView.vue'),
          meta: {
            requiresAuth: true,
          },
        },
        {
          path: '/community',
          name: 'community',
          component: () => import('../views/CommunityView.vue'),
          meta: {
            requiresAuth: true,
          },
        },
        {
          path: '/perfil',
          name: 'perfil',
          component: () => import('../views/ProfileView.vue'),
          meta: {
            requiresAuth: true,
          },
        }
      ]
    },
  ],
})
router.beforeEach(async (to) => {
  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)
  const guestOnly = to.matched.some((record) => record.meta.guestOnly)

  if ((requiresAuth || guestOnly) && authState.status === 'unknown') {
    await initializeAuth()
  }

  if (requiresAuth && authState.status !== 'authenticated') {
    return { name: 'login' }
  }

  if (guestOnly && authState.status === 'authenticated') {
    return { name: 'home' }
  }
})



export default router
