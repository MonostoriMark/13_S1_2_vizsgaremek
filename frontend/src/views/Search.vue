<template>
  <div class="search-page">
    <h1>Search Hotels</h1>
    <div class="search-form card">
      <form @submit.prevent="handleSearch">
        <div class="form-row">
          <div class="form-group">
            <label for="city">City</label>
            <input
              id="city"
              v-model="searchParams.city"
              type="text"
              required
              placeholder="Enter city name"
            />
          </div>
          <div class="form-group">
            <label for="startDate">Check-in</label>
            <input
              id="startDate"
              v-model="searchParams.startDate"
              type="date"
              required
              :min="minDate"
            />
          </div>
          <div class="form-group">
            <label for="endDate">Check-out</label>
            <input
              id="endDate"
              v-model="searchParams.endDate"
              type="date"
              required
              :min="searchParams.startDate || minDate"
            />
          </div>
          <div class="form-group">
            <label for="guests">Guests</label>
            <input
              id="guests"
              v-model.number="searchParams.guests"
              type="number"
              required
              min="1"
            />
          </div>
        </div>
        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? 'Searching...' : 'Search' }}
        </button>
      </form>
    </div>

    <div v-if="error" class="error-message">{{ error }}</div>

    <div v-if="loading" class="loading">Searching hotels...</div>

    <div v-if="results.length > 0" class="results">
      <h2>Search Results</h2>
      <div v-for="hotel in results" :key="hotel.hotel_id" class="hotel-card card">
        <div class="hotel-header">
          <h3>{{ hotel.location }}</h3>
          <span v-if="hotel.starRating" class="stars">
            {{ '★'.repeat(hotel.starRating) }}
          </span>
        </div>
        <p class="hotel-type">{{ hotel.type }}</p>
        <div v-if="hotel.tags && hotel.tags.length > 0" class="tags">
          <span v-for="tag in hotel.tags" :key="tag" class="tag">{{ tag }}</span>
        </div>
        <div class="plans">
          <h4>Available Plans:</h4>
          <div v-for="plan in hotel.plans" :key="plan.label" class="plan-card">
            <div class="plan-header">
              <strong>{{ plan.label }}</strong>
              <span class="plan-price">{{ plan.total_price }} €</span>
            </div>
            <div class="plan-rooms">
              <div v-for="room in plan.rooms" :key="room.room_id" class="room-info">
                {{ room.name }} ({{ room.capacity }} guests) - {{ room.price }} €
              </div>
            </div>
            <button
              @click="selectPlan(hotel.hotel_id, plan)"
              class="btn-primary"
              :disabled="!isAuthenticated"
            >
              Select Plan
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!loading && results.length === 0 && hasSearched" class="no-results">
      No hotels found matching your criteria.
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { searchService } from '../services/searchService'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const searchParams = ref({
  city: '',
  startDate: '',
  endDate: '',
  guests: 1
})

const results = ref([])
const error = ref('')
const loading = ref(false)
const hasSearched = ref(false)

const minDate = computed(() => {
  return new Date().toISOString().split('T')[0]
})

const isAuthenticated = computed(() => authStore.state.isAuthenticated)

const handleSearch = async () => {
  error.value = ''
  loading.value = true
  hasSearched.value = true

  try {
    const data = await searchService.search(
      searchParams.value.city,
      searchParams.value.startDate,
      searchParams.value.endDate,
      searchParams.value.guests
    )
    results.value = data
  } catch (err) {
    error.value = err.response?.data?.message || 'Search failed'
    results.value = []
  } finally {
    loading.value = false
  }
}

const selectPlan = (hotelId, plan) => {
  if (!isAuthenticated.value) {
    router.push('/login')
    return
  }

  // Store booking data and navigate to hotel detail
  const bookingData = {
    hotelId,
    plan,
    searchParams: { ...searchParams.value }
  }
  sessionStorage.setItem('pendingBooking', JSON.stringify(bookingData))
  router.push(`/hotel/${hotelId}`)
}
</script>

<style scoped>
.search-page h1 {
  margin-bottom: 2rem;
}

.search-form {
  margin-bottom: 2rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.results h2 {
  margin: 2rem 0 1rem;
}

.hotel-card {
  margin-bottom: 2rem;
}

.hotel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.hotel-header h3 {
  margin: 0;
}

.stars {
  color: #f39c12;
  font-size: 1.2rem;
}

.hotel-type {
  text-transform: capitalize;
  color: #7f8c8d;
  margin-bottom: 0.5rem;
}

.tags {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.tag {
  background-color: #ecf0f1;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  color: #2c3e50;
}

.plans {
  margin-top: 1.5rem;
}

.plans h4 {
  margin-bottom: 1rem;
}

.plan-card {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.plan-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.plan-price {
  font-size: 1.2rem;
  font-weight: bold;
  color: #27ae60;
}

.plan-rooms {
  margin-bottom: 1rem;
}

.room-info {
  padding: 0.5rem;
  background-color: #f8f9fa;
  border-radius: 4px;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.no-results {
  text-align: center;
  padding: 2rem;
  color: #7f8c8d;
}
</style>


