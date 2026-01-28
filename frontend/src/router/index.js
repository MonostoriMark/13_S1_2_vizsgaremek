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
      path: '/forgot-password',
      name: 'ForgotPassword',
      component: () => import('../views/ForgotPassword.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/reset-password/:token?',
      name: 'ResetPassword',
      component: () => import('../views/ResetPassword.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/verify-email/:token',
      name: 'VerifyEmail',
      component: () => import('../views/VerifyEmail.vue'),
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
      path: '/profile',
      name: 'Profile',
      component: () => import('../views/Profile.vue'),
      meta: { requiresAuth: true }
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
    },
    {
      path: '/admin/tags',
      name: 'AdminTags',
      component: () => import('../views/admin/Tags.vue'),
      meta: { requiresAuth: true, role: 'hotel' }
    },
    {
      path: '/admin/rfid-keys',
      name: 'AdminRFIDKeys',
      component: () => import('../views/admin/RFIDKeys.vue'),
      meta: { requiresAuth: true, role: 'hotel' }
    },
    {
      path: '/admin/users',
      name: 'AdminUsers',
      component: () => import('../views/admin/Users.vue'),
      meta: { requiresAuth: true, role: 'hotel' }
    },
    {
      path: '/two-factor-auth',
      name: 'TwoFactorAuth',
      component: () => import('../views/TwoFactorAuth.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/two-factor-recovery',
      name: 'TwoFactorRecoveryRequest',
      component: () => import('../views/TwoFactorRecoveryRequest.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/two-factor-recovery/:token',
      name: 'TwoFactorRecoveryConfirm',
      component: () => import('../views/TwoFactorRecoveryConfirm.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/super-admin/dashboard',
      name: 'SuperAdminDashboard',
      component: () => import('../views/super-admin/Dashboard.vue'),
      meta: { requiresAuth: true, role: 'super_admin' }
    },
    {
      path: '/super-admin/users',
      name: 'SuperAdminUsers',
      component: () => import('../views/super-admin/Users.vue'),
      meta: { requiresAuth: true, role: 'super_admin' }
    },
    {
      path: '/super-admin/hotels',
      name: 'SuperAdminHotels',
      component: () => import('../views/super-admin/Hotels.vue'),
      meta: { requiresAuth: true, role: 'super_admin' }
    },
    {
      path: '/super-admin/rooms',
      name: 'SuperAdminRooms',
      component: () => import('../views/super-admin/Rooms.vue'),
      meta: { requiresAuth: true, role: 'super_admin' }
    },
    {
      path: '/super-admin/services',
      name: 'SuperAdminServices',
      component: () => import('../views/super-admin/Services.vue'),
      meta: { requiresAuth: true, role: 'super_admin' }
    },
    {
      path: '/super-admin/bookings',
      name: 'SuperAdminBookings',
      component: () => import('../views/super-admin/Bookings.vue'),
      meta: { requiresAuth: true, role: 'super_admin' }
    },
    {
      path: '/super-admin/invoices',
      name: 'SuperAdminInvoices',
      component: () => import('../views/super-admin/Invoices.vue'),
      meta: { requiresAuth: true, role: 'super_admin' }
    },
    {
      path: '/super-admin/rfid-keys',
      name: 'SuperAdminRFIDKeys',
      component: () => import('../views/super-admin/RFIDKeys.vue'),
      meta: { requiresAuth: true, role: 'super_admin' }
    },
    {
      path: '/super-admin/devices',
      name: 'SuperAdminDevices',
      component: () => import('../views/super-admin/Devices.vue'),
      meta: { requiresAuth: true, role: 'super_admin' }
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
    // Super admin can access everything
    if (userRole === 'super_admin') {
      next()
      return
    }
    // Redirect based on role
    if (userRole === 'user') {
      next('/bookings')
    } else if (userRole === 'hotel') {
      next('/admin/bookings')
    } else if (userRole === 'super_admin') {
      next('/super-admin/dashboard')
    } else {
      next('/search')
    }
    return
  }

  // Force 2FA for hotel admins (except profile page)
  if (userRole === 'hotel' && to.path.startsWith('/admin') && to.path !== '/admin/users') {
    const twoFactorEnabled = authStore.state.user?.two_factor_enabled
    if (!twoFactorEnabled) {
      // Redirect to profile to enable 2FA
      next('/admin/users')
      return
    }
  }

  // If authenticated user tries to access login/register, redirect them
  if ((to.path === '/login' || to.path === '/register') && isAuthenticated) {
    if (userRole === 'super_admin') {
      next('/super-admin/dashboard')
    } else if (userRole === 'user') {
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


