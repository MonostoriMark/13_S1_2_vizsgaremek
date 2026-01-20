<template>
  <div id="app">
    <nav v-if="!isAdminRoute && !isAuthRoute" class="navbar">
      <div class="nav-container">
        <router-link to="/" class="nav-logo">
          <span class="logo-icon">🏨</span>
          <span class="logo-text">HotelFlow</span>
        </router-link>
        
        <div class="nav-menu" :class="{ active: mobileMenuOpen }">
          <template v-if="isAuthenticated">
            <router-link 
              to="/search" 
              class="nav-link"
              @click="closeMobileMenu"
            >
              Szállodák keresése
            </router-link>
            <router-link 
              v-if="userRole === 'user'" 
              to="/bookings" 
              class="nav-link"
              @click="closeMobileMenu"
            >
              Foglalásaim
            </router-link>
            <router-link 
              v-if="userRole === 'hotel'" 
              to="/admin" 
              class="nav-link"
              @click="closeMobileMenu"
            >
              Admin felület
            </router-link>
            <router-link 
              v-if="userRole === 'hotel'" 
              to="/admin/bookings" 
              class="nav-link"
              @click="closeMobileMenu"
            >
              Foglalások
            </router-link>
            
            <div class="user-menu">
              <router-link to="/profile" class="user-info" @click="closeMobileMenu">
                <span class="user-avatar">{{ getUserInitials }}</span>
                <span class="user-name">{{ userName }}</span>
              </router-link>
              <button @click="handleLogout" class="btn-logout">
                <span class="logout-icon">🚪</span>
                <span>Kijelentkezés</span>
              </button>
            </div>
          </template>
          
          <template v-else>
            <router-link 
              to="/search" 
              class="nav-link"
              @click="closeMobileMenu"
            >
              Szállodák keresése
            </router-link>
            <div class="auth-buttons">
              <router-link 
                to="/login" 
                class="btn-login"
                @click="closeMobileMenu"
              >
                Bejelentkezés
              </router-link>
              <router-link 
                to="/register" 
                class="btn-signup"
                @click="closeMobileMenu"
              >
                Regisztráció
              </router-link>
            </div>
          </template>
        </div>
        
        <button 
          class="mobile-menu-toggle" 
          @click="toggleMobileMenu"
          aria-label="Toggle menu"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </nav>
    
    <!-- Admin Loading Screen -->
    <Transition name="fade">
      <div v-if="adminLoading && isAdminRoute" class="admin-loading-screen">
        <div class="loading-content">
          <div class="loading-spinner-large"></div>
          <h2>Admin felület betöltése</h2>
          <p>Kérjük, várjon...</p>
        </div>
      </div>
    </Transition>

    <main class="main-content" :class="{ 'full-screen': isAuthRoute || route.path.startsWith('/super-admin') }">
      <router-view />
    </main>
    <Toast />
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from './stores/auth'
import Toast from './components/Toast.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const mobileMenuOpen = ref(false)
const adminLoading = ref(false)

const isAdminRoute = computed(() => {
  return route.path.startsWith('/admin') || route.path.startsWith('/super-admin')
})

const isAuthRoute = computed(() => {
  return route.path === '/login' || route.path === '/register' || route.path === '/profile' || route.path.startsWith('/two-factor-auth')
})

// Watch for admin route navigation and show loading
watch(() => route.path, (newPath, oldPath) => {
  if (newPath.startsWith('/admin') && !oldPath.startsWith('/admin')) {
    adminLoading.value = true
    // Hide loading after a short delay to allow page to render
    setTimeout(() => {
      adminLoading.value = false
    }, 500)
  } else {
    adminLoading.value = false
  }
}, { immediate: true })

const isAuthenticated = computed(() => authStore.state.isAuthenticated)
const userRole = computed(() => authStore.state.user?.role)
const userName = computed(() => authStore.state.user?.name || '')
const getUserInitials = computed(() => {
  if (!userName.value) return 'U'
  const names = userName.value.split(' ')
  if (names.length >= 2) {
    return (names[0][0] + names[names.length - 1][0]).toUpperCase()
  }
  return userName.value[0].toUpperCase()
})

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

