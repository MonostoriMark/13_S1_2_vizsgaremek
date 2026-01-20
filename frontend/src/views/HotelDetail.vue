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
                    <!-- Room Image Gallery -->
                    <div v-if="getRoomImages(room).length > 0" class="room-gallery">
                      <div class="room-gallery-main" @click.stop="openFullscreenGallery(room)">
                        <img 
                          :src="getRoomImages(room)[getRoomImageIndex(room.room_id)]" 
                          :alt="room.name"
                          @error="handleRoomImageError"
                          class="room-gallery-main-image"
                        />
                        <button 
                          v-if="getRoomImages(room).length > 1"
                          @click.stop="previousRoomImage(room.room_id)"
                          class="gallery-nav-btn gallery-prev"
                          :disabled="getRoomImageIndex(room.room_id) === 0"
                        >
                          ‹
                        </button>
                        <button 
                          v-if="getRoomImages(room).length > 1"
                          @click.stop="nextRoomImage(room.room_id)"
                          class="gallery-nav-btn gallery-next"
                          :disabled="getRoomImageIndex(room.room_id) === getRoomImages(room).length - 1"
                        >
                          ›
                        </button>
                        <div v-if="getRoomImages(room).length > 1" class="gallery-indicator">
                          {{ getRoomImageIndex(room.room_id) + 1 }} / {{ getRoomImages(room).length }}
                        </div>
                        <div class="gallery-zoom-hint">
                          <span class="zoom-icon">🔍</span> Click to view fullscreen
                        </div>
                      </div>
                      <!-- Thumbnail Gallery -->
                      <div v-if="getRoomImages(room).length > 1" class="room-gallery-thumbnails">
                        <div
                          v-for="(img, imgIdx) in getRoomImages(room)"
                          :key="imgIdx"
                          class="gallery-thumbnail"
                          :class="{ 'active': getRoomImageIndex(room.room_id) === imgIdx }"
                          @click.stop="setRoomImageIndex(room.room_id, imgIdx)"
                        >
                          <img 
                            :src="img" 
                            :alt="`${room.name} - Image ${imgIdx + 1}`"
                            @error="handleRoomImageError"
                          />
                        </div>
                      </div>
                    </div>
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
                @click="openPaymentInvoiceModal"
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

          <!-- Payment & Invoice Details Modal (pre-submit) -->
          <Transition name="modal">
            <div v-if="showPaymentInvoiceModal" class="modal-overlay pay-invoice-overlay" @click.self="closePaymentInvoiceModal">
              <div class="modal-content pay-invoice-modal glass-card">
                <div class="modal-header">
                  <h2>💳 Payment & Invoice Details</h2>
                  <button @click="closePaymentInvoiceModal" class="btn-close-modal">×</button>
                </div>

                <div class="pay-invoice-body">
                  <div class="pill-tabs">
                    <button
                      type="button"
                      class="pill"
                      :class="{ active: invoiceForm.customer_type === 'private' }"
                      @click="invoiceForm.customer_type = 'private'"
                    >
                      Private
                    </button>
                    <button
                      type="button"
                      class="pill"
                      :class="{ active: invoiceForm.customer_type === 'business' }"
                      @click="invoiceForm.customer_type = 'business'"
                    >
                      Business
                    </button>
                  </div>

                  <div class="grid-2">
                    <div class="card">
                      <h3 class="card-title">Payment method</h3>
                      <div class="radio-row">
                        <label class="radio-card">
                          <input type="radio" value="bank_transfer" v-model="paymentMethod" />
                          <div class="radio-card-content">
                            <div class="radio-title">Bank transfer</div>
                            <div class="radio-sub">Pay after invoice is sent</div>
                          </div>
                        </label>
                      </div>
                    </div>

                    <div class="card">
                      <h3 class="card-title">Invoice preview</h3>
                      <div class="preview">
                        <div class="preview-row"><span>Customer</span><strong>{{ invoiceForm.full_name || '—' }}</strong></div>
                        <div class="preview-row" v-if="invoiceForm.customer_type === 'business'"><span>Company</span><strong>{{ invoiceForm.company_name || '—' }}</strong></div>
                        <div class="preview-row"><span>Email</span><strong>{{ invoiceForm.email || '—' }}</strong></div>
                        <div class="preview-row"><span>Address</span><strong>{{ invoiceAddressPreview }}</strong></div>
                        <div class="preview-row"><span>Total</span><strong>{{ getGrandTotal() }} €</strong></div>
                      </div>
                    </div>
                  </div>

                  <div class="card">
                    <h3 class="card-title">Billing details</h3>
                    <div class="form-grid">
                      <div class="form-field">
                        <label>Full name *</label>
                        <input v-model="invoiceForm.full_name" type="text" class="input" placeholder="John Doe" />
                      </div>
                      <div class="form-field">
                        <label>Email *</label>
                        <input v-model="invoiceForm.email" type="email" class="input" placeholder="john@example.com" />
                      </div>

                      <div v-if="invoiceForm.customer_type === 'business'" class="form-field">
                        <label>Company name *</label>
                        <input v-model="invoiceForm.company_name" type="text" class="input" placeholder="ACME Ltd." />
                      </div>
                      <div v-if="invoiceForm.customer_type === 'business'" class="form-field">
                        <label>Tax number</label>
                        <input v-model="invoiceForm.tax_number" type="text" class="input" placeholder="Optional" />
                      </div>

                      <div class="form-field">
                        <label>Country</label>
                        <input v-model="invoiceForm.country" type="text" class="input" placeholder="Hungary" />
                      </div>
                      <div class="form-field">
                        <label>City</label>
                        <input v-model="invoiceForm.city" type="text" class="input" placeholder="Budapest" />
                      </div>
                      <div class="form-field">
                        <label>Postal code</label>
                        <input v-model="invoiceForm.postal_code" type="text" class="input" placeholder="1111" />
                      </div>
                      <div class="form-field">
                        <label>Address line</label>
                        <input v-model="invoiceForm.address_line" type="text" class="input" placeholder="Street 1." />
                      </div>
                      <div class="form-field full">
                        <label>Note to hotel (optional)</label>
                        <textarea v-model="invoiceForm.note" class="textarea" rows="3" placeholder="Optional note to appear on invoice / request"></textarea>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="modal-actions">
                  <button type="button" class="btn-back" @click="closePaymentInvoiceModal">Cancel</button>
                  <button
                    type="button"
                    class="btn-confirm-booking"
                    :disabled="bookingLoading || !canSubmitBookingWithInvoice"
                    @click="createBooking"
                  >
                    {{ bookingLoading ? 'Creating booking...' : 'Send booking request' }}
                  </button>
                </div>
              </div>
            </div>
          </Transition>

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

  <!-- Fullscreen Image Carousel Modal -->
  <Transition name="fullscreen-modal">
    <div v-if="fullscreenGallery.open" class="fullscreen-gallery-overlay" @click.self="closeFullscreenGallery">
      <div class="fullscreen-gallery-container">
        <button class="fullscreen-close-btn" @click="closeFullscreenGallery" title="Close (ESC)">
          ×
        </button>
        <div class="fullscreen-gallery-content">
          <img 
            :src="fullscreenGalleryCurrentImage" 
            :alt="fullscreenGallery.roomName"
            class="fullscreen-gallery-image"
            @error="handleRoomImageError"
          />
          <button 
            v-if="fullscreenGallery.images.length > 1"
            @click.stop="previousFullscreenImage"
            class="fullscreen-nav-btn fullscreen-prev"
            :disabled="fullscreenGallery.currentIndex === 0"
          >
            ‹
          </button>
          <button 
            v-if="fullscreenGallery.images.length > 1"
            @click.stop="nextFullscreenImage"
            class="fullscreen-nav-btn fullscreen-next"
            :disabled="fullscreenGallery.currentIndex === fullscreenGallery.images.length - 1"
          >
            ›
          </button>
          <div v-if="fullscreenGallery.images.length > 1" class="fullscreen-gallery-info">
            <div class="fullscreen-image-counter">
              {{ fullscreenGallery.currentIndex + 1 }} / {{ fullscreenGallery.images.length }}
            </div>
            <div class="fullscreen-room-name">{{ fullscreenGallery.roomName }}</div>
          </div>
        </div>
        <!-- Fullscreen Thumbnails -->
        <div v-if="fullscreenGallery.images.length > 1" class="fullscreen-thumbnails">
          <div
            v-for="(img, idx) in fullscreenGallery.images"
            :key="idx"
            class="fullscreen-thumbnail"
            :class="{ 'active': fullscreenGallery.currentIndex === idx }"
            @click.stop="setFullscreenImageIndex(idx)"
          >
            <img 
              :src="img" 
              :alt="`Image ${idx + 1}`"
              @error="handleRoomImageError"
            />
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
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
const showPaymentInvoiceModal = ref(false)
const paymentMethod = ref('bank_transfer')
const invoiceForm = ref({
  customer_type: 'private',
  full_name: '',
  email: '',
  company_name: '',
  tax_number: '',
  country: '',
  city: '',
  postal_code: '',
  address_line: '',
  note: ''
})

