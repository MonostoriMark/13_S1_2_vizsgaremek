<template>
  <div class="admin-layout">
    <Admin2FABlocker
      :visible="show2FABlocker"
      @enable="handleEnable2FA"
    />
    
    <InvoiceDataBlocker
      :visible="showInvoiceDataBlocker"
      :missing-hotels="missingBillingHotels"
      @complete="handleInvoiceDataComplete"
    />
    
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed, 'blocked': show2FABlocker || showInvoiceDataBlocker }">
      <div class="sidebar-header">
        <div class="user-profile-sidebar">
          <div class="user-avatar-sidebar">{{ getUserInitials }}</div>
          <div v-if="!sidebarCollapsed" class="user-info-sidebar">
            <div class="logo-text">HOTELFLOW</div>
            <div class="logo-subtitle">Admin felület</div>
          </div>
        </div>
        <button class="sidebar-toggle" @click="toggleSidebar" :title="sidebarCollapsed ? 'Oldalsáv kibontása' : 'Oldalsáv összecsukása'">
          <svg class="toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path v-if="!sidebarCollapsed" d="M15 18l-6-6 6-6"/>
            <path v-else d="M9 18l6-6-6-6"/>
          </svg>
        </button>
      </div>
      <nav class="sidebar-nav">
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          class="nav-item"
          :title="item.label"
          @click="handleNavClick($event, item.path)"
        >
          <span class="nav-icon">{{ item.icon }}</span>
          <span v-if="!sidebarCollapsed" class="nav-label">{{ item.label }}</span>
        </router-link>
      </nav>
      <div class="sidebar-footer">
        <router-link
          to="/admin/bookings"
          class="nav-item bookings-nav-item"
          :title="sidebarCollapsed ? 'Bookings' : ''"
          @click="handleNavClick($event, '/admin/bookings')"
        >
          <span class="nav-icon">📅</span>
          <span v-if="!sidebarCollapsed" class="nav-label">Foglalások</span>
        </router-link>
        <button @click="handleLogout" class="logout-btn" :title="sidebarCollapsed ? 'Kijelentkezés' : ''" :disabled="show2FABlocker">
          <span class="nav-icon">🚪</span>
          <span v-if="!sidebarCollapsed">Kijelentkezés</span>
        </button>
      </div>
    </aside>

    <div class="main-content">
      <header class="topbar">
        <div class="topbar-content">
          <h1 class="page-title">{{ pageTitle }}</h1>
          <div class="topbar-actions">
            <div v-if="hotelApprovalStatus !== null" class="approval-status" :class="hotelApprovalStatus ? 'approved' : 'pending'">
              <span class="status-icon">{{ hotelApprovalStatus ? '✅' : '⏳' }}</span>
              <span class="status-text">{{ hotelApprovalStatus ? 'Jóváhagyva' : 'Jóváhagyásra vár' }}</span>
            </div>
            <div class="user-profile">
              <span class="user-avatar">{{ getUserInitials }}</span>
              <span class="user-name">{{ userName }}</span>
            </div>
          </div>
        </div>
      </header>

      <!-- Invoice Data Notification Banner -->
      <div v-if="showInvoiceDataNotification && !showInvoiceDataBlocker" class="invoice-data-notification">
        <div class="notification-content">
          <span class="notification-icon">⚠️</span>
          <div class="notification-text">
            <strong>Fontos:</strong> Kérjük, töltse ki a számlázási adatokat (adószám, bankszámlaszám, EU adószám) minden szállodához a teljes funkcionalitás érdekében.
            <router-link to="/admin/company-info" class="notification-link">Számlázási adatok kitöltése →</router-link>
          </div>
          <button @click="dismissInvoiceNotification" class="notification-close" title="Bezárás">×</button>
        </div>
      </div>

      <div class="content-area" :class="{ 'blocked': show2FABlocker || showInvoiceDataBlocker }">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import Admin2FABlocker from '../components/Admin2FABlocker.vue'
