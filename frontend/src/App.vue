<template>
  <div id="app">
    <nav v-if="isAuthenticated">
      <div class="nav-content">
        <router-link to="/" class="logo">HotelFlow</router-link>
        <div class="nav-links">
          <template v-if="userRole === 'user'">
            <router-link to="/search">Search Hotels</router-link>
            <router-link to="/bookings">My Bookings</router-link>
          </template>
          <template v-else-if="userRole === 'hotel'">
            <router-link to="/admin/bookings">Bookings</router-link>
          </template>
          <span class="user-info">{{ userName }}</span>
          <button @click="handleLogout" class="logout-btn">Logout</button>
        </div>
      </div>
    </nav>
    <main>
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const isAuthenticated = computed(() => authStore.isAuthenticated)
const userRole = computed(() => authStore.user?.role)
const userName = computed(() => authStore.user?.name || '')

const handleLogout = async () => {
  await authStore.logout()
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
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  color: #333;
  background-color: #f5f5f5;
}

#app {
  min-height: 100vh;
}

nav {
  background-color: #2c3e50;
  color: white;
  padding: 1rem 2rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nav-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  text-decoration: none;
}

.nav-links {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.nav-links a {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.nav-links a:hover,
.nav-links a.router-link-active {
  background-color: rgba(255,255,255,0.1);
}

.user-info {
  color: #ecf0f1;
  font-size: 0.9rem;
}

.logout-btn {
  background-color: #e74c3c;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
}

.logout-btn:hover {
  background-color: #c0392b;
}

main {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}
</style>


