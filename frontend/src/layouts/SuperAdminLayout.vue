<template>
  <div class="super-admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="sidebar-header">
        <div class="logo-section">
          <div class="logo-icon">⚡</div>
          <div v-if="!sidebarCollapsed" class="logo-text">
            <div class="logo-title">SUPER ADMIN</div>
            <div class="logo-subtitle">Vezérlőközpont</div>
          </div>
        </div>
        <button class="sidebar-toggle" @click="toggleSidebar" :title="sidebarCollapsed ? 'Kibontás' : 'Összecsukás'">
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
          :class="{ active: isActiveRoute(item.path) }"
          :title="item.label"
        >
          <span class="nav-icon">{{ item.icon }}</span>
          <span v-if="!sidebarCollapsed" class="nav-label">{{ item.label }}</span>
          <div class="nav-glow"></div>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <!-- User info and logout moved to topbar -->
      </div>
    </aside>

    <!-- Fixed Topbar -->
    <header class="topbar">
      <div class="topbar-content">
        <h1 class="page-title">{{ pageTitle }}</h1>
        <div class="topbar-actions">
          <div class="status-indicator">
            <span class="status-dot"></span>
            <span class="status-text">Rendszer online</span>
          </div>
          <div class="user-info-top">
            <div class="user-avatar-top">{{ getUserInitials }}</div>
            <div class="user-details-top">
              <div class="user-name-top">{{ userName }}</div>
              <div class="user-role-top">Super Admin</div>
            </div>
          </div>
          <button @click="handleLogout" class="logout-btn-top" title="Kijelentkezés">
            <span class="logout-icon">🚪</span>
            <span class="logout-text">Kijelentkezés</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="main-content" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
      <div class="content-area">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const sidebarCollapsed = ref(false)

const menuItems = [
  { path: '/super-admin/dashboard', label: 'Irányítópult', icon: '📊' },
  { path: '/super-admin/users', label: 'Felhasználók', icon: '👥' },
  { path: '/super-admin/hotels', label: 'Szállodák', icon: '🏨' },
  { path: '/super-admin/rooms', label: 'Szobák', icon: '🛏️' },
  { path: '/super-admin/services', label: 'Szolgáltatások', icon: '✨' },
  { path: '/super-admin/bookings', label: 'Foglalások', icon: '📅' },
  { path: '/super-admin/invoices', label: 'Számlák', icon: '🧾' },
  { path: '/super-admin/rfid-keys', label: 'RFID kulcsok', icon: '🔑' }
]

const pageTitle = computed(() => {
  const item = menuItems.find(i => i.path === route.path)
  return item ? item.label : 'Super Admin felület'
})

const userName = computed(() => authStore.state.user?.name || 'Super Admin')
const getUserInitials = computed(() => {
  if (!userName.value) return 'SA'
  const names = userName.value.split(' ')
  if (names.length >= 2) {
    return (names[0][0] + names[names.length - 1][0]).toUpperCase()
  }
  return userName.value.substring(0, 2).toUpperCase()
})

const isActiveRoute = (path) => {
  return route.path === path
}

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  if (!authStore.state.isAuthenticated || authStore.state.user?.role !== 'super_admin') {
    router.push('/login')
  }
})
</script>

<style scoped>
.super-admin-layout {
  display: flex;
  min-height: 100vh;
  background: #0a0a0a;
  color: #e5e7eb;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Sidebar */
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #111111 0%, #0a0a0a 100%);
  border-right: 1px solid rgba(102, 126, 234, 0.2);
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  position: fixed;
  top: 80px;
  left: 0;
  bottom: 0;
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.5);
  height: calc(100vh - 80px);
  z-index: 90;
}

.sidebar::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 0% 0%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 100% 100%, rgba(118, 75, 162, 0.1) 0%, transparent 50%);
  pointer-events: none;
  z-index: 0;
}

.sidebar.collapsed {
  width: 80px;
}