const isAuthenticated = computed(() => authStore.state.isAuthenticated)

const invoiceAddressPreview = computed(() => {
  const parts = [
    invoiceForm.value.address_line,
    [invoiceForm.value.postal_code, invoiceForm.value.city].filter(Boolean).join(' '),
    invoiceForm.value.country
  ].filter(Boolean)
  return parts.length ? parts.join(', ') : '—'
})

const canSubmitBookingWithInvoice = computed(() => {
  if (!invoiceForm.value.full_name || !invoiceForm.value.email) return false
  if (invoiceForm.value.customer_type === 'business' && !invoiceForm.value.company_name) return false
  return true
})

const openPaymentInvoiceModal = () => {
  if (!isAuthenticated.value || selectedPlanIndex.value === null) return
  // prefill from logged-in user
  invoiceForm.value.full_name = invoiceForm.value.full_name || (authStore.state.user?.name || '')
  invoiceForm.value.email = invoiceForm.value.email || (authStore.state.user?.email || '')
  showPaymentInvoiceModal.value = true
}

const closePaymentInvoiceModal = () => {
  showPaymentInvoiceModal.value = false
}

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

  // Keyboard navigation for fullscreen gallery
  window.addEventListener('keydown', handleKeyPress)
})

// Keyboard navigation handler (defined outside onMounted so it can be referenced in onUnmounted)
const handleKeyPress = (e) => {
  if (!fullscreenGallery.value.open) return
  
  if (e.key === 'Escape') {
    closeFullscreenGallery()
  } else if (e.key === 'ArrowLeft') {
    e.preventDefault()
    previousFullscreenImage()
  } else if (e.key === 'ArrowRight') {
    e.preventDefault()
    nextFullscreenImage()
  }
}

