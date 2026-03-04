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
              <span class="action-icon">🏨</span>
              <h3>Szállodák</h3>
              <p>Szállodák kezelése</p>
            </router-link>
            <router-link to="/admin/rooms" class="action-card">
              <span class="action-icon">🛏️</span>
              <h3>Szobák</h3>
              <p>Szobák kezelése</p>
            </router-link>
            <router-link to="/admin/services" class="action-card">
              <span class="action-icon">✨</span>
              <h3>Szolgáltatások</h3>
              <p>Szolgáltatások kezelése</p>
            </router-link>
            <router-link to="/admin/bookings" class="action-card">
              <span class="action-icon">📅</span>
              <h3>Foglalások</h3>
              <p>Foglalások kezelése</p>
            </router-link>
            <router-link to="/admin/tags" class="action-card">
              <span class="action-icon">🏷️</span>
              <h3>Címkék</h3>
              <p>Címkék kezelése</p>
            </router-link>
            <router-link to="/admin/rfid-keys" class="action-card">
              <span class="action-icon">🔑</span>
              <h3>RFID kulcsok</h3>
              <p>RFID kulcsok kezelése</p>
            </router-link>
            <router-link to="/admin/company-info" class="action-card">
              <span class="action-icon">💼</span>
              <h3>Cégadatok</h3>
              <p>Számlázási adatok</p>
            </router-link>
            <router-link to="/admin/users" class="action-card">
              <span class="action-icon">👤</span>
              <h3>Profilom</h3>
              <p>Felhasználói beállítások</p>
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
  // Store in localStorage that THIS user has seen the welcome
  const userId = authStore.state.user?.id
  if (userId) {
    localStorage.setItem(`admin_welcome_seen_${userId}`, 'true')
  } else {
    // Fallback (should rarely be used)
    localStorage.setItem('admin_welcome_seen', 'true')
  }
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

    // Load recent activities from bookings and other sources
    await loadRecentActivities(userHotel?.id)
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
  } finally {
    loading.value = false
  }
}

const loadRecentActivities = async (hotelId) => {
  if (!hotelId) {
    recentActivity.value = []
    return
  }

  try {
    const response = await adminService.getRecentActivities(hotelId, 15)
    recentActivity.value = response.activities || []
  } catch (err) {
    console.error('Failed to load recent activities:', err)
    recentActivity.value = []
  }
}

const formatTime = (date) => {
  if (!date) return 'Ismeretlen'
  const activityDate = new Date(date)
  // Format: YYYY-MM-DD HH:MM:SS
  const year = activityDate.getFullYear()
  const month = String(activityDate.getMonth() + 1).padStart(2, '0')
  const day = String(activityDate.getDate()).padStart(2, '0')
  const hours = String(activityDate.getHours()).padStart(2, '0')
  const minutes = String(activityDate.getMinutes()).padStart(2, '0')
  const seconds = String(activityDate.getSeconds()).padStart(2, '0')
  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
}

onMounted(() => {
  loadDashboardData()
  
  // Show welcome prompt only the first time this user signs in (per browser)
  const userId = authStore.state.user?.id
  const key = userId ? `admin_welcome_seen_${userId}` : 'admin_welcome_seen'
  const welcomeSeen = localStorage.getItem(key)
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
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.25rem;
}

.action-card {
  text-decoration: none;
  background: white;
  border: 2px solid #e0e0e0;
  color: #2c3e50;
  padding: 1.5rem;
  border-radius: 12px;
  text-align: center;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 140px;
  position: relative;
  overflow: hidden;
}

.action-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.action-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.2);
  border-color: #667eea;
}

.action-card:hover::before {
  transform: scaleX(1);
}

.action-icon {
  font-size: 2.5rem;
  display: block;
  margin-bottom: 0.75rem;
  transition: transform 0.3s ease;
}

.action-card:hover .action-icon {
  transform: scale(1.1);
}

.action-card h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
}

.action-card p {
  margin: 0;
  font-size: 0.85rem;
  color: #7f8c8d;
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