import InvoiceDataBlocker from '../components/InvoiceDataBlocker.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const sidebarCollapsed = ref(false)
const show2FABlocker = ref(false)

const menuItems = [
  { path: '/admin', label: 'Irányítópult', icon: '📊' },
  { path: '/admin/hotels', label: 'Szállodák', icon: '🏨' },
  { path: '/admin/rooms', label: 'Szobák', icon: '🛏️' },
  { path: '/admin/services', label: 'Szolgáltatások', icon: '✨' },
  { path: '/admin/tags', label: 'Címkék', icon: '🏷️' },
  { path: '/admin/rfid-keys', label: 'RFID kulcsok', icon: '🔑' },
  { path: '/admin/company-info', label: 'Cégadatok', icon: '💼' },
  { path: '/admin/users', label: 'Profilom', icon: '👤' }
]

const pageTitle = computed(() => {
  if (route.path === '/admin/bookings') {
    return 'Bookings'
  }
  if (route.path === '/admin/users') {
    return 'My Profile'
  }
  const item = menuItems.find(i => i.path === route.path)
  return item ? item.label : 'Admin Panel'
})

const userName = computed(() => authStore.state.user?.name || 'Admin')
const getUserInitials = computed(() => {
  if (!userName.value) return 'A'
  const names = userName.value.split(' ')
  if (names.length >= 2) {
    return (names[0][0] + names[names.length - 1][0]).toUpperCase()
  }
  return userName.value[0].toUpperCase()
})

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const handleEnable2FA = () => {
  // Navigate to profile to enable 2FA
  // The profile page will auto-open the 2FA setup modal
  router.push('/admin/users')
  show2FABlocker.value = false
}

const handleInvoiceDataComplete = async () => {
  // Reload user data to check if invoice data is now complete
  try {
    const { authService } = await import('../services/authService')
    const userData = await authService.getMe()
    if (authStore.state.user) {
      Object.assign(authStore.state.user, userData)
      localStorage.setItem('auth_user', JSON.stringify(authStore.state.user))
    }
    showInvoiceDataBlocker.value = false
  } catch (err) {
    console.error('Failed to reload user data:', err)
  }
}

const handleNavClick = (event, path) => {
  // Block navigation if 2FA is not enabled (except profile page)
  if (show2FABlocker.value && path !== '/admin/users') {
    event.preventDefault()
    event.stopPropagation()
    return false
  }
}

const showInvoiceDataBlocker = ref(false)
const showInvoiceDataNotification = ref(false)
const missingBillingHotels = ref([])
const hotelApprovalStatus = ref(null)

const hasCompleteInvoiceData = async () => {
  // Check if all hotels owned by the user have complete billing information
  try {
    const { adminService } = await import('../services/adminService')
    const hotelsData = await adminService.getHotels()
    const hotels = Array.isArray(hotelsData) ? hotelsData : (hotelsData.hotels || [])
    
    // Filter to only hotels owned by current user
    const userHotels = hotels.filter(hotel => {
      const hotelUserId = hotel.user_id || hotel.user?.id
      const currentUserId = authStore.state.user?.id
      return hotelUserId && currentUserId && (hotelUserId === currentUserId || hotelUserId == currentUserId)
    })
    
    // If user has no hotels, they can't access admin features anyway
    if (userHotels.length === 0) {
      missingBillingHotels.value = []
      return false
    }
    
    // Find hotels missing billing info
    const missing = userHotels.filter(hotel => 
      !hotel.tax_number || !hotel.bank_account || !hotel.eu_tax_number
    )
    
    missingBillingHotels.value = missing
    
    return missing.length === 0
  } catch (err) {
    console.error('Failed to check hotel billing info:', err)
    missingBillingHotels.value = []
    return false
  }
}