onUnmounted(() => {
  // Cleanup: restore body scroll and remove event listeners
  document.body.style.overflow = ''
  window.removeEventListener('keydown', handleKeyPress)
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
  if (!hotel.value) return imageFallback
  
  // First priority: hotel cover_image
  if (hotel.value.cover_image) {
    const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000'
    const url = hotel.value.cover_image.startsWith('/storage/') 
      ? `${baseUrl}${hotel.value.cover_image}`
      : hotel.value.cover_image
    return url
  }
  
  // Second priority: first room's first image
  if (hotel.value.rooms && hotel.value.rooms.length > 0) {
    for (const room of hotel.value.rooms) {
      if (room.images && room.images.length > 0) {
        const imageUrl = room.images[0].url
        if (imageUrl) {
          const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000'
          return imageUrl.startsWith('/storage/') 
            ? `${baseUrl}${imageUrl}`
            : imageUrl
        }
      }
    }
  }
  
  return imageFallback
}

const handleImageError = (event) => {
  event.target.src = imageFallback
}

// Track current image index for each room gallery
const roomImageIndices = ref({})

// Fullscreen gallery state
const fullscreenGallery = ref({
  open: false,
  images: [],
  currentIndex: 0,
  roomId: null,
  roomName: ''
})

const fullscreenGalleryCurrentImage = computed(() => {
  if (!fullscreenGallery.value.open || fullscreenGallery.value.images.length === 0) return null
  return fullscreenGallery.value.images[fullscreenGallery.value.currentIndex]
})

const getRoomImages = (room) => {
  const images = []
  
  // Get images from room if available (from search results)
  if (room.images && Array.isArray(room.images) && room.images.length > 0) {
    room.images.forEach(img => {
      const imageUrl = typeof img === 'string' ? img : img.url
      if (imageUrl) {
        const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000'
        const fullUrl = imageUrl.startsWith('/storage/') 
          ? `${baseUrl}${imageUrl}`
          : imageUrl
        images.push(fullUrl)
      }
    })
  }
  
  // Try to find room in hotel.rooms for additional images
  if (hotel.value && hotel.value.rooms) {
    const hotelRoom = hotel.value.rooms.find(r => r.id === room.room_id || r.id === room.id)
    if (hotelRoom && hotelRoom.images && Array.isArray(hotelRoom.images) && hotelRoom.images.length > 0) {
      hotelRoom.images.forEach(img => {
        const imageUrl = typeof img === 'string' ? img : img.url
        if (imageUrl) {
          const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000'
          const fullUrl = imageUrl.startsWith('/storage/') 
            ? `${baseUrl}${imageUrl}`
            : imageUrl
          // Avoid duplicates
          if (!images.includes(fullUrl)) {
            images.push(fullUrl)
          }
        }
      })
    }
  }
  
  return images
}

const getRoomImage = (room) => {
  const images = getRoomImages(room)
  return images.length > 0 ? images[0] : null
}

const getRoomImageIndex = (roomId) => {
  return roomImageIndices.value[roomId] || 0
}

const setRoomImageIndex = (roomId, index) => {
  roomImageIndices.value[roomId] = index
}

const previousRoomImage = (roomId) => {
  const currentIndex = getRoomImageIndex(roomId)
  if (currentIndex > 0) {
    setRoomImageIndex(roomId, currentIndex - 1)
  }
}

const nextRoomImage = (roomId) => {
  // Find the room to get its images
  let roomImages = []
  if (searchResultsData.value && searchResultsData.value.hotel && searchResultsData.value.hotel.plans) {
    for (const plan of searchResultsData.value.hotel.plans) {
      const room = plan.rooms.find(r => (r.room_id === roomId) || (r.id === roomId))
      if (room) {
        roomImages = getRoomImages(room)
        break
      }
    }
  }
  
  const currentIndex = getRoomImageIndex(roomId)
  if (currentIndex < roomImages.length - 1) {
    setRoomImageIndex(roomId, currentIndex + 1)
  }
}

const handleRoomImageError = (event) => {
  event.target.style.display = 'none'
}

const openFullscreenGallery = (room) => {
  const images = getRoomImages(room)
  if (images.length === 0) return
  
  const roomId = room.room_id || room.id
  const currentIndex = getRoomImageIndex(roomId)
  
  fullscreenGallery.value = {
    open: true,
    images: images,
    currentIndex: currentIndex,
    roomId: roomId,
    roomName: room.name || 'Room'
  }
  
  // Prevent body scroll when modal is open
  document.body.style.overflow = 'hidden'
}

const closeFullscreenGallery = () => {
  fullscreenGallery.value.open = false
  // Restore body scroll
  document.body.style.overflow = ''
}

const previousFullscreenImage = () => {
  if (fullscreenGallery.value.currentIndex > 0) {
    fullscreenGallery.value.currentIndex--
    // Also update the room gallery index
    if (fullscreenGallery.value.roomId) {
      setRoomImageIndex(fullscreenGallery.value.roomId, fullscreenGallery.value.currentIndex)
    }
  }
}

const nextFullscreenImage = () => {
  if (fullscreenGallery.value.currentIndex < fullscreenGallery.value.images.length - 1) {
    fullscreenGallery.value.currentIndex++
    // Also update the room gallery index
    if (fullscreenGallery.value.roomId) {
      setRoomImageIndex(fullscreenGallery.value.roomId, fullscreenGallery.value.currentIndex)
    }
  }
}

const setFullscreenImageIndex = (index) => {
  fullscreenGallery.value.currentIndex = index
  // Also update the room gallery index
  if (fullscreenGallery.value.roomId) {
    setRoomImageIndex(fullscreenGallery.value.roomId, index)
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

    // Include payment method + invoice details (captured from modal)
    bookingData.payment_method = paymentMethod.value
    bookingData.invoice_details = { ...invoiceForm.value }

    await bookingService.createBooking(bookingData)

    // Clear search results
    sessionStorage.removeItem('searchResults')
    bookingSuccess.value = true
    showPaymentInvoiceModal.value = false

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
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.room-gallery {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.room-gallery-main {
  position: relative;
  width: 100%;
  height: 400px;
  border-radius: 12px;
  overflow: hidden;
  background: #e0e0e0;
  border: 2px solid #e0e0e0;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.room-gallery-main:hover {
  transform: scale(1.01);
}

.room-gallery-main-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.room-gallery-main:hover .room-gallery-main-image {
  transform: scale(1.05);
}

.gallery-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.9);
  border: 2px solid rgba(102, 126, 234, 0.3);
  color: #667eea;
  font-size: 1.5rem;
  font-weight: bold;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  z-index: 10;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.gallery-nav-btn:hover:not(:disabled) {
  background: white;
  border-color: #667eea;
  transform: translateY(-50%) scale(1.1);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.gallery-nav-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.gallery-prev {
  left: 1rem;
}

.gallery-next {
  right: 1rem;
}

.gallery-indicator {
  position: absolute;
  bottom: 1rem;
  right: 1rem;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  z-index: 10;
}

.room-gallery-thumbnails {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding: 0.5rem 0;
  scrollbar-width: thin;
  scrollbar-color: rgba(102, 126, 234, 0.4) rgba(0, 0, 0, 0.1);
}

.room-gallery-thumbnails::-webkit-scrollbar {
  height: 6px;
}

.room-gallery-thumbnails::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 3px;
}

.room-gallery-thumbnails::-webkit-scrollbar-thumb {
  background: rgba(102, 126, 234, 0.4);
  border-radius: 3px;
}

.room-gallery-thumbnails::-webkit-scrollbar-thumb:hover {
  background: rgba(102, 126, 234, 0.6);
}

.gallery-thumbnail {
  flex-shrink: 0;
  width: 80px;
  height: 80px;
  border-radius: 8px;
  overflow: hidden;
  border: 3px solid transparent;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #e0e0e0;
}

.gallery-thumbnail:hover {
  border-color: rgba(102, 126, 234, 0.5);
  transform: scale(1.05);
}

.gallery-thumbnail.active {
  border-color: #667eea;
  box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
}

.gallery-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.room-info {
  flex: 1;
}

.gallery-zoom-hint {
  position: absolute;
  bottom: 1rem;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
  z-index: 5;
}

.room-gallery-main:hover .gallery-zoom-hint {
  opacity: 1;
}

.zoom-icon {
  font-size: 1rem;
}

/* Fullscreen Gallery Modal */
.fullscreen-gallery-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.95);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
}

.fullscreen-gallery-container {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.fullscreen-close-btn {
  position: absolute;
  top: 2rem;
  right: 2rem;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.3);
  color: white;
  font-size: 2rem;
  font-weight: 300;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  z-index: 10001;
  line-height: 1;
}

.fullscreen-close-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.5);
  transform: scale(1.1);
}

