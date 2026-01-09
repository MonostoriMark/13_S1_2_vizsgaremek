<template>
  <div class="admin-bookings-page">
    <div class="page-header">
      <div class="page-header-top">
        <router-link to="/admin" class="back-to-dashboard-btn">
          <span class="back-icon">←</span>
          <span>Back to Dashboard</span>
        </router-link>
      </div>
      <h1>Hotel Bookings</h1>
      <p class="page-subtitle">Manage bookings for your hotel</p>
    </div>

    <!-- Loading States -->
    <div v-if="loading || hotelLoading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>{{ hotelLoading ? 'Loading hotel information...' : 'Loading bookings...' }}</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="error-message">{{ error }}</div>

    <!-- Hotel Info Card -->
    <div v-if="hotel && !hotelLoading" class="hotel-info-card">
      <div class="hotel-info-content">
        <div class="hotel-icon">🏨</div>
        <div class="hotel-details">
          <h2>{{ hotel.user?.name || 'My Hotel' }}</h2>
          <p class="hotel-location">📍 {{ hotel.location }}</p>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="bookings.length === 0 && !loading && !hotelLoading" class="empty-state">
      <div class="empty-icon">📋</div>
      <h2>No Bookings Found</h2>
      <p>No bookings have been made for your hotel yet.</p>
    </div>

    <!-- Bookings List -->
    <div v-if="bookings.length > 0" class="bookings-container">
      <div class="bookings-stats">
        <div class="stat-card">
          <div class="stat-value">{{ bookings.length }}</div>
          <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ confirmedCount }}</div>
          <div class="stat-label">Confirmed</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ pendingCount }}</div>
          <div class="stat-label">Pending</div>
        </div>
      </div>

      <div class="bookings-grid">
        <div
          v-for="booking in bookings"
          :key="booking.id"
          class="booking-card"
          :class="`status-${booking.status}`"
        >
          <!-- Booking Header -->
          <div class="booking-card-header">
            <div class="booking-id-section">
              <span class="booking-label">Booking</span>
              <h3 class="booking-id">#{{ booking.id }}</h3>
            </div>
            <span :class="['status-badge', `badge-${booking.status}`]">
              {{ formatStatus(booking.status) }}
            </span>
          </div>

          <!-- Guest Information -->
          <div v-if="booking.user" class="guest-section">
            <div class="guest-header">
              <div class="guest-icon">👤</div>
              <div class="guest-info">
                <span class="guest-name">{{ booking.user.name }}</span>
                <span class="guest-email">{{ booking.user.email }}</span>
              </div>
            </div>
          </div>

          <!-- Booking Dates -->
          <div class="booking-dates-section">
            <div class="date-card">
              <div class="date-icon">📅</div>
              <div class="date-info">
                <span class="date-label">Check-in</span>
                <span class="date-value">{{ formatDate(booking.startDate) }}</span>
              </div>
            </div>
            <div class="date-separator">→</div>
            <div class="date-card">
              <div class="date-icon">📅</div>
              <div class="date-info">
                <span class="date-label">Check-out</span>
                <span class="date-value">{{ formatDate(booking.endDate) }}</span>
              </div>
            </div>
          </div>

          <!-- Booking Details -->
          <div class="booking-details">
            <div class="detail-row">
              <span class="detail-label">Total Price</span>
              <span class="detail-value price">{{ booking.totalPrice || 'N/A' }} €</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Nights</span>
              <span class="detail-value">{{ calculateNights(booking.startDate, booking.endDate) }} nights</span>
            </div>
          </div>

          <!-- Rooms Section -->
          <div v-if="booking.rooms && booking.rooms.length > 0" class="booking-rooms-section">
            <h4 class="section-title">Rooms</h4>
            <div class="rooms-list">
              <div
                v-for="room in booking.rooms"
                :key="room.id"
                class="room-item"
              >
                <div class="room-icon">🛏️</div>
                <div class="room-info">
                  <span class="room-name">{{ room.name }}</span>
                  <span class="room-capacity">{{ room.capacity }} guests</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Guests Section -->
          <div v-if="booking.guests && booking.guests.length > 0" class="booking-guests-section">
            <h4 class="section-title">Registered Guests</h4>
            <div class="guests-list">
              <div
                v-for="guest in booking.guests"
                :key="guest.id"
                class="guest-item"
              >
                <div class="guest-icon-small">👤</div>
                <div class="guest-info-small">
                  <span class="guest-name-small">{{ guest.name }}</span>
                  <span class="guest-id">ID: {{ guest.idNumber }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Booking Actions (Admin) -->
          <div class="booking-actions">
            <div v-if="booking.status === 'pending'" class="pending-actions">
              <button
                @click="updateBookingStatus(booking.id, 'confirmed')"
                class="btn-accept"
                :disabled="updating === booking.id"
              >
                {{ updating === booking.id ? 'Updating...' : '✓ Accept' }}
              </button>
              <button
                @click="updateBookingStatus(booking.id, 'cancelled')"
                class="btn-reject"
                :disabled="updating === booking.id"
              >
                {{ updating === booking.id ? 'Updating...' : '✗ Reject' }}
              </button>
            </div>
            <div v-else-if="booking.status === 'confirmed'" class="confirmed-badge">
              ✅ Confirmed - Guest can check in
            </div>
            <div v-else-if="booking.status === 'cancelled'" class="cancelled-badge">
              ❌ Rejected
            </div>
            <div v-else-if="booking.status === 'finished'" class="completed-badge">
              ✓ Completed
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { hotelService } from '../../services/hotelService'
import { bookingService } from '../../services/bookingService'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const hotel = ref(null)
const bookings = ref([])
const loading = ref(true)
const hotelLoading = ref(true)
const error = ref('')
const updating = ref(null)

const confirmedCount = computed(() => {
  return bookings.value.filter(b => b.status === 'confirmed').length
})

const pendingCount = computed(() => {
  return bookings.value.filter(b => b.status === 'pending').length
})

onMounted(async () => {
  await loadHotel()
  if (hotel.value) {
    await loadBookings()
  }
})

const loadHotel = async () => {
  if (!authStore.state.user) {
    error.value = 'Not authenticated'
    hotelLoading.value = false
    return
  }

  try {
    const hotels = await hotelService.getHotels()
    hotel.value = hotels.find(h => h.user_id === authStore.state.user.id)
    
    if (!hotel.value) {
      error.value = 'Hotel not found for this user'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load hotel information'
  } finally {
    hotelLoading.value = false
  }
}

const loadBookings = async () => {
  if (!hotel.value) {
    return
  }

  loading.value = true
  error.value = ''
  try {
    const data = await bookingService.getBookingsByHotelId(hotel.value.id)
    
    if (data && data.bookings && Array.isArray(data.bookings)) {
      // The new endpoint returns bookings with all relationships already loaded
      bookings.value = data.bookings.map(booking => {
        // Rooms are already loaded via the relationship
        const rooms = booking.rooms ? booking.rooms.map(room => ({
          id: room.id,
          name: room.name,
          capacity: room.capacity || 'N/A'
        })) : []
        
        return {
          ...booking,
          rooms: rooms,
          user: booking.user || null,
          guests: booking.guests || []
        }
      })
    } else {
      bookings.value = []
    }
  } catch (err) {
    console.error('Failed to load bookings:', err)
    if (err.response?.status === 404) {
      bookings.value = []
    } else {
      error.value = err.response?.data?.message || 'Failed to load bookings'
      bookings.value = []
    }
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatStatus = (status) => {
  const statusMap = {
    'pending': 'Pending',
    'confirmed': 'Confirmed',
    'cancelled': 'Cancelled',
    'finished': 'Completed'
  }
  return statusMap[status] || status
}

const calculateNights = (startDate, endDate) => {
  const start = new Date(startDate)
  const end = new Date(endDate)
  const diffTime = Math.abs(end - start)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
}

const updateBookingStatus = async (bookingId, status) => {
  updating.value = bookingId
  try {
    await bookingService.updateBookingStatus(bookingId, status)
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update booking status'
  } finally {
    updating.value = null
  }
}
</script>

<style scoped>
.admin-bookings-page {
  min-height: 100vh;
  background: #f8f9fa;
  padding: 2rem;
}

.page-header {
  max-width: 1400px;
  margin: 0 auto 2rem;
}

.page-header-top {
  margin-bottom: 1rem;
}

.back-to-dashboard-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-decoration: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
  border: none;
  cursor: pointer;
}

.back-to-dashboard-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}

.back-icon {
  font-size: 1.2rem;
  font-weight: 700;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.page-subtitle {
  color: #7f8c8d;
  font-size: 1.1rem;
}

/* Loading State */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  color: #7f8c8d;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #f0f0f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Error Message */
.error-message {
  background: #fee;
  color: #c33;
  padding: 1rem;
  border-radius: 8px;
  margin: 1rem 0;
  border: 1px solid #fcc;
  max-width: 1400px;
  margin-left: auto;
  margin-right: auto;
}

/* Hotel Info Card */
.hotel-info-card {
  max-width: 1400px;
  margin: 0 auto 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.hotel-info-content {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.hotel-icon {
  font-size: 3rem;
}

.hotel-details h2 {
  color: white;
  font-size: 1.75rem;
  margin-bottom: 0.5rem;
}

.hotel-location {
  color: rgba(255, 255, 255, 0.9);
  font-size: 1.1rem;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  max-width: 600px;
  margin: 0 auto;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h2 {
  font-size: 1.75rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #7f8c8d;
  margin-bottom: 2rem;
  font-size: 1.1rem;
}

/* Bookings Container */
.bookings-container {
  max-width: 1400px;
  margin: 0 auto;
}

/* Stats Cards */
.bookings-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.stat-value {
  font-size: 2.5rem;
  font-weight: 700;
  color: #667eea;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 0.9rem;
  color: #7f8c8d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Bookings Grid */
.bookings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 2rem;
}

/* Booking Card */
.booking-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border-top: 4px solid transparent;
}

.booking-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.booking-card.status-pending {
  border-top-color: #f39c12;
}

.booking-card.status-confirmed {
  border-top-color: #27ae60;
}

.booking-card.status-cancelled {
  border-top-color: #e74c3c;
}

.booking-card.status-finished {
  border-top-color: #3498db;
}

/* Booking Header */
.booking-card-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.booking-id-section {
  display: flex;
  flex-direction: column;
}

.booking-label {
  font-size: 0.85rem;
  color: #7f8c8d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.25rem;
}

.booking-id {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: capitalize;
}

.badge-pending {
  background: #fff3cd;
  color: #856404;
}

.badge-confirmed {
  background: #d4edda;
  color: #155724;
}

.badge-cancelled {
  background: #f8d7da;
  color: #721c24;
}

.badge-finished {
  background: #d1ecf1;
  color: #0c5460;
}

/* Guest Section */
.guest-section {
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.guest-header {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.guest-icon {
  font-size: 2rem;
}

.guest-info {
  display: flex;
  flex-direction: column;
}

.guest-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.25rem;
}

.guest-email {
  font-size: 0.9rem;
  color: #7f8c8d;
}

/* Dates Section */
.booking-dates-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
  border-radius: 12px;
}

.date-card {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.date-icon {
  font-size: 1.5rem;
}

.date-info {
  display: flex;
  flex-direction: column;
}

.date-label {
  font-size: 0.85rem;
  color: #7f8c8d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.25rem;
}

.date-value {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
}

.date-separator {
  font-size: 1.5rem;
  color: #667eea;
  font-weight: 600;
}

/* Booking Details */
.booking-details {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.detail-row {
  display: flex;
  flex-direction: column;
}

.detail-label {
  font-size: 0.85rem;
  color: #7f8c8d;
  margin-bottom: 0.25rem;
}

.detail-value {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
}

.detail-value.price {
  color: #27ae60;
  font-size: 1.25rem;
}

/* Rooms Section */
.booking-rooms-section,
.booking-guests-section {
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 1rem;
}

.rooms-list,
.guests-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.room-item,
.guest-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.room-icon,
.guest-icon-small {
  font-size: 1.5rem;
}

.room-info,
.guest-info-small {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.room-name,
.guest-name-small {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.25rem;
}

.room-capacity {
  font-size: 0.9rem;
  color: #7f8c8d;
}

.guest-id {
  font-size: 0.9rem;
  color: #7f8c8d;
}

/* Booking Actions */
.booking-actions {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 2px solid #f0f0f0;
}

.pending-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.btn-accept {
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-accept:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.btn-reject {
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-reject:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

.btn-accept:disabled,
.btn-reject:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.confirmed-badge,
.cancelled-badge,
.completed-badge {
  text-align: center;
  padding: 0.875rem;
  border-radius: 12px;
  font-weight: 600;
}

.confirmed-badge {
  background: #d4edda;
  color: #155724;
}

.cancelled-badge {
  background: #f8d7da;
  color: #721c24;
}

.completed-badge {
  background: #d1ecf1;
  color: #0c5460;
}

/* Responsive Design */
@media (max-width: 768px) {
  .admin-bookings-page {
    padding: 1rem;
  }

  .page-header h1 {
    font-size: 2rem;
  }

  .bookings-grid {
    grid-template-columns: 1fr;
  }

  .booking-dates-section {
    flex-direction: column;
  }

  .date-separator {
    transform: rotate(90deg);
  }

  .booking-details {
    grid-template-columns: 1fr;
  }

  .pending-actions {
    grid-template-columns: 1fr;
  }
}
</style>
