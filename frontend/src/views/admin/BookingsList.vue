<template>
  <div class="admin-bookings-page">
    <h1>Hotel Bookings</h1>
    <div v-if="loading" class="loading">Loading bookings...</div>
    <div v-if="error" class="error-message">{{ error }}</div>
    <div v-if="hotelLoading" class="loading">Loading hotel information...</div>

    <div v-if="hotel" class="hotel-info card">
      <h2>{{ hotel.user?.name || 'My Hotel' }}</h2>
      <p><strong>Location:</strong> {{ hotel.location }}</p>
    </div>

    <div v-if="bookings.length === 0 && !loading && !hotelLoading" class="no-bookings">
      <p>No bookings found for your hotel.</p>
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
          <p><strong>Total Price:</strong> {{ booking.totalPrice || 'N/A' }} €</p>
          <p v-if="booking.user"><strong>Guest:</strong> {{ booking.user.name }} ({{ booking.user.email }})</p>
        </div>
        <div v-if="booking.rooms && booking.rooms.length > 0" class="booking-rooms">
          <h4>Rooms:</h4>
          <ul>
            <li v-for="room in booking.rooms" :key="room.id">
              {{ room.name }} ({{ room.capacity }} guests)
            </li>
          </ul>
        </div>
        <div v-if="booking.guests && booking.guests.length > 0" class="booking-guests">
          <h4>Guests:</h4>
          <ul>
            <li v-for="guest in booking.guests" :key="guest.id">
              {{ guest.name }} (ID: {{ guest.idNumber }})
            </li>
          </ul>
        </div>
        <div class="booking-actions">
          <button
            v-if="booking.status === 'pending'"
            @click="updateBookingStatus(booking.id, 'confirmed')"
            class="btn-success"
            :disabled="updating === booking.id"
          >
            {{ updating === booking.id ? 'Updating...' : 'Accept' }}
          </button>
          <button
            v-if="booking.status === 'pending'"
            @click="updateBookingStatus(booking.id, 'cancelled')"
            class="btn-danger"
            :disabled="updating === booking.id"
          >
            {{ updating === booking.id ? 'Updating...' : 'Reject' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
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
    // Get all hotels and find the one belonging to the logged-in user
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
    // Get bookings for the hotel
    // Note: The /devices/bookings endpoint only returns confirmed bookings
    // For a complete solution, the backend should provide an endpoint that returns
    // all bookings (pending, confirmed, etc.) for a hotel
    const data = await bookingService.getBookingsByHotelId(hotel.value.id)
    
    // The response structure from /devices/bookings/{hotelId}
    if (data && data.bookings && Array.isArray(data.bookings)) {
      bookings.value = data.bookings.map(booking => {
        // Find related rooms from relations
        const relatedRooms = data.relations
          ?.filter(r => r.booking_id === booking.id)
          .map(r => {
            const room = data.rooms?.find(rm => rm.id === r.rooms_id)
            return room ? { id: room.id, name: room.name, capacity: room.capacity || 'N/A' } : null
          })
          .filter(Boolean) || []
        
        return {
          ...booking,
          rooms: relatedRooms,
          // Get user info if available
          user: booking.user || null
        }
      })
    } else {
      bookings.value = []
    }
  } catch (err) {
    console.error('Failed to load bookings:', err)
    // Handle 404 (no bookings) gracefully
    if (err.response?.status === 404) {
      bookings.value = []
      // Don't show error for empty results
    } else {
      error.value = 'Failed to load bookings. Note: This endpoint only shows confirmed bookings. Pending bookings may require a backend extension.'
      bookings.value = []
    }
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
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
.admin-bookings-page h1 {
  margin-bottom: 2rem;
}

.hotel-info {
  margin-bottom: 2rem;
  background-color: #e8f5e9;
}

.hotel-info h2 {
  margin-bottom: 0.5rem;
}

.no-bookings {
  text-align: center;
  padding: 3rem;
  background-color: #ecf0f1;
  border-radius: 8px;
}

.bookings-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.booking-card {
  border-left: 4px solid #27ae60;
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
.booking-guests {
  margin: 1rem 0;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 4px;
}

.booking-rooms h4,
.booking-guests h4 {
  margin-bottom: 0.5rem;
}

.booking-rooms ul,
.booking-guests ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.booking-rooms li,
.booking-guests li {
  padding: 0.25rem 0;
}

.booking-actions {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #ddd;
  display: flex;
  gap: 1rem;
}
</style>