.main-content.sidebar-collapsed {
  margin-left: 80px;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(102, 126, 234, 0.2);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  z-index: 1;
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-icon {
  font-size: 2rem;
  filter: drop-shadow(0 0 10px rgba(102, 126, 234, 0.5));
  animation: pulse-glow 2s ease-in-out infinite;
}

@keyframes pulse-glow {
  0%, 100% {
    filter: drop-shadow(0 0 10px rgba(102, 126, 234, 0.5));
  }
  50% {
    filter: drop-shadow(0 0 20px rgba(102, 126, 234, 0.8));
  }
}

.logo-title {
  font-size: 1.1rem;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: 2px;
}

.logo-subtitle {
  font-size: 0.75rem;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.sidebar-toggle {
  background: rgba(102, 126, 234, 0.1);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 6px;
  padding: 0.5rem;
  color: #667eea;
  cursor: pointer;
  transition: all 0.2s;
}

.sidebar-toggle:hover {
  background: rgba(102, 126, 234, 0.2);
  border-color: rgba(102, 126, 234, 0.5);
}

.toggle-icon {
  width: 20px;
  height: 20px;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
  position: relative;
  z-index: 1;
}

/* Dark scrollbar for sidebar navigation */
.sidebar-nav::-webkit-scrollbar {
  width: 8px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background: rgba(102, 126, 234, 0.4);
  border-radius: 4px;
  border: 1px solid rgba(102, 126, 234, 0.2);
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
  background: rgba(102, 126, 234, 0.6);
}

/* Firefox scrollbar */
.sidebar-nav {
  scrollbar-width: thin;
  scrollbar-color: rgba(102, 126, 234, 0.4) rgba(0, 0, 0, 0.3);
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  color: #9ca3af;
  text-decoration: none;
  transition: all 0.3s;
  position: relative;
  border-left: 3px solid transparent;
}

.nav-item:hover {
  background: rgba(102, 126, 234, 0.1);
  color: #d1d5db;
  border-left-color: rgba(102, 126, 234, 0.5);
}

.nav-item.active {
  background: rgba(102, 126, 234, 0.15);
  color: #667eea;
  border-left-color: #667eea;
}

.nav-item.active .nav-glow {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 0 10px rgba(102, 126, 234, 0.6);
}

.nav-icon {
  font-size: 1.5rem;
  min-width: 24px;
}

.nav-label {
  font-weight: 500;
  font-size: 0.95rem;
}

.sidebar-footer {
  padding: 1.5rem;
  border-top: 1px solid rgba(102, 126, 234, 0.2);
  position: relative;
  z-index: 1;
  /* User info and logout moved to topbar */
}

/* Main Content */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: #0f0f0f;
  overflow: hidden;
  margin-top: 80px;
  margin-left: 280px;
  transition: margin-left 0.3s ease;
  min-height: calc(100vh - 80px);
}

.topbar {
  background: rgba(20, 20, 20, 0.95);
  border-bottom: 1px solid rgba(102, 126, 234, 0.2);
  padding: 1.5rem 2rem;
  backdrop-filter: blur(10px);
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  width: 100%;
}

.topbar-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1600px;
  margin: 0 auto;
  width: 100%;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
  text-shadow: 0 0 30px rgba(102, 126, 234, 0.3);
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.user-info-top {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  background: rgba(102, 126, 234, 0.1);
  border-radius: 8px;
  border: 1px solid rgba(102, 126, 234, 0.2);
}

.user-avatar-top {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.85rem;
  color: white;
  box-shadow: 0 0 15px rgba(102, 126, 234, 0.4);
  flex-shrink: 0;
}

.user-details-top {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.user-name-top {
  font-weight: 600;
  color: #e5e7eb;
  font-size: 0.9rem;
  line-height: 1.2;
}

.user-role-top {
  font-size: 0.7rem;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  line-height: 1.2;
}

.logout-btn-top {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  background: rgba(220, 38, 38, 0.1);
  border: 1px solid rgba(220, 38, 38, 0.3);
  border-radius: 8px;
  color: #fca5a5;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.875rem;
  font-weight: 500;
}

.logout-btn-top:hover {
  background: rgba(220, 38, 38, 0.2);
  border-color: rgba(220, 38, 38, 0.5);
  color: #f87171;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
}

.logout-icon {
  font-size: 1.1rem;
}

.logout-text {
  font-weight: 500;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.3);
  border-radius: 8px;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #22c55e;
  box-shadow: 0 0 10px rgba(34, 197, 94, 0.6);
  animation: pulse-dot 2s ease-in-out infinite;
}

@keyframes pulse-dot {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.7;
    transform: scale(1.1);
  }
}

.status-text {
  font-size: 0.85rem;
  color: #86efac;
  font-weight: 500;
}

.content-area {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
  max-width: 1600px;
  width: 100%;
  margin: 0 auto;
}

/* Dark scrollbar for content area */
.content-area::-webkit-scrollbar {
  width: 10px;
}

.content-area::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 5px;
}

.content-area::-webkit-scrollbar-thumb {
  background: rgba(102, 126, 234, 0.4);
  border-radius: 5px;
  border: 1px solid rgba(102, 126, 234, 0.2);
}

.content-area::-webkit-scrollbar-thumb:hover {
  background: rgba(102, 126, 234, 0.6);
}

/* Firefox scrollbar */
.content-area {
  scrollbar-width: thin;
  scrollbar-color: rgba(102, 126, 234, 0.4) rgba(0, 0, 0, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    left: 0;
    top: 80px;
    bottom: 0;
    z-index: 1000;
    transform: translateX(-100%);
    margin-top: 0;
    height: calc(100vh - 80px);
  }

  .sidebar:not(.collapsed) {
    transform: translateX(0);
  }

  .main-content {
    margin-left: 0 !important;
  }

  .content-area {
    padding: 1rem;
  }

  .topbar {
    padding: 1rem;
  }

  .topbar-content {
    flex-wrap: wrap;
    gap: 1rem;
  }

  .page-title {
    font-size: 1.25rem;
  }

  .topbar-actions {
    flex-wrap: wrap;
    gap: 0.75rem;
  }

  .user-details-top {
    display: none;
  }

  .logout-text {
    display: none;
  }
}

@media (max-width: 480px) {
  .status-indicator {
    display: none;
  }

  .user-info-top {
    padding: 0.5rem;
  }
}
</style>
