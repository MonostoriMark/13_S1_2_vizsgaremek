<template>
  <div class="hotel-detail-page">
    <div v-if="loading" class="loading">Loading hotel details...</div>
    <div v-if="error" class="error-message">{{ error }}</div>

    <div v-if="hotel" class="hotel-info">
      <h1>{{ hotel.user?.name || 'Hotel' }}</h1>
      <div class="hotel-meta">
        <p><strong>Location:</strong> {{ hotel.location }}</p>
        <p><strong>Type:</strong> {{ hotel.type }}</p>
        <p v-if="hotel.starRating"><strong>Rating:</strong> {{ '★'.repeat(hotel.starRating) }}</p>
      </div>
      <p v-if="hotel.description" class="description">{{ hotel.description }}</p>

      <div v-if="pendingBooking" class="booking-section card">
        <h2>Complete Your Booking</h2>
        <div class="booking-summary">
          <p><strong>Check-in:</strong> {{ formatDate(pendingBooking.searchParams.startDate) }}</p>
          <p><strong>Check-out:</strong> {{ formatDate(pendingBooking.searchParams.endDate) }}</p>
          <p><strong>Guests:</strong> {{ pendingBooking.searchParams.guests }}</p>
          <div class="selected-plan">
            <h3>{{ pendingBooking.plan.label }}</h3>
            <p class="total-price">Total: {{ pendingBooking.plan.total_price }} €</p>
            <div class="plan-rooms">
              <div v-for="room in pendingBooking.plan.rooms" :key="room.room_id" class="room-item">
                {{ room.name }} ({{ room.capacity }} guests) - {{ room.price }} €
              </div>
            </div>
          </div>
        </div>

        <div v-if="hotel.services && hotel.services.length > 0" class="services-section">
          <h3>Additional Services</h3>
          <div v-for="service in hotel.services" :key="service.id" class="service-item">
            <label>
              <input
                type="checkbox"
                :value="service.id"
                v-model="selectedServices"
              />
              {{ service.name }} - {{ service.price }} €
            </label>
          </div>
        </div>

        <div v-if="!isAuthenticated" class="auth-prompt">
          <p>Please <router-link to="/login">login</router-link> to complete your booking.</p>
        </div>

        <button
          v-else
          @click="createBooking"
          class="btn-primary"
          :disabled="bookingLoading"
        >
          {{ bookingLoading ? 'Creating booking...' : 'Confirm Booking' }}
        </button>

        <div v-if="bookingError" class="error-message">{{ bookingError }}</div>
        <div v-if="bookingSuccess" class="success-message">
          Booking created successfully! <router-link to="/bookings">View my bookings</router-link>
        </div>
      </div>

      <div v-else class="info-message">
        <p>Select a plan from the search results to make a booking.</p>
        <router-link to="/search" class="btn-primary">Search Hotels</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { hotelService } from '../services/hotelService'
import { bookingService } from '../services/bookingService'
import { useAuthStore } from '../stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const hotel = ref(null)
const loading = ref(true)
const error = ref('')
const pendingBooking = ref(null)
const selectedServices = ref([])
const bookingLoading = ref(false)
const bookingError = ref('')
const bookingSuccess = ref(false)

const isAuthenticated = computed(() => authStore.state.isAuthenticated)

onMounted(async () => {
  const hotelId = route.params.id

  // Load pending booking from session storage
  const storedBooking = sessionStorage.getItem('pendingBooking')
  if (storedBooking) {
    try {
      pendingBooking.value = JSON.parse(storedBooking)
    } catch (e) {
      console.error('Failed to parse pending booking:', e)
    }
  }

  // Load hotel details
  try {
    const data = await hotelService.getHotelById(hotelId)
    hotel.value = data
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load hotel details'
  } finally {
    loading.value = false
  }
})

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

const createBooking = async () => {
  if (!pendingBooking.value || !isAuthenticated.value) {
    return
  }

  bookingLoading.value = true
  bookingError.value = ''
  bookingSuccess.value = false

  try {
    const bookingData = {
      userId: authStore.state.user.id,
      hotelId: pendingBooking.value.hotelId,
      startDate: pendingBooking.value.searchParams.startDate,
      endDate: pendingBooking.value.searchParams.endDate,
      rooms: pendingBooking.value.plan.rooms.map(room => ({
        id: room.room_id,
        guests: room.capacity
      }))
    }

    // Only include services if any are selected
    if (selectedServices.value && selectedServices.value.length > 0) {
      bookingData.services = selectedServices.value
    }

    await bookingService.createBooking(bookingData)

    // Clear pending booking
    sessionStorage.removeItem('pendingBooking')
    bookingSuccess.value = true

    // Redirect after 2 seconds
    setTimeout(() => {
      router.push('/bookings')
    }, 2000)
  } catch (err) {
    bookingError.value = err.response?.data?.error || err.response?.data?.message || 'Failed to create booking'
  } finally {
    bookingLoading.value = false
  }
}
</script>

<style scoped>
.hotel-detail-page {
  max-width: 800px;
}

.hotel-info h1 {
  margin-bottom: 1rem;
}

.hotel-meta {
  margin-bottom: 1rem;
  color: #7f8c8d;
}

.hotel-meta p {
  margin: 0.5rem 0;
}

.description {
  margin: 1.5rem 0;
  line-height: 1.6;
}

.booking-section {
  margin-top: 2rem;
}

.booking-summary {
  margin-bottom: 1.5rem;
}

.booking-summary p {
  margin: 0.5rem 0;
}

.selected-plan {
  margin-top: 1rem;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 4px;
}

.total-price {
  font-size: 1.2rem;
  font-weight: bold;
  color: #27ae60;
  margin: 0.5rem 0;
}

.plan-rooms {
  margin-top: 0.5rem;
}

.room-item {
  padding: 0.5rem;
  background-color: white;
  border-radius: 4px;
  margin-bottom: 0.5rem;
}

.services-section {
  margin: 1.5rem 0;
  padding-top: 1.5rem;
  border-top: 1px solid #ddd;
}

.service-item {
  margin: 0.75rem 0;
}

.service-item label {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.service-item input[type="checkbox"] {
  width: auto;
  margin-right: 0.5rem;
}

.auth-prompt {
  padding: 1rem;
  background-color: #fff3cd;
  border-radius: 4px;
  margin: 1rem 0;
}

.auth-prompt a {
  color: #3498db;
  text-decoration: none;
}

.auth-prompt a:hover {
  text-decoration: underline;
}

.info-message {
  text-align: center;
  padding: 2rem;
  background-color: #ecf0f1;
  border-radius: 4px;
  margin-top: 2rem;
}
</style>


