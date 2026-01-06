<template>
  <div id="app">
    <nav class="navbar">
      <div class="nav-container">
        <router-link to="/" class="nav-logo">
          <span class="logo-icon">🏨</span>
          <span class="logo-text">HotelFlow</span>
        </router-link>
        
        <div class="nav-menu" :class="{ active: mobileMenuOpen }">
          <template v-if="isAuthenticated">
            <router-link 
              v-if="userRole === 'user'" 
              to="/search" 
              class="nav-link"
              @click="closeMobileMenu"
            >
              Search Hotels
            </router-link>
            <router-link 
              v-if="userRole === 'user'" 
              to="/bookings" 
              class="nav-link"
              @click="closeMobileMenu"
            >
              My Bookings
            </router-link>
            <router-link 
              v-if="userRole === 'hotel'" 
              to="/admin" 
              class="nav-link"
              @click="closeMobileMenu"
            >
              Admin Panel
            </router-link>
            <router-link 
              v-if="userRole === 'hotel'" 
              to="/admin/bookings" 
              class="nav-link"
              @click="closeMobileMenu"
            >
              Bookings
            </router-link>
            
            <div class="user-menu">
              <div class="user-info">
                <span class="user-avatar">{{ getUserInitials }}</span>
                <span class="user-name">{{ userName }}</span>
              </div>
              <button @click="handleLogout" class="btn-logout">
                <span class="logout-icon">🚪</span>
                <span>Sign Out</span>
              </button>
            </div>
          </template>
          
          <template v-else>
            <router-link 
              to="/search" 
              class="nav-link"
              @click="closeMobileMenu"
            >
              Search Hotels
            </router-link>
            <div class="auth-buttons">
              <router-link 
                to="/login" 
                class="btn-login"
                @click="closeMobileMenu"
              >
                Login
              </router-link>
              <router-link 
                to="/register" 
                class="btn-signup"
                @click="closeMobileMenu"
              >
                Sign Up
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
    
    <main class="main-content">
      <router-view />
    </main>
    <Toast />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
import Toast from './components/Toast.vue'

const router = useRouter()
const authStore = useAuthStore()
const mobileMenuOpen = ref(false)

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
</style>


