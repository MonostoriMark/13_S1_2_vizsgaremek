<template>
  <AdminLayout>
    <AdminWelcomePrompt
      :visible="showWelcomePrompt"
      message="Üdvözöljük a szálloda kezelő irányítópultján! Itt kezelheti szállodáit, szobáit, szolgáltatásait és megtekintheti az összes foglalást."
      dismiss-text="Kezdjük el!"
      @dismiss="handleDismissWelcome"
    />
    
    <div class="dashboard">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">🏨</div>
          <div class="stat-content">
            <h3>{{ stats.hotels }}</h3>
            <p>Szállodák</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">🛏️</div>
          <div class="stat-content">
            <h3>{{ stats.rooms }}</h3>
            <p>Szobák</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">✨</div>
          <div class="stat-content">
            <h3>{{ stats.services }}</h3>
            <p>Szolgáltatások</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">📅</div>
          <div class="stat-content">
            <h3>{{ stats.bookings }}</h3>
            <p>Foglalások</p>
          </div>
        </div>
      </div>

      <div class="dashboard-content">
        <div class="dashboard-section">
          <h2>Legutóbbi tevékenységek</h2>
          <div class="activity-list">
            <div v-if="loading" class="loading">Betöltés...</div>
            <div v-else-if="recentActivity.length === 0" class="empty-state">
              <p>Nincs legutóbbi tevékenység</p>
            </div>
            <div v-else class="activity-item" v-for="activity in recentActivity" :key="activity.id">
              <span class="activity-icon">{{ activity.icon }}</span>
              <div class="activity-content">
                <p class="activity-text">{{ activity.text }}</p>
                <span class="activity-time">{{ formatTime(activity.time) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="dashboard-section">
          <h2>Gyors műveletek</h2>
          <div class="quick-actions">
            <router-link to="/admin/hotels" class="action-card">
              <span class="action-icon">➕</span>
              <h3>Szálloda hozzáadása</h3>
              <p>Új szálloda létrehozása</p>
            </router-link>
            <router-link to="/admin/rooms" class="action-card">
              <span class="action-icon">🛏️</span>
              <h3>Szoba hozzáadása</h3>
              <p>Új szoba létrehozása</p>
            </router-link>
            <router-link to="/admin/services" class="action-card">
              <span class="action-icon">✨</span>
              <h3>Szolgáltatás hozzáadása</h3>
              <p>Új szolgáltatás létrehozása</p>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import AdminWelcomePrompt from '../../components/AdminWelcomePrompt.vue'
import { adminService } from '../../services/adminService'
import { bookingService } from '../../services/bookingService'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const loading = ref(true)
const showWelcomePrompt = ref(false)
const stats = ref({
  hotels: 0,
  rooms: 0,
  services: 0,
  bookings: 0
})
const recentActivity = ref([])

const handleDismissWelcome = () => {
  showWelcomePrompt.value = false
  // Store in localStorage that user has seen the welcome
  localStorage.setItem('admin_welcome_seen', 'true')
}

const loadDashboardData = async () => {
  loading.value = true
  try {
    // Get user's hotel
    const hotels = await adminService.getHotels()
    const userHotel = hotels.find(h => h.user_id === authStore.state.user?.id)

    if (userHotel) {
      // Get rooms
      const rooms = await adminService.getRoomsByHotelId(userHotel.id)
      stats.value.rooms = rooms.length

      // Get services
      const services = await adminService.getServicesByHotelId(userHotel.id)
      stats.value.services = services.length || 0

      // Get bookings
      try {
        const bookingsData = await bookingService.getBookingsByHotelId(userHotel.id)
        stats.value.bookings = bookingsData?.bookings?.length || 0
      } catch (err) {
        console.error('Failed to load bookings:', err)
      }
    }

    stats.value.hotels = userHotel ? 1 : 0

    // Generate recent activity (mock for now)
    recentActivity.value = [
      {
        id: 1,
        icon: '🏨',
        text: 'Szálloda frissítve',
        time: new Date(Date.now() - 3600000)
      },
      {
        id: 2,
        icon: '🛏️',
        text: 'Új szoba hozzáadva',
        time: new Date(Date.now() - 7200000)
      }
    ]
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
  } finally {
    loading.value = false
  }
}

const formatTime = (date) => {
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(minutes / 60)
  const days = Math.floor(hours / 24)

  if (minutes < 1) return 'Épp most'
  if (minutes < 60) return `${minutes} perce`
  if (hours < 24) return `${hours} órája`
  return `${days} napja`
}

onMounted(() => {
  loadDashboardData()
  
  // Show welcome prompt if user hasn't seen it before
  const welcomeSeen = localStorage.getItem('admin_welcome_seen')
  if (!welcomeSeen) {
    // Show after a short delay for better UX
    setTimeout(() => {
      showWelcomePrompt.value = true
    }, 500)
  }
})
</script>

<style scoped>
.dashboard {
  max-width: 1400px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1.5rem;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  font-size: 3rem;
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
}

.stat-content h3 {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0 0 0.25rem 0;
}

.stat-content p {
  color: #7f8c8d;
  margin: 0;
  font-size: 0.9rem;
}

.dashboard-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.dashboard-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.dashboard-section h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 1.5rem 0;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.activity-icon {
  font-size: 1.5rem;
}

.activity-content {
  flex: 1;
}

.activity-text {
  margin: 0 0 0.25rem 0;
  color: #2c3e50;
  font-weight: 500;
}

.activity-time {
  font-size: 0.85rem;
  color: #7f8c8d;
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.action-card {
  text-decoration: none;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem;
  border-radius: 12px;
  text-align: center;
  transition: transform 0.2s, box-shadow 0.2s;
}

.action-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
}

.action-icon {
  font-size: 2.5rem;
  display: block;
  margin-bottom: 0.75rem;
}

.action-card h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
}

.action-card p {
  margin: 0;
  font-size: 0.85rem;
  opacity: 0.9;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #7f8c8d;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #7f8c8d;
}

@media (max-width: 1024px) {
  .dashboard-content {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
