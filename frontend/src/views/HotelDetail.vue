<template>
  <div class="hotel-detail-page">
    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Loading hotel details...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="error-message">{{ error }}</div>

    <!-- Hotel Content -->
    <div v-if="hotel && !loading" class="hotel-content-wrapper">
      <div class="hotel-content">
      <!-- Hotel Header with Image -->
      <div class="hotel-header">
        <div class="hotel-image-gallery">
          <img
            :src="getHotelImage()"
            :alt="hotel.location"
            @error="handleImageError"
            class="main-hotel-image"
          />
          <div v-if="hotel.starRating" class="star-overlay">
            {{ '★'.repeat(hotel.starRating) }}
          </div>
        </div>
        <div class="hotel-info-header">
          <div class="hotel-title-section">
            <h1 class="hotel-name">{{ hotel.user?.name || hotel.location || 'Hotel' }}</h1>
            <div class="hotel-meta">
              <span class="location-badge">📍 {{ hotel.location }}</span>
              <span v-if="hotel.type" class="type-badge">{{ hotel.type }}</span>
            </div>
            <div v-if="hotel.tags && hotel.tags.length > 0" class="hotel-tags">
              <span
                v-for="(tag, idx) in hotel.tags"
                :key="typeof tag === 'object' ? tag.id : idx"
                class="tag"
              >
                {{ typeof tag === 'object' ? tag.name : tag }}
              </span>
            </div>
          </div>
          <p v-if="hotel.description" class="hotel-description">{{ hotel.description }}</p>
        </div>
      </div>

      <!-- Booking Section -->
      <div class="booking-section">
        <!-- Search Results Available - Show Plan Selection -->
        <div v-if="searchResultsData && searchResultsData.hotel.plans && searchResultsData.hotel.plans.length > 0" class="plans-section">
          <div class="booking-info-card">
            <h2 class="section-title">Select Your Plan</h2>
            <div class="booking-dates">
              <div class="date-item">
                <span class="date-label">Check-in</span>
                <span class="date-value">{{ formatDate(searchResultsData.searchParams.startDate) }}</span>
              </div>
              <div class="date-item">
                <span class="date-label">Check-out</span>
                <span class="date-value">{{ formatDate(searchResultsData.searchParams.endDate) }}</span>
              </div>
              <div class="date-item">
                <span class="date-label">Guests</span>
                <span class="date-value">{{ searchResultsData.searchParams.guests }}</span>
              </div>
            </div>
          </div>

          <!-- Plans Carousel -->
          <div class="plans-carousel-container">
            <div class="plans-carousel" :style="{ transform: `translateX(-${currentPlanIndex * 100}%)` }">
              <div
                v-for="(plan, index) in searchResultsData.hotel.plans"
                :key="index"
                class="plan-card"
                :class="{ 'selected': selectedPlanIndex === index }"
                @click="selectPlan(index)"
              >
                <div class="plan-header">
                  <div class="plan-badge" :class="getPlanBadgeClass(plan.label)">
                    {{ plan.label }}
                  </div>
                  <div class="plan-price">
                    <span class="price-amount">{{ plan.total_price }} €</span>
                    <span class="price-label">Total Price</span>
                  </div>
                </div>
                <div class="plan-rooms">
                  <div class="rooms-header">
                    <span class="rooms-count">{{ plan.room_count }} {{ plan.room_count === 1 ? 'Room' : 'Rooms' }}</span>
                  </div>
                  <div v-for="room in plan.rooms" :key="room.room_id" class="room-detail">
                    <div class="room-info">
                      <h4 class="room-name">{{ room.name }}</h4>
                      <div class="room-specs">
                        <span class="spec-item">👥 {{ room.capacity }} guests</span>
                        <span class="spec-item">💰 {{ room.price }} €</span>
                      </div>
                      <div v-if="room.tags && room.tags.length > 0" class="room-tags">
                        <span
                          v-for="(tag, idx) in room.tags"
                          :key="typeof tag === 'object' ? tag.id : idx"
                          class="room-tag"
                        >
                          {{ typeof tag === 'object' ? tag.name : tag }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                <button
                  class="btn-select-plan"
                  :class="{ 'selected': selectedPlanIndex === index }"
                  @click.stop="confirmPlan(index)"
                >
                  {{ selectedPlanIndex === index ? 'Selected' : 'Select Plan' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Carousel Navigation -->
          <div v-if="searchResultsData.hotel.plans.length > 1" class="carousel-controls">
            <button
              class="carousel-btn prev"
              @click="previousPlan"
              :disabled="currentPlanIndex === 0"
            >
              ←
            </button>
            <div class="carousel-dots">
              <span
                v-for="(plan, index) in searchResultsData.hotel.plans"
                :key="index"
                class="dot"
                :class="{ 'active': currentPlanIndex === index }"
                @click="goToPlan(index)"
              ></span>
            </div>
            <button
              class="carousel-btn next"
              @click="nextPlan"
              :disabled="currentPlanIndex === searchResultsData.hotel.plans.length - 1"
            >
              →
            </button>
          </div>

          <!-- Selected Plan Summary -->
          <div v-if="selectedPlanIndex !== null" class="selected-plan-summary">
            <h3>Selected Plan</h3>
            <div class="summary-content">
              <div class="summary-plan">
                <strong>{{ searchResultsData.hotel.plans[selectedPlanIndex].label }}</strong>
                <span class="summary-price">{{ searchResultsData.hotel.plans[selectedPlanIndex].total_price }} €</span>
              </div>
              <div class="summary-rooms">
                <div
                  v-for="room in searchResultsData.hotel.plans[selectedPlanIndex].rooms"
                  :key="room.room_id"
                  class="summary-room"
                >
                  {{ room.name }} - {{ room.price }} €
                </div>
              </div>
              <div v-if="selectedServices.length > 0" class="summary-services">
                <div class="summary-services-header">Additional Services:</div>
                <div
                  v-for="serviceId in selectedServices"
                  :key="serviceId"
                  class="summary-service"
                >
                  {{ getServiceName(serviceId) }} - {{ getServicePrice(serviceId) }} €
                </div>
              </div>
              <div class="summary-total">
                <div class="total-line">
                  <span>Plan Total:</span>
                  <span>{{ searchResultsData.hotel.plans[selectedPlanIndex].total_price }} €</span>
                </div>
                <div v-if="selectedServices.length > 0" class="total-line">
                  <span>Services Total:</span>
                  <span>{{ getSelectedServicesTotal() }} €</span>
                </div>
                <div class="total-line final-total">
                  <span><strong>Grand Total:</strong></span>
                  <span><strong>{{ getGrandTotal() }} €</strong></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Additional Services -->
          <div v-if="availableServices.length > 0" class="services-section">
            <h3 class="services-title">Additional Services</h3>
            <p class="services-subtitle">Select additional services for your stay</p>
            <div v-if="servicesLoading" class="services-loading">
              <div class="loading-spinner-small"></div>
              <span>Loading services...</span>
            </div>
            <div v-else class="services-grid">
              <label
                v-for="service in availableServices"
                :key="service.id"
                class="service-item"
                :class="{ 'selected': selectedServices.includes(service.id) }"
              >
                <input
                  type="checkbox"
                  :value="service.id"
                  v-model="selectedServices"
                  class="service-checkbox"
                />
                <div class="service-content">
                  <div class="service-info">
                    <span class="service-name">{{ service.name }}</span>
                    <span v-if="service.description" class="service-description">{{ service.description }}</span>
                  </div>
                  <span class="service-price">{{ service.price || 0 }} €</span>
                </div>
              </label>
            </div>
            <div v-if="selectedServices.length > 0" class="selected-services-summary">
              <h4>Selected Services</h4>
              <div class="selected-services-list">
                <div
                  v-for="serviceId in selectedServices"
                  :key="serviceId"
                  class="selected-service-item"
                >
                  <span>{{ getServiceName(serviceId) }}</span>
                  <span class="service-price">{{ getServicePrice(serviceId) }} €</span>
                </div>
              </div>
              <div class="services-total">
                <strong>Services Total: {{ getSelectedServicesTotal() }} €</strong>
              </div>
            </div>
          </div>
          <div v-else-if="!servicesLoading" class="no-services">
            <p>No additional services available for this hotel.</p>
          </div>

          <!-- Booking Actions -->
          <div class="booking-actions">
            <div v-if="!isAuthenticated" class="auth-prompt">
              <p>Please <router-link to="/login">login</router-link> to complete your booking.</p>
            </div>
            <div v-else class="action-buttons">
              <button
                @click="createBooking"
                class="btn-confirm-booking"
                :disabled="bookingLoading || selectedPlanIndex === null"
              >
                {{ bookingLoading ? 'Creating booking...' : 'Confirm Booking' }}
              </button>
              <button @click="goBackToSearch" class="btn-back">
                Back to Search
              </button>
            </div>
          </div>

          <!-- Messages -->
          <div v-if="bookingError" class="error-message">{{ bookingError }}</div>
          <div v-if="bookingSuccess" class="success-message">
            <p>✅ Booking created successfully!</p>
            <router-link to="/bookings" class="view-bookings-link">View my bookings</router-link>
          </div>
        </div>

        <!-- No Search Results - Show Info Message -->
        <div v-else class="no-search-results">
          <div class="info-card">
            <h2>No Booking Plans Available</h2>
            <p>Please search for hotels with your dates to see available plans.</p>
            <router-link to="/search" class="btn-primary">Search Hotels</router-link>
          </div>
        </div>
      </div>
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
const searchResultsData = ref(null)
const selectedPlanIndex = ref(null)
const currentPlanIndex = ref(0)
const selectedServices = ref([])
const availableServices = ref([])
const servicesLoading = ref(false)
const bookingLoading = ref(false)
const bookingError = ref('')
const bookingSuccess = ref(false)

const isAuthenticated = computed(() => authStore.state.isAuthenticated)

// Image fallback
const imageFallback = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'

onMounted(async () => {
  const hotelId = route.params.id

  // Load search results from session storage
  const storedSearchResults = sessionStorage.getItem('searchResults')
  if (storedSearchResults) {
    try {
      searchResultsData.value = JSON.parse(storedSearchResults)
    } catch (e) {
      console.error('Failed to parse search results:', e)
    }
  }

  // Load hotel details
  try {
    const data = await hotelService.getHotelById(hotelId)
    hotel.value = data
    
    // Load services for this hotel
    // Use hotel_id from search results if available, otherwise use hotel.id or route hotelId
    const servicesHotelId = searchResultsData.value?.hotel?.hotel_id || data.id || hotelId
    await loadServices(servicesHotelId)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load hotel details'
  } finally {
    loading.value = false
  }
})

const loadServices = async (hotelId) => {
  servicesLoading.value = true
  try {
    availableServices.value = await hotelService.getServicesByHotelId(hotelId)
  } catch (err) {
    console.error('Failed to load services:', err)
    availableServices.value = []
  } finally {
    servicesLoading.value = false
  }
}

const getHotelImage = () => {
  // Try to get image from hotel rooms
  if (hotel.value && hotel.value.rooms && hotel.value.rooms.length > 0) {
    // In a real implementation, fetch room images
    return imageFallback
  }
  return imageFallback
}

const handleImageError = (event) => {
  event.target.src = imageFallback
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getPlanBadgeClass = (label) => {
  const labelLower = label.toLowerCase()
  if (labelLower.includes('ajánlott') || labelLower.includes('recommended')) {
    return 'badge-recommended'
  } else if (labelLower.includes('olcsóbb') || labelLower.includes('cheapest')) {
    return 'badge-cheapest'
  }
  return 'badge-alternative'
}

const selectPlan = (index) => {
  selectedPlanIndex.value = index
  currentPlanIndex.value = index
}

const confirmPlan = (index) => {
  selectedPlanIndex.value = index
  // Scroll to booking actions
  setTimeout(() => {
    document.querySelector('.booking-actions')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
  }, 100)
}

const previousPlan = () => {
  if (currentPlanIndex.value > 0) {
    currentPlanIndex.value--
  }
}

const nextPlan = () => {
  if (searchResultsData.value && currentPlanIndex.value < searchResultsData.value.hotel.plans.length - 1) {
    currentPlanIndex.value++
  }
}

const goToPlan = (index) => {
  currentPlanIndex.value = index
}

const createBooking = async () => {
  if (!searchResultsData.value || selectedPlanIndex.value === null || !isAuthenticated.value) {
    return
  }

  bookingLoading.value = true
  bookingError.value = ''
  bookingSuccess.value = false

  try {
    const selectedPlan = searchResultsData.value.hotel.plans[selectedPlanIndex.value]
    const bookingData = {
      userId: authStore.state.user.id,
      hotelId: searchResultsData.value.hotel.hotel_id,
      startDate: searchResultsData.value.searchParams.startDate,
      endDate: searchResultsData.value.searchParams.endDate,
      rooms: selectedPlan.rooms.map(room => ({
        id: room.room_id,
        guests: room.capacity
      }))
    }

    // Only include services if any are selected
    if (selectedServices.value && selectedServices.value.length > 0) {
      bookingData.services = selectedServices.value
    }

    await bookingService.createBooking(bookingData)

    // Clear search results
    sessionStorage.removeItem('searchResults')
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

const goBackToSearch = () => {
  router.push('/search')
}

const getServiceName = (serviceId) => {
  const service = availableServices.value.find(s => s.id === serviceId)
  return service ? service.name : 'Unknown Service'
}

const getServicePrice = (serviceId) => {
  const service = availableServices.value.find(s => s.id === serviceId)
  return service ? (service.price || 0) : 0
}

const getSelectedServicesTotal = () => {
  return selectedServices.value.reduce((total, serviceId) => {
    return total + getServicePrice(serviceId)
  }, 0)
}

const getGrandTotal = () => {
  if (!searchResultsData.value || selectedPlanIndex.value === null) {
    return 0
  }
  const planTotal = searchResultsData.value.hotel.plans[selectedPlanIndex.value].total_price || 0
  const servicesTotal = getSelectedServicesTotal()
  return planTotal + servicesTotal
}
</script>

<style scoped>
.hotel-detail-page {
  min-height: 100vh;
  background: #f8f9fa;
  padding: 2rem 0;
}

.hotel-content-wrapper {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 1rem;
}

.hotel-content {
  width: 100%;
}

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

.error-message {
  background: #fee;
  color: #c33;
  padding: 1rem;
  border-radius: 8px;
  margin: 1rem;
  border: 1px solid #fcc;
}

/* Hotel Header */
.hotel-header {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.hotel-image-gallery {
  position: relative;
  width: 100%;
  height: 400px;
  overflow: hidden;
  background: #e0e0e0;
}

.main-hotel-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.star-overlay {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  background: rgba(255, 255, 255, 0.95);
  padding: 0.75rem 1rem;
  border-radius: 12px;
  color: #f39c12;
  font-size: 1.1rem;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.hotel-info-header {
  padding: 2rem;
}

.hotel-title-section {
  margin-bottom: 1.5rem;
}

.hotel-name {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 1rem;
}

.hotel-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.location-badge,
.type-badge {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
}

.location-badge {
  background: #e8f4f8;
  color: #667eea;
}

.type-badge {
  background: #f0f0f0;
  color: #7f8c8d;
  text-transform: capitalize;
}

.hotel-tags {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-top: 1rem;
}

.tag {
  padding: 0.4rem 0.8rem;
  background: #e8f4f8;
  color: #667eea;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
}

.hotel-description {
  color: #7f8c8d;
  line-height: 1.6;
  font-size: 1rem;
}

/* Booking Section */
.booking-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem 2rem;
}

.booking-info-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 1.5rem;
}

.booking-dates {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.date-item {
  display: flex;
  flex-direction: column;
}

.date-label {
  font-size: 0.85rem;
  color: #7f8c8d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}

.date-value {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
}

/* Plans Carousel */
.plans-carousel-container {
  position: relative;
  overflow: hidden;
  margin-bottom: 2rem;
  border-radius: 16px;
}

.plans-carousel {
  display: flex;
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  gap: 1.5rem;
}

.plan-card {
  min-width: 100%;
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  cursor: pointer;
  border: 3px solid transparent;
}

.plan-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.plan-card.selected {
  border-color: #667eea;
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
}

.plan-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.plan-badge {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
}

.badge-recommended {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.badge-cheapest {
  background: #27ae60;
  color: white;
}

.badge-alternative {
  background: #f39c12;
  color: white;
}

.plan-price {
  text-align: right;
}

.price-amount {
  display: block;
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
}

.price-label {
  font-size: 0.85rem;
  color: #7f8c8d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.plan-rooms {
  margin-bottom: 1.5rem;
}

.rooms-header {
  margin-bottom: 1rem;
}

.rooms-count {
  font-size: 0.9rem;
  color: #7f8c8d;
  font-weight: 500;
}

.room-detail {
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
  margin-bottom: 1rem;
}

.room-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.room-specs {
  display: flex;
  gap: 1rem;
  margin-bottom: 0.5rem;
  flex-wrap: wrap;
}

.spec-item {
  font-size: 0.9rem;
  color: #7f8c8d;
}

.room-tags {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-top: 0.5rem;
}

.room-tag {
  padding: 0.25rem 0.75rem;
  background: white;
  color: #667eea;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 500;
}

.btn-select-plan {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-select-plan:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-select-plan.selected {
  background: #27ae60;
}

/* Carousel Controls */
.carousel-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.carousel-btn {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: 2px solid #667eea;
  background: white;
  color: #667eea;
  font-size: 1.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.carousel-btn:hover:not(:disabled) {
  background: #667eea;
  color: white;
  transform: scale(1.1);
}

.carousel-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.carousel-dots {
  display: flex;
  gap: 0.5rem;
}

.dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #ddd;
  cursor: pointer;
  transition: all 0.3s ease;
}

.dot.active {
  background: #667eea;
  transform: scale(1.2);
}

/* Selected Plan Summary */
.selected-plan-summary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2rem;
}

.selected-plan-summary h3 {
  margin-bottom: 1rem;
  font-size: 1.25rem;
}

.summary-content {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
}

.summary-plan {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.summary-price {
  font-size: 1.5rem;
  font-weight: 700;
}

.summary-rooms {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.summary-room {
  font-size: 0.95rem;
  opacity: 0.9;
}

/* Services Section */
.services-section {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.services-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 1.5rem;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
}

.service-item {
  display: flex;
  align-items: center;
  padding: 1rem;
  border: 2px solid #f0f0f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.service-item:hover {
  border-color: #667eea;
  background: #f8f9ff;
}

.service-checkbox {
  width: 20px;
  height: 20px;
  margin-right: 1rem;
  cursor: pointer;
}

.service-content {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.service-name {
  font-weight: 500;
  color: #2c3e50;
}

.service-price {
  font-weight: 600;
  color: #667eea;
  font-size: 1.1rem;
}

.service-item.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
}

.service-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.service-description {
  font-size: 0.85rem;
  color: #7f8c8d;
  font-style: italic;
}

.services-subtitle {
  color: #7f8c8d;
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
}

.services-loading {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 2rem;
  color: #7f8c8d;
  justify-content: center;
}

.loading-spinner-small {
  width: 24px;
  height: 24px;
  border: 3px solid #f0f0f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.selected-services-summary {
  margin-top: 2rem;
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 12px;
  border: 2px solid #e0e0e0;
}

.selected-services-summary h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 1rem;
}

.selected-services-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.selected-service-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background: white;
  border-radius: 8px;
  font-size: 0.95rem;
}

.services-total {
  padding-top: 1rem;
  border-top: 2px solid #e0e0e0;
  text-align: right;
  font-size: 1.1rem;
  color: #2c3e50;
}

.no-services {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  color: #7f8c8d;
  font-style: italic;
}

.summary-services {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.summary-services-header {
  font-weight: 600;
  margin-bottom: 0.5rem;
  opacity: 0.9;
}

.summary-service {
  font-size: 0.95rem;
  opacity: 0.9;
  margin-left: 1rem;
}

.summary-total {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 2px solid rgba(255, 255, 255, 0.3);
}

.total-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 1rem;
}

.total-line.final-total {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 2px solid rgba(255, 255, 255, 0.3);
  font-size: 1.25rem;
}

/* Booking Actions */
.booking-actions {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.auth-prompt {
  text-align: center;
  padding: 1.5rem;
  background: #fff3cd;
  border-radius: 12px;
  color: #856404;
}

.auth-prompt a {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.btn-confirm-booking {
  flex: 1;
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 200px;
}

.btn-confirm-booking:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-confirm-booking:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-back {
  padding: 1rem 2rem;
  background: white;
  color: #667eea;
  border: 2px solid #667eea;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-back:hover {
  background: #f8f9ff;
}

.success-message {
  background: #d4edda;
  color: #155724;
  padding: 1.5rem;
  border-radius: 12px;
  margin-top: 1rem;
  text-align: center;
}

.view-bookings-link {
  display: inline-block;
  margin-top: 0.5rem;
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
}

.view-bookings-link:hover {
  text-decoration: underline;
}

/* No Search Results */
.no-search-results {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}

.info-card {
  background: white;
  border-radius: 16px;
  padding: 3rem;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  max-width: 500px;
}

.info-card h2 {
  margin-bottom: 1rem;
  color: #2c3e50;
}

.info-card p {
  color: #7f8c8d;
  margin-bottom: 2rem;
}

.btn-primary {
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* Responsive Design */
@media (max-width: 768px) {
  .hotel-image-gallery {
    height: 300px;
  }

  .hotel-name {
    font-size: 1.5rem;
  }

  .plans-carousel {
    gap: 1rem;
  }

  .plan-card {
    padding: 1.5rem;
  }

  .price-amount {
    font-size: 1.5rem;
  }

  .action-buttons {
    flex-direction: column;
  }

  .btn-confirm-booking,
  .btn-back {
    width: 100%;
  }

  .services-grid {
    grid-template-columns: 1fr;
  }
}
</style>