const check2FARequirement = async () => {
  // Check if user is hotel admin and doesn't have 2FA enabled
  if (authStore.state.user?.role === 'hotel' && !authStore.state.user?.two_factor_enabled) {
    // Allow access to profile page to enable 2FA
    if (route.path === '/admin/users') {
      show2FABlocker.value = false
      return
    }
    // Block all other admin pages
    show2FABlocker.value = true
    // Redirect to profile if trying to access other pages
    if (route.path !== '/admin/users') {
      router.push('/admin/users')
    }
  } else {
    show2FABlocker.value = false
    
    // After 2FA is enabled, check invoice data
    if (authStore.state.user?.role === 'hotel' && authStore.state.user?.two_factor_enabled) {
      // Check if all hotels have complete billing info
      const hasComplete = await hasCompleteInvoiceData()

      if (!hasComplete) {
        const dismissed = localStorage.getItem('invoice_data_notification_dismissed')

        if (route.path === '/admin/company-info') {
          // On company info page: do NOT block, just show non-blocking notification
          showInvoiceDataBlocker.value = false
          showInvoiceDataNotification.value = !dismissed
        } else {
          // On all other admin pages: block all operations, only blocker is clickable
          showInvoiceDataBlocker.value = true
          showInvoiceDataNotification.value = false
        }
      } else {
        // All invoice data complete: no blocker, no notification
        showInvoiceDataBlocker.value = false
        showInvoiceDataNotification.value = false
        localStorage.removeItem('invoice_data_notification_dismissed')
      }
    }
  }
}

// Watch for route changes
watch(() => route.path, () => {
  check2FARequirement()
})

// Watch for user 2FA status changes
watch(() => authStore.state.user?.two_factor_enabled, () => {
  check2FARequirement()
})

const dismissInvoiceNotification = () => {
  showInvoiceDataNotification.value = false
  localStorage.setItem('invoice_data_notification_dismissed', 'true')
}

const loadHotelApprovalStatus = async () => {
  try {
    const { adminService } = await import('../services/adminService')
    const hotelsData = await adminService.getHotels()
    const hotels = Array.isArray(hotelsData) ? hotelsData : (hotelsData.hotels || [])
    
    // Get first hotel owned by current user
    const userHotel = hotels.find(hotel => {
      const hotelUserId = hotel.user_id || hotel.user?.id
      const currentUserId = authStore.state.user?.id
      return hotelUserId && currentUserId && (hotelUserId === currentUserId || hotelUserId == currentUserId)
    })
    
    if (userHotel) {
      hotelApprovalStatus.value = userHotel.is_approved ?? false
    } else {
      hotelApprovalStatus.value = null
    }
  } catch (err) {
    console.error('Failed to load hotel approval status:', err)
    hotelApprovalStatus.value = null
  }
}

onMounted(async () => {
  // Check if user is authenticated and has hotel role
  if (!authStore.state.isAuthenticated || authStore.state.user?.role !== 'hotel') {
    router.push('/login')
    return
  }
  
  // Load fresh user data to check 2FA status
  try {
    const { authService } = await import('../services/authService')
    const userData = await authService.getMe()
    if (authStore.state.user) {
      Object.assign(authStore.state.user, {
        two_factor_enabled: userData.two_factor_enabled || false
      })
      localStorage.setItem('auth_user', JSON.stringify(authStore.state.user))
    }
  } catch (err) {
    console.error('Failed to load user data:', err)
  }
  
  // Load hotel approval status
  await loadHotelApprovalStatus()
  
  // Check 2FA requirement after a short delay to ensure route is ready
  setTimeout(() => {
    check2FARequirement()
  }, 100)
  
  // Listen for hotel billing data updates from CompanyInfo page
  window.addEventListener('hotel-billing-updated', async () => {
    // Recheck billing data completion
    check2FARequirement()
  })
  
  // Listen for hotel approval status updates
  window.addEventListener('hotel-approval-updated', async () => {
    await loadHotelApprovalStatus()
  })
  
  // Check invoice data notification on mount
  if (authStore.state.user?.role === 'hotel' && authStore.state.user?.two_factor_enabled) {
    setTimeout(async () => {
      const hasComplete = await hasCompleteInvoiceData()
      if (!hasComplete) {
        const dismissed = localStorage.getItem('invoice_data_notification_dismissed')
        if (!dismissed) {
          showInvoiceDataNotification.value = true
        }
      }
    }, 1000)
  }
})

