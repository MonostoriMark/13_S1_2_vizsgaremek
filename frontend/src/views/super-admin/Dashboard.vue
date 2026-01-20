<template>
  <SuperAdminLayout>
    <div class="dashboard-page">
      <!-- Stats Grid -->
      <div class="stats-grid">
        <div class="stat-card" v-for="stat in stats" :key="stat.key">
          <div class="stat-icon">{{ stat.icon }}</div>
          <div class="stat-content">
            <div class="stat-value">{{ stat.value }}</div>
            <div class="stat-label">{{ stat.label }}</div>
          </div>
          <div class="stat-glow"></div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="section">
        <h2 class="section-title">Legutóbbi foglalások</h2>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Vendég</th>
                <th>Szálloda</th>
                <th>Dátumok</th>
                <th>Státusz</th>
                <th>Összesen</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="booking in recentBookings" :key="booking.id">
                <td>#{{ booking.id }}</td>
                <td>{{ booking.user?.name || 'Nincs adat' }}</td>
                <td>{{ booking.hotel?.name || 'Nincs adat' }}</td>
                <td>{{ formatDate(booking.startDate) }} - {{ formatDate(booking.endDate) }}</td>
                <td>
                  <span class="status-badge" :class="`status-${booking.status}`">
                    {{ booking.status }}
                  </span>
                </td>
                <td>€{{ parseFloat(booking.totalPrice || 0).toFixed(2) }}</td>
              </tr>
              <tr v-if="recentBookings.length === 0">
                <td colspan="6" class="empty-state">Nincs legutóbbi foglalás</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import SuperAdminLayout from '../../layouts/SuperAdminLayout.vue'
import { superAdminService } from '../../services/superAdminService'

const stats = ref([])
const recentBookings = ref([])
const loading = ref(true)

const formatDate = (dateString) => {
  if (!dateString) return 'Nincs adat'
  return new Date(dateString).toLocaleDateString('hu-HU')
}

const loadDashboard = async () => {
  loading.value = true
  try {
    const data = await superAdminService.getDashboardStats()
    
    stats.value = [
      { key: 'users', label: 'Összes felhasználó', value: data.total_users, icon: '👥' },
      { key: 'hotels', label: 'Szállodák', value: data.total_hotels, icon: '🏨' },
      { key: 'rooms', label: 'Szobák', value: data.total_rooms, icon: '🛏️' },
      { key: 'services', label: 'Szolgáltatások', value: data.total_services, icon: '✨' },
      { key: 'bookings', label: 'Foglalások', value: data.total_bookings, icon: '📅' },
      { key: 'invoices', label: 'Számlák', value: data.total_invoices, icon: '🧾' },
      { key: 'rfid_keys', label: 'RFID kulcsok', value: data.total_rfid_keys || 0, icon: '🔑' },
      { key: 'pending', label: 'Függőben', value: data.pending_bookings, icon: '⏳' },
      { key: 'confirmed', label: 'Megerősítve', value: data.confirmed_bookings, icon: '✓' }
    ]

    recentBookings.value = data.recent_bookings || []
  } catch (err) {
    console.error('Failed to load dashboard:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboard()
})
</script>

<style scoped>
.dashboard-page {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: rgba(20, 20, 20, 0.8);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  position: relative;
  overflow: hidden;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.stat-card:hover {
  border-color: rgba(102, 126, 234, 0.6);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.2);
}

.stat-icon {
  font-size: 2.5rem;
  filter: drop-shadow(0 0 10px rgba(102, 126, 234, 0.5));
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.85rem;
  color: #9ca3af;
  margin-top: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-glow {
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
  pointer-events: none;
}

.section {
  background: rgba(20, 20, 20, 0.8);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 1.5rem;
}

.table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead {
  background: rgba(102, 126, 234, 0.1);
  border-bottom: 2px solid rgba(102, 126, 234, 0.3);
}

.data-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #d1d5db;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid rgba(102, 126, 234, 0.1);
  color: #e5e7eb;
}

.data-table tbody tr:hover {
  background: rgba(102, 126, 234, 0.05);
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending {
  background: rgba(251, 191, 36, 0.2);
  color: #fbbf24;
  border: 1px solid rgba(251, 191, 36, 0.3);
}

.status-confirmed {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.status-cancelled {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.empty-state {
  text-align: center;
  color: #6b7280;
  padding: 2rem !important;
}
</style>