const handleLogout = async () => {
  await authStore.logout()
  closeMobileMenu()
  router.push('/login')
}
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
  color: #333;
  background-color: #f8f9fa;
  line-height: 1.6;
}

#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Navigation Bar */
.navbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 0;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
  backdrop-filter: blur(10px);
}

.nav-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  color: white;
  font-size: 1.5rem;
  font-weight: 700;
  transition: transform 0.2s ease;
}

.nav-logo:hover {
  transform: scale(1.05);
}

.logo-icon {
  font-size: 1.75rem;
}

.logo-text {
  background: linear-gradient(45deg, #fff, #e0e7ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.nav-menu {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  list-style: none;
}

.nav-link {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
  position: relative;
}

.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
}

.nav-link.router-link-active {
  background-color: rgba(255, 255, 255, 0.2);
  font-weight: 600;
}

.nav-link.router-link-active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 60%;
  height: 2px;
  background-color: white;
  border-radius: 2px;
}

/* User Menu */
.user-menu {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-left: 1rem;
  padding-left: 1rem;
  border-left: 1px solid rgba(255, 255, 255, 0.2);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 0.5rem;
  border-radius: 8px;
}

.user-info:hover {
  background-color: rgba(255, 255, 255, 0.1);
  transform: translateY(-2px);
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.9rem;
  color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.user-name {
  font-weight: 500;
  font-size: 0.95rem;
}

.btn-logout {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background-color: rgba(255, 255, 255, 0.15);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-logout:hover {
  background-color: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.logout-icon {
  font-size: 1rem;
}

/* Auth Buttons */
.auth-buttons {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-left: 1rem;
  padding-left: 1rem;
  border-left: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-login {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1.25rem;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.btn-login:hover {
  background-color: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.5);
}

.btn-signup {
  background-color: white;
  color: #667eea;
  text-decoration: none;
  padding: 0.5rem 1.25rem;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-signup:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  background-color: #f8f9ff;
}

/* Mobile Menu Toggle */
.mobile-menu-toggle {
  display: none;
  flex-direction: column;
  gap: 5px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
}

.mobile-menu-toggle span {
  width: 25px;
  height: 3px;
  background-color: white;
  border-radius: 3px;
  transition: all 0.3s ease;
}

/* Main Content */
.main-content {
  flex: 1;
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
  padding: 2rem;
}

.main-content.full-screen {
  max-width: 100%;
  padding: 0;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .nav-container {
    padding: 1rem;
  }

  .mobile-menu-toggle {
    display: flex;
  }

  .nav-menu {
    position: fixed;
    top: 70px;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    flex-direction: column;
    align-items: stretch;
    padding: 1.5rem;
    gap: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    max-height: calc(100vh - 70px);
    overflow-y: auto;
  }

  .nav-menu.active {
    transform: translateX(0);
  }

  .nav-link {
    padding: 1rem;
    text-align: center;
    border-radius: 8px;
  }

  .user-menu {
    flex-direction: column;
    align-items: stretch;
    margin-left: 0;
    padding-left: 0;
    border-left: none;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    padding-top: 1rem;
    margin-top: 0.5rem;
  }

  .user-info {
    justify-content: center;
    margin-bottom: 0.5rem;
  }

  .btn-logout {
    width: 100%;
    justify-content: center;
  }

  .auth-buttons {
    flex-direction: column;
    margin-left: 0;
    padding-left: 0;
    border-left: none;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    padding-top: 1rem;
    margin-top: 0.5rem;
  }

  .btn-login,
  .btn-signup {
    width: 100%;
    text-align: center;
  }

  .main-content {
    padding: 1rem;
  }
}

@media (max-width: 480px) {
  .nav-logo {
    font-size: 1.25rem;
  }

  .logo-icon {
    font-size: 1.5rem;
  }
}

/* Admin Loading Screen */
.admin-loading-screen {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(10px);
}

.loading-content {
  text-align: center;
  color: white;
}

.loading-spinner-large {
  width: 64px;
  height: 64px;
  border: 5px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 2rem;
}

.loading-content h2 {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: white;
}

.loading-content p {
  font-size: 1rem;
  opacity: 0.9;
  color: white;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>


