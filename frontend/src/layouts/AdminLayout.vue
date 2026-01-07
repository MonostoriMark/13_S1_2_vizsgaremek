<template>
  <div class="admin-layout">
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="sidebar-header">
        <router-link to="/admin" class="logo">
          <span class="logo-icon">🏨</span>
          <span v-if="!sidebarCollapsed" class="logo-text">HotelFlow Admin</span>
        </router-link>
        <button class="sidebar-toggle" @click="toggleSidebar">
          {{ sidebarCollapsed ? '→' : '←' }}
        </button>
      </div>
      <nav class="sidebar-nav">
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          class="nav-item"
          :title="item.label"
        >
          <span class="nav-icon">{{ item.icon }}</span>
          <span v-if="!sidebarCollapsed" class="nav-label">{{ item.label }}</span>
        </router-link>
      </nav>
      <div class="sidebar-footer">
        <button @click="handleLogout" class="logout-btn" :title="sidebarCollapsed ? 'Logout' : ''">
          <span class="nav-icon">🚪</span>
          <span v-if="!sidebarCollapsed">Logout</span>
        </button>
      </div>
    </aside>

    <div class="main-content">
      <header class="topbar">
        <div class="topbar-content">
          <h1 class="page-title">{{ pageTitle }}</h1>
          <div class="topbar-actions">
            <div class="user-profile">
              <span class="user-avatar">{{ getUserInitials }}</span>
              <span class="user-name">{{ userName }}</span>
            </div>
          </div>
        </div>
      </header>

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
  { path: '/admin', label: 'Dashboard', icon: '📊' },
  { path: '/admin/hotels', label: 'Hotels', icon: '🏨' },
  { path: '/admin/rooms', label: 'Rooms', icon: '🛏️' },
  { path: '/admin/services', label: 'Services', icon: '✨' }
]

const pageTitle = computed(() => {
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

onMounted(() => {
  // Check if user is authenticated and has hotel role
  if (!authStore.state.isAuthenticated || authStore.state.user?.role !== 'hotel') {
    router.push('/login')
  }
})
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f5f6fa;
}

/* Sidebar */
.sidebar {
  width: 260px;
  background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
  color: white;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
  position: fixed;
  height: 100vh;
  z-index: 1000;
}

.sidebar.collapsed {
  width: 80px;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  color: white;
  font-size: 1.25rem;
  font-weight: 700;
}

.logo-icon {
  font-size: 1.5rem;
}

.logo-text {
  white-space: nowrap;
}

.sidebar-toggle {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.2s;
}

.sidebar-toggle:hover {
  background: rgba(255, 255, 255, 0.2);
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
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.2s;
  border-left: 3px solid transparent;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-item.router-link-active {
  background-color: rgba(255, 255, 255, 0.15);
  border-left-color: #3498db;
  color: white;
}

.nav-icon {
  font-size: 1.25rem;
  width: 24px;
  text-align: center;
  flex-shrink: 0;
}

.nav-label {
  white-space: nowrap;
}

.sidebar.collapsed .nav-label {
  display: none;
}

.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.logout-btn {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1rem;
  background: rgba(231, 76, 60, 0.2);
  border: 1px solid rgba(231, 76, 60, 0.3);
  color: white;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.95rem;
}

.logout-btn:hover {
  background: rgba(231, 76, 60, 0.3);
  border-color: rgba(231, 76, 60, 0.5);
}

.sidebar.collapsed .logout-btn {
  justify-content: center;
  padding: 0.75rem;
}

.sidebar.collapsed .logout-btn span:not(.nav-icon) {
  display: none;
}

/* Main Content */
.main-content {
  flex: 1;
  margin-left: 260px;
  display: flex;
  flex-direction: column;
  transition: margin-left 0.3s ease;
}

.sidebar.collapsed ~ .main-content {
  margin-left: 80px;
}

.topbar {
  background: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 0;
  z-index: 100;
}

.topbar-content {
  max-width: 100%;
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
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
  background-color: #f8f9fa;
  border-radius: 8px;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.9rem;
  color: white;
}

.user-name {
  font-weight: 500;
  color: #2c3e50;
}

.content-area {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
}

@media (max-width: 768px) {
  .sidebar {
    width: 80px;
  }

  .sidebar:not(.collapsed) {
    width: 260px;
    z-index: 2000;
  }

  .main-content {
    margin-left: 80px;
  }

  .topbar-content {
    padding: 1rem;
  }

  .page-title {
    font-size: 1.25rem;
  }

  .content-area {
    padding: 1rem;
  }
}
</style>