.fullscreen-gallery-content {
  position: relative;
  width: 100%;
  height: calc(100% - 120px);
  display: flex;
  align-items: center;
  justify-content: center;
  max-width: 95vw;
  max-height: 95vh;
  margin: 0 auto;
  padding: 2rem;
}

.fullscreen-gallery-image {
  max-width: 100%;
  max-height: 100%;
  width: auto;
  height: auto;
  object-fit: contain;
  border-radius: 8px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  transition: opacity 0.3s ease;
  /* Maintain aspect ratio and show full resolution */
  image-rendering: -webkit-optimize-contrast;
  image-rendering: crisp-edges;
}

.fullscreen-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.15);
  border: 2px solid rgba(255, 255, 255, 0.3);
  color: white;
  font-size: 2rem;
  font-weight: bold;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  z-index: 10001;
  backdrop-filter: blur(10px);
}

.fullscreen-nav-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-50%) scale(1.1);
}

.fullscreen-nav-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.fullscreen-prev {
  left: 2rem;
}

.fullscreen-next {
  right: 2rem;
}

.fullscreen-gallery-info {
  position: absolute;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.7);
  padding: 1rem 2rem;
  border-radius: 30px;
  color: white;
  text-align: center;
  backdrop-filter: blur(10px);
  z-index: 10001;
}

