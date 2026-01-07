import { createRouter, createWebHistory } from 'vue-router'
import { authStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      redirect: '/search'
    },
    {
      path: '/login',
      name: 'Login',
      component: () => import('../views/Login.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/register',
      name: 'Register',
      component: () => import('../views/Register.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/search',
      name: 'Search',
      component: () => import('../views/Search.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/hotel/:id',
      name: 'HotelDetail',
      component: () => import('../views/HotelDetail.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/bookings',
      name: 'MyBookings',
      component: () => import('../views/MyBookings.vue'),
      meta: { requiresAuth: true, role: 'user' }
    },
    {
      path: '/admin/bookings',
      name: 'AdminBookings',
      component: () => import('../views/admin/BookingsList.vue'),
      meta: { requiresAuth: true, role: 'hotel' }
    },
    {
      path: '/admin',
      name: 'AdminDashboard',
      component: () => import('../views/admin/Dashboard.vue'),
      meta: { requiresAuth: true, role: 'hotel' }
    },
    {
      path: '/admin/hotels',
      name: 'AdminHotels',
      component: () => import('../views/admin/Hotels.vue'),
      meta: { requiresAuth: true, role: 'hotel' }
    },
    {
      path: '/admin/rooms',
      name: 'AdminRooms',
      component: () => import('../views/admin/Rooms.vue'),
      meta: { requiresAuth: true, role: 'hotel' }
    },
    {
      path: '/admin/services',
      name: 'AdminServices',
      component: () => import('../views/admin/Services.vue'),
      meta: { requiresAuth: true, role: 'hotel' }
    }
  ]
})

router.beforeEach((to, from, next) => {
  const requiresAuth = to.meta.requiresAuth !== false
  const requiredRole = to.meta.role
  const isAuthenticated = authStore.state.isAuthenticated
  const userRole = authStore.state.user?.role

  // If route requires auth and user is not authenticated
  if (requiresAuth && !isAuthenticated) {
    next('/login')
    return
  }

  // If route requires specific role
  if (requiredRole && userRole !== requiredRole) {
    // Redirect based on role
    if (userRole === 'user') {
      next('/bookings')
    } else if (userRole === 'hotel') {
      next('/admin/bookings')
    } else {
      next('/search')
    }
    return
  }

  // If authenticated user tries to access login/register
  if (!requiresAuth && isAuthenticated) {
    if (userRole === 'user') {
      next('/bookings')
    } else if (userRole === 'hotel') {
      next('/admin/bookings')
    } else {
      next('/search')
    }
    return
  }

  next()
})

export default router