// Watch for route changes to recheck billing data
watch(() => route.path, async () => {
  if (route.path === '/admin/company-info') {
    // When user navigates to company-info page, recheck after a delay
    setTimeout(() => {
      check2FARequirement()
    }, 500)
  } else {
    // Recheck invoice data when navigating away from company-info
    if (authStore.state.user?.role === 'hotel' && authStore.state.user?.two_factor_enabled) {
      setTimeout(async () => {
        const hasComplete = await hasCompleteInvoiceData()
        if (!hasComplete) {
          const dismissed = localStorage.getItem('invoice_data_notification_dismissed')
          if (!dismissed && route.path !== '/admin/company-info') {
            showInvoiceDataNotification.value = true
          }
        } else {
          showInvoiceDataNotification.value = false
          localStorage.removeItem('invoice_data_notification_dismissed')
        }
      }, 500)
    }
  }
})

</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background-color: #e8f4f8;
  overflow-x: hidden;
  position: relative;
  margin-top: 0;
  padding-top: 0;
}

/* Sidebar */
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #e0d5ff 0%, #d4c5f7 100%);
  color: #2c3e50;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease, filter 0.3s;
  box-shadow: 2px 0 12px rgba(0, 0, 0, 0.15);
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  z-index: 2000;
  overflow: hidden;
  border: none;
  outline: none;
  border-right: none;
}

.sidebar.blocked {
  filter: blur(3px);
  pointer-events: none;
  user-select: none;
}

.sidebar.collapsed {
  width: 80px;
}

.sidebar-header {
  padding: 1rem 0.875rem;
  border-bottom: 2px solid rgba(102, 126, 234, 0.2);
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 65px;
  position: relative;
  gap: 0.5rem;
  width: 100%;
}

.user-profile-sidebar {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  flex: 1;
  min-width: 0;
  overflow: visible;
}

.user-avatar-sidebar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.9rem;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.user-info-sidebar {
  flex: 1;
  min-width: 0;
  overflow: visible;
}

.logo-text {
  font-size: 0.95rem;
  font-weight: 700;
  color: #2c3e50;
  letter-spacing: 0.5px;
  white-space: nowrap;
  margin-bottom: 0.15rem;
  line-height: 1.2;
  overflow: visible;
}

.logo-subtitle {
  font-size: 0.65rem;
  color: rgba(44, 62, 80, 0.7);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
  line-height: 1.2;
  overflow: visible;
}

.sidebar-toggle {
  background: transparent;
  border: none;
  color: #667eea;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  padding: 0;
  opacity: 0.7;
  min-width: 28px;
}

.sidebar-toggle:hover {
  background: rgba(102, 126, 234, 0.1);
  color: #764ba2;
  opacity: 1;
}

.sidebar-toggle:active {
  background: rgba(102, 126, 234, 0.15);
  transform: scale(0.95);
}

.toggle-icon {
  width: 20px;
  height: 20px;
  stroke: currentColor;
  transition: transform 0.2s ease;
}

.sidebar-toggle:hover .toggle-icon {
  transform: scale(1.1);
}

