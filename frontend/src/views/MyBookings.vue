<template>
  <div class="bookings-page">
    <h1>My Bookings</h1>
    <div v-if="loading" class="loading">Loading bookings...</div>
    <div v-if="error" class="error-message">{{ error }}</div>

    <div v-if="bookings.length === 0 && !loading" class="no-bookings">
      <p>You don't have any bookings yet.</p>
      <router-link to="/search" class="btn-primary">Search Hotels</router-link>
    </div>

    <div v-if="bookings.length > 0" class="bookings-list">
      <div v-for="booking in bookings" :key="booking.id" class="booking-card card">
        <div class="booking-header">
          <h3>Booking #{{ booking.id }}</h3>
          <span :class="['status-badge', `status-${booking.status}`]">
            {{ booking.status }}
          </span>
        </div>
        <div class="booking-details">
          <p><strong>Check-in:</strong> {{ formatDate(booking.startDate) }}</p>
          <p><strong>Check-out:</strong> {{ formatDate(booking.endDate) }}</p>
          <p><strong>Total Price:</strong> {{ booking.totalPrice }} €</p>
        </div>
        <div v-if="booking.rooms && booking.rooms.length > 0" class="booking-rooms">
          <h4>Rooms:</h4>
          <ul>
            <li v-for="room in booking.rooms" :key="room.id">
              {{ room.name }} ({{ room.capacity }} guests)
            </li>
          </ul>
        </div>
        <div v-if="booking.services && booking.services.length > 0" class="booking-services">
          <h4>Services:</h4>
          <ul>
            <li v-for="service in booking.services" :key="service.id">
              {{ service.name }} - {{ service.price }} €
            </li>
          </ul>
        </div>
        <div class="booking-actions">
          <button
            v-if="booking.status === 'pending'"
            @click="cancelBooking(booking.id)"
            class="btn-danger"
            :disabled="deleting === booking.id"
          >
            {{ deleting === booking.id ? 'Cancelling...' : 'Cancel Booking' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { bookingService } from '../services/bookingService'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const bookings = ref([])
const loading = ref(true)
const error = ref('')
const deleting = ref(null)

onMounted(async () => {
  await loadBookings()
})

const loadBookings = async () => {
  if (!authStore.state.user) {
    error.value = 'Not authenticated'
    loading.value = false
    return
  }

  try {
    const data = await bookingService.getBookingsByUserId(authStore.state.user.id)
    bookings.value = data.bookings || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load bookings'
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

const cancelBooking = async (bookingId) => {
  if (!confirm('Are you sure you want to cancel this booking?')) {
    return
  }

  deleting.value = bookingId
  try {
    await bookingService.deleteBooking(bookingId)
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to cancel booking'
  } finally {
    deleting.value = null
  }
}
</script>

<style scoped>
.bookings-page h1 {
  margin-bottom: 2rem;
}

.no-bookings {
  text-align: center;
  padding: 3rem;
  background-color: #ecf0f1;
  border-radius: 8px;
}

.no-bookings p {
  margin-bottom: 1rem;
  color: #7f8c8d;
}

.bookings-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.booking-card {
  border-left: 4px solid #3498db;
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.booking-header h3 {
  margin: 0;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status-pending {
  background-color: #fff3cd;
  color: #856404;
}

.status-confirmed {
  background-color: #d4edda;
  color: #155724;
}

.status-cancelled {
  background-color: #f8d7da;
  color: #721c24;
}

.status-completed {
  background-color: #d1ecf1;
  color: #0c5460;
}

.booking-details {
  margin: 1rem 0;
}

.booking-details p {
  margin: 0.5rem 0;
}

.booking-rooms,
.booking-services {
  margin: 1rem 0;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 4px;
}

.booking-rooms h4,
.booking-services h4 {
  margin-bottom: 0.5rem;
}

.booking-rooms ul,
.booking-services ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.booking-rooms li,
.booking-services li {
  padding: 0.25rem 0;
}

.booking-actions {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #ddd;
}
</style>