.fullscreen-image-counter {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.fullscreen-room-name {
  font-size: 0.9rem;
  opacity: 0.9;
}

.fullscreen-thumbnails {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  gap: 0.75rem;
  padding: 1.5rem;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, transparent 100%);
  overflow-x: auto;
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
  z-index: 10001;
}

.fullscreen-thumbnails::-webkit-scrollbar {
  height: 6px;
}

.fullscreen-thumbnails::-webkit-scrollbar-track {
  background: transparent;
}

.fullscreen-thumbnails::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 3px;
}

.fullscreen-thumbnails::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}

.fullscreen-thumbnail {
  flex-shrink: 0;
  width: 100px;
  height: 100px;
  border-radius: 8px;
  overflow: hidden;
  border: 3px solid transparent;
  cursor: pointer;
  transition: all 0.2s ease;
  background: rgba(255, 255, 255, 0.1);
  opacity: 0.6;
}

.fullscreen-thumbnail:hover {
  opacity: 0.9;
  transform: scale(1.05);
}

.fullscreen-thumbnail.active {
  border-color: white;
  opacity: 1;
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
}

.fullscreen-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Fullscreen Modal Transitions */
.fullscreen-modal-enter-active,
.fullscreen-modal-leave-active {
  transition: opacity 0.3s ease;
}