.sidebar.collapsed .sidebar-toggle {
  margin-left: 0;
  width: 100%;
  justify-content: center;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  color: #2c3e50;
  text-decoration: none;
  transition: all 0.2s;
  border-left: 4px solid transparent;
  font-size: 0.95rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.nav-item:hover {
  background-color: rgba(102, 126, 234, 0.1);
  color: #667eea;
}

.nav-item.router-link-active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-left-color: white;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.nav-icon {
  font-size: 1.3rem;
  width: 28px;
  text-align: center;
  flex-shrink: 0;
}

.nav-label {
  white-space: nowrap;
}

.sidebar.collapsed .nav-label {
  display: none;
}

.sidebar.collapsed .user-info-sidebar {
  display: none;
}

.sidebar.collapsed .user-avatar-sidebar {
  width: 40px;
  height: 40px;
  font-size: 0.9rem;
}

.sidebar-footer {
  padding: 0.75rem 0;
  border-top: 2px solid rgba(26, 35, 126, 0.1);
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.bookings-nav-item {
  margin: 0 1.25rem;
  padding: 0.875rem 1rem;
}

.sidebar.collapsed .bookings-nav-item {
  margin: 0 0.75rem;
  padding: 0.875rem;
  justify-content: center;
}

.logout-btn {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.875rem 1rem;
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
  border: 2px solid #e74c3c;
  color: white;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.logout-btn:hover {
  background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
  border-color: #c0392b;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
}

.sidebar.collapsed .logout-btn {
  justify-content: center;
  padding: 0.875rem;
}

.sidebar.collapsed .logout-btn span:not(.nav-icon) {
  display: none;
}

/* Main Content */
.main-content {
  flex: 1;
  margin-left: 280px;
  display: flex;
  flex-direction: column;
  transition: margin-left 0.3s ease;
  min-width: 0;
  background: white;
  width: calc(100% - 280px);
}

.sidebar.collapsed ~ .main-content {
  margin-left: 80px;
  width: calc(100% - 80px);
}

.topbar {
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  position: sticky;
  top: 0;
  z-index: 100;
  border-bottom: 2px solid #e8f4f8;
  margin-left: 0;
  width: 100%;
}

.topbar-content {
  max-width: 100%;
  padding: 1.25rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.85rem;
  color: #667eea;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.user-name {
  font-weight: 600;
  color: white;
  font-size: 0.95rem;
}

.invoice-data-notification {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  border-bottom: 2px solid #f59e0b;
  padding: 1rem 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.notification-content {
  display: flex;
  align-items: center;
  gap: 1rem;
  max-width: 1400px;
  margin: 0 auto;
}

.notification-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.notification-text {
  flex: 1;
  color: #92400e;
  font-size: 0.9rem;
  line-height: 1.5;
}

.notification-text strong {
  font-weight: 600;
}

.notification-link {
  color: #d97706;
  font-weight: 600;
  text-decoration: underline;
  margin-left: 0.5rem;
  transition: color 0.2s;
}

.notification-link:hover {
  color: #b45309;
}

.notification-close {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  color: #92400e;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: background 0.2s;
  flex-shrink: 0;
  line-height: 1;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notification-close:hover {
  background: rgba(217, 119, 6, 0.2);
}

.approval-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.2s ease;
}

.approval-status.approved {
  background: #d1fae5;
  color: #059669;
  border: 2px solid #10b981;
}

.approval-status.pending {
  background: #fef3c7;
  color: #d97706;
  border: 2px solid #f59e0b;
}

.status-icon {
  font-size: 1rem;
}

.status-text {
  white-space: nowrap;
}

.content-area {
  flex: 1;
  padding: 1.5rem 2rem;
  overflow-y: auto;
  max-width: 100%;
  background: white;
  transition: filter 0.3s;
}

.content-area.blocked {
  filter: blur(5px);
  pointer-events: none;
  user-select: none;
}

@media (max-width: 768px) {
  .sidebar {
    width: 80px;
  }

  .sidebar:not(.collapsed) {
    width: 280px;
    z-index: 2000;
  }

  .main-content {
    margin-left: 80px;
  }

  .topbar-content {
    padding: 1rem 1.25rem;
  }

  .page-title {
    font-size: 1.25rem;
  }

  .content-area {
    padding: 1rem 1.25rem;
  }

  .sidebar-toggle {
    right: -18px;
    width: 36px;
    height: 36px;
    font-size: 1rem;
  }
}
</style>