.fullscreen-modal-enter-active .fullscreen-gallery-container,
.fullscreen-modal-leave-active .fullscreen-gallery-container {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.fullscreen-modal-enter-from,
.fullscreen-modal-leave-to {
  opacity: 0;
}

.fullscreen-modal-enter-from .fullscreen-gallery-container,
.fullscreen-modal-leave-to .fullscreen-gallery-container {
  transform: scale(0.95);
  opacity: 0;
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

  .room-gallery-main {
    height: 300px;
  }

  .gallery-nav-btn {
    width: 40px;
    height: 40px;
    font-size: 1.25rem;
  }

  .gallery-prev {
    left: 0.5rem;
  }

  .gallery-next {
    right: 0.5rem;
  }

  .gallery-thumbnail {
    width: 60px;
    height: 60px;
  }

  .fullscreen-gallery-content {
    height: calc(100% - 100px);
    padding: 1rem;
    max-width: 100vw;
    max-height: 90vh;
  }

  .fullscreen-gallery-image {
    max-width: 95vw;
    max-height: 85vh;
  }

  .fullscreen-nav-btn {
    width: 48px;
    height: 48px;
    font-size: 1.5rem;
  }

  .fullscreen-prev {
    left: 0.5rem;
  }

  .fullscreen-next {
    right: 0.5rem;
  }

  .fullscreen-close-btn {
    top: 1rem;
    right: 1rem;
    width: 40px;
    height: 40px;
    font-size: 1.5rem;
  }

  .fullscreen-thumbnail {
    width: 70px;
    height: 70px;
  }

  .fullscreen-gallery-info {
    bottom: 1rem;
    padding: 0.75rem 1.5rem;
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

/* Payment & Invoice modal (modern) */
.pay-invoice-overlay {
  position: fixed;
  inset: 0;
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
  background: rgba(10, 10, 10, 0.55);
  backdrop-filter: blur(14px);
}

.pay-invoice-overlay::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 20% 30%, rgba(102, 126, 234, 0.18) 0%, transparent 55%),
    radial-gradient(circle at 80% 70%, rgba(118, 75, 162, 0.16) 0%, transparent 55%);
  pointer-events: none;
}

.glass-card {
  position: relative;
  z-index: 1;
  background: rgba(255, 255, 255, 0.82);
  border: 1px solid rgba(102, 126, 234, 0.22);
  box-shadow:
    0 20px 60px rgba(0, 0, 0, 0.25),
    0 0 40px rgba(102, 126, 234, 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(18px);
}

.pay-invoice-modal {
  max-width: 980px;
  width: 92%;
  max-height: 90vh;
  overflow: auto;
  border-radius: 20px;
  padding: 0;
  display: flex;
  flex-direction: column;
}

.pay-invoice-modal .modal-header {
  padding: 2rem 2.5rem 1.5rem;
  border-bottom: 1px solid rgba(102, 126, 234, 0.12);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.pay-invoice-modal .modal-header h2 {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 800;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.pay-invoice-modal .btn-close-modal {
  background: rgba(102, 126, 234, 0.1);
  border: 1px solid rgba(102, 126, 234, 0.2);
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #667eea;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pay-invoice-modal .btn-close-modal:hover {
  background: rgba(102, 126, 234, 0.2);
  border-color: rgba(102, 126, 234, 0.4);
  transform: scale(1.1);
}

.pay-invoice-modal .modal-actions {
  padding: 1.5rem 2.5rem 2.5rem;
  border-top: 1px solid rgba(102, 126, 234, 0.12);
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.pay-invoice-body {
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
  padding: 2rem 2.5rem;
  flex: 1;
  overflow-y: auto;
}

.pill-tabs {
  display: inline-flex;
  gap: 0.5rem;
  background: #f3f4f6;
  padding: 0.35rem;
  border-radius: 999px;
  align-self: flex-start;
  border: 1px solid #e5e7eb;
}

.pill {
  border: none;
  background: transparent;
  padding: 0.55rem 0.9rem;
  border-radius: 999px;
  font-weight: 700;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pill.active {
  background: white;
  box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  color: #111827;
}

.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.card {
  background: rgba(255, 255, 255, 0.65);
  border: 1px solid rgba(102, 126, 234, 0.12);
  border-radius: 16px;
  padding: 1.75rem;
}

.card-title {
  font-size: 1rem;
  font-weight: 800;
  color: #111827;
  margin-bottom: 0.75rem;
}

.radio-card {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  background: rgba(255, 255, 255, 0.75);
  border: 2px solid rgba(102, 126, 234, 0.16);
  border-radius: 14px;
  padding: 1.25rem;
  cursor: pointer;
  transition: all 0.2s ease;
  backdrop-filter: blur(10px);
}

.radio-card:hover {
  border-color: #c7d2fe;
  box-shadow: 0 10px 24px rgba(102,126,234,0.12);
}

.radio-card input {
  transform: scale(1.15);
}

.radio-title {
  font-weight: 800;
  color: #111827;
}

.radio-sub {
  font-size: 0.9rem;
  color: #6b7280;
}

.preview {
  background: rgba(255, 255, 255, 0.75);
  border: 2px solid rgba(102, 126, 234, 0.16);
  border-radius: 14px;
  padding: 1.5rem;
  backdrop-filter: blur(10px);
}

.preview-row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.45rem 0;
  border-bottom: 1px dashed #e5e7eb;
  font-size: 0.95rem;
}
.preview-row:last-child { border-bottom: none; }
.preview-row span { color: #6b7280; }
.preview-row strong { color: #111827; text-align: right; }

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;
}

.form-field.full {
  grid-column: 1 / -1;
}

.form-field label {
  display: block;
  font-weight: 700;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.input, .textarea {
  width: 100%;
  border: 2px solid rgba(102, 126, 234, 0.14);
  background: rgba(255, 255, 255, 0.75);
  border-radius: 12px;
  padding: 1rem 1.25rem;
  font-size: 1rem;
  transition: all 0.2s ease;
  backdrop-filter: blur(10px);
}

.input:focus, .textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
}

@media (max-width: 900px) {
  .grid-2 { grid-template-columns: 1fr; }
  .form-grid { grid-template-columns: 1fr; }
}
</style>
