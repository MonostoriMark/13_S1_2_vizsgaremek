<template>
  <div class="search-page">
    <!-- Home Button -->
    <router-link to="/" class="home-button" v-if="!isAuthenticated">
      <span class="home-icon">🏠</span>
      <span class="home-text">Kezdőlap</span>
    </router-link>
    
    <!-- Clean Search Section -->
    <div class="search-container">
      <div class="search-header">
        <h1 style="margin-top: 50px;">Találja meg az ideális szálláshelyet</h1>
        <p>Fedezzen fel fantasztikus szállodákat, apartmanokat és villákat</p>
      </div>

      <!-- Main Search Bar -->
      <div class="search-card">
          <form @submit.prevent="handleSearch" class="search-form-booking">
            <div class="form-row-booking">
              <div class="form-group-booking location-input-wrapper">
                <span class="input-icon">🛏️</span>
                <input
                  id="city"
                  v-model="searchParams.city"
                  type="text"
                  required
                  placeholder="Hová utazik?"
                  class="booking-input"
                  list="locations-list"
                  autocomplete="off"
                  @focus="showLocationDropdown = true"
                  @blur="handleLocationBlur"
                  @input="filterLocations"
                />
                <datalist id="locations-list">
                  <option v-for="location in filteredLocations" :key="location" :value="location" />
                </datalist>
                <div v-if="showLocationDropdown && filteredLocations.length > 0" class="location-dropdown">
                  <div
                    v-for="location in filteredLocations"
                    :key="location"
                    class="location-option"
                    @mousedown.prevent="selectLocation(location)"
                  >
                    {{ location }}
                  </div>
                </div>
              </div>
              <div class="form-group-booking">
                <span class="input-icon">📅</span>
                <div class="date-input-wrapper">
                  <input
                    id="startDate"
                    v-model="searchParams.startDate"
                    type="date"
                    required
                    :min="minDate"
                    class="booking-input date-input"
                  />
                  <span class="date-separator">–</span>
                  <input
                    id="endDate"
                    v-model="searchParams.endDate"
                    type="date"
                    required
                    :min="searchParams.startDate || minDate"
                    class="booking-input date-input"
                  />
                </div>
              </div>
              <div class="form-group-booking">
                <span class="input-icon">👤</span>
                <input
                  id="guests"
                  v-model.number="searchParams.guests"
                  type="number"
                  required
                  min="1"
                  :placeholder="`${searchParams.guests || 1} vendég${(searchParams.guests || 1) > 1 ? '' : ''}`"
                  class="booking-input"
                />
                <span class="input-arrow">▼</span>
              </div>
              <button type="submit" class="btn-search-booking" :disabled="loading">
                {{ loading ? 'Keresés...' : 'Keresés' }}
              </button>
            </div>
          </form>

          <!-- Filter bar above recommendations (hide after a search is started) -->
          <div v-if="!hasSearched && (smartRecommendations.length > 0 || recommendationsLoading)" class="filters-row-booking" style="margin-top: 1.5rem;">
            <div class="filter-group-booking">
              <label for="typeFilter">Típus</label>
              <select id="typeFilter" v-model="filters.type" class="filter-select">
                <option value="">Bármelyik</option>
                <option value="hotel">Szálloda</option>
                <option value="apartment">Apartman</option>
                <option value="villa">Villa</option>
              </select>
            </div>
            <div class="filter-group-booking">
              <label>Ár / éjszaka</label>
              <div class="price-filter-booking">
                <input
                  v-model.number="filters.minPrice"
                  type="number"
                  min="0"
                  placeholder="Min"
                  class="filter-input"
                />
                <span class="price-separator">–</span>
                <input
                  v-model.number="filters.maxPrice"
                  type="number"
                  min="0"
                  placeholder="Max"
                  class="filter-input"
                />
              </div>
            </div>
            <div class="filter-group-booking services-filter-booking">
              <label>Címkék</label>
              <div class="services-chips-booking">
                <button
                  v-for="tag in availableTagsForFilter"
                  :key="tag.id"
                  type="button"
                  class="chip-booking"
                  :class="{ active: filters.tags.includes(tag.id) }"
                  @click="toggleTagFilter(tag.id)"
                >
                  {{ tag.name }}
                </button>
              </div>
            </div>
          </div>

          <!-- Simple Random Recommendations (hide after a search is started) -->
          <div v-if="!hasSearched && recommendationsLoading" class="recommendation-panel">
            <div class="recommendations-loading">
              <div class="loading-spinner"></div>
              <p>Ajánlott szálláshelyek betöltése...</p>
            </div>
          </div>
          
          <div v-else-if="!hasSearched && smartRecommendations.length > 0" class="recommendation-panel">
            <div class="recommendations-content">
              <div class="recommendations-header">
                <h3 class="recommendations-title">
                  <span class="recommendations-icon">✨</span>
                  Ajánlott szálláshelyek
                </h3>
              </div>
              
              <div class="recommendations-grid">
                <div
                  v-for="hotel in filteredRecommendations"
                  :key="hotel.id"
                  class="recommendation-card"
                  @click="viewRecommendedHotel(hotel)"
                >
                  <div class="recommendation-image-container">
                    <img
                      :src="getRecommendationImage(hotel)"
                      :alt="hotel.name"
                      @error="handleImageError"
                      class="recommendation-image"
                    />
                    <div v-if="hotel.starRating" class="recommendation-rating">
                      {{ '★'.repeat(hotel.starRating) }}
                    </div>
                  </div>
                  
                  <div class="recommendation-content">
                    <h4 class="recommendation-name">{{ hotel.name }}</h4>
                    <p class="recommendation-location">📍 {{ hotel.location }}</p>
                    
                    <div v-if="hotel.tags && hotel.tags.length > 0" class="recommendation-tags">
                      <span
                        v-for="tag in hotel.tags.slice(0, 3)"
                        :key="tag.id"
                        class="recommendation-tag"
                      >
                        {{ tag.name }}
                      </span>
                    </div>
                    
                    <div class="recommendation-footer">
                      <div class="recommendation-price">
                        <span v-if="hotel.price_per_night" class="price-amount">
                          {{ hotel.price_per_night }} €
                          <span class="price-period">/éjszaka</span>
                        </span>
                        <span v-else class="price-on-request">Ár igény esetén</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      <!-- Content Area -->
      <div class="search-content">
      <!-- Loading State -->
      <div v-if="loading && !hasSearched" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Ajánlott szállodák betöltése...</p>
      </div>

      <!-- Error State -->
      <div v-if="error" class="error-message">{{ error }}</div>

      <!-- Search Results -->
      <div v-if="hasSearched && filteredResults.length > 0" ref="resultsSection" class="results-section">
        <h2 class="section-title">Keresési eredmények ({{ filteredResults.length }})</h2>
        <div class="hotels-grid">
          <div
            v-for="hotel in filteredResults"
            :key="hotel.hotel_id"
            class="hotel-card"
            @click="viewHotel(hotel.hotel_id)"
          >
            <div class="hotel-image-container">
              <img
                :src="getHotelImage(hotel)"
                :alt="hotel.name || hotel.location"
                @error="handleImageError"
                class="hotel-image"
              />
              <div v-if="hotel.starRating" class="star-badge">
                {{ '★'.repeat(hotel.starRating) }}
              </div>
            </div>
            <div class="hotel-content">
              <div class="hotel-header">
                <h3 class="hotel-name">{{ hotel.name || hotel.location }}</h3>
                <span v-if="hotel.type" class="hotel-type">{{ hotel.type }}</span>
              </div>
              <p v-if="hotel.description" class="hotel-description">
                {{ truncateText(hotel.description, 100) }}
              </p>
              <div v-if="hotel.tags && hotel.tags.length > 0" class="hotel-tags">
                <span
                  v-for="(tag, idx) in hotel.tags.slice(0, 6)"
                  :key="idx"
                  class="tag"
                >
                  {{ typeof tag === 'object' ? tag.name : tag }}
                </span>
              </div>
              <div v-if="getHotelServices(hotel).length" class="hotel-services">
                <span
                  v-for="service in getHotelServices(hotel)"
                  :key="service"
                  class="service-pill"
                >
                  {{ service }}
                </span>
              </div>
              <div class="hotel-footer">
                <div class="price-info">
                  <span class="price-label">Ár kezdve</span>
                  <span class="price-amount">
                    {{ getStartingPrice(hotel) }} €
                    <span class="price-period">/éjszaka</span>
                  </span>
                </div>
                <button class="btn-view" @click.stop="viewHotel(hotel.hotel_id)">
                  Részletek
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>


      <!-- Empty State (No Search Results) -->
      <div v-if="hasSearched && filteredResults.length === 0 && !loading" class="empty-state">
        <div class="empty-icon">🏨</div>
        <h2>Nem található szálloda</h2>
        <p>Nem találtunk szállodát, amely megfelelne a keresési feltételeinek.</p>
        <div class="empty-actions">
          <button @click="clearSearch" class="btn-primary">Próbáljon más dátumokat</button>
          <button @click="showRecommended" class="btn-secondary">Ajánlott szállodák megtekintése</button>
        </div>
        <div v-if="recommendedHotels.length > 0" class="alternative-hotels">
          <h3>Ez is érdekelheti:</h3>
          <div class="hotels-grid compact">
            <div
              v-for="hotel in recommendedHotels.slice(0, 3)"
              :key="hotel.id"
              class="hotel-card compact"
              @click="viewRecommendedHotel(hotel)"
            >
              <div class="hotel-image-container">
                <img
                  :src="getRecommendedHotelImage(hotel)"
                  :alt="hotel.name || hotel.location"
                  @error="handleImageError"
                  class="hotel-image"
                />
              </div>
              <div class="hotel-content">
                <h3 class="hotel-name">{{ hotel.name || hotel.location }}</h3>
                <div class="price-info">
                  <span class="price-amount" v-if="hotel.startingPrice">
                    {{ hotel.startingPrice }} €<span class="price-period">/éjszaka</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { searchService } from '../services/searchService'
import { hotelService } from '../services/hotelService'
import { tagService } from '../services/tagService'
import { recommendationService } from '../services/recommendationService'
import { hotelDataService } from '../services/hotelDataService'
import { useAuthStore } from '../stores/auth'
import { getHotelCoverImage } from '../utils/imageUtils'

const router = useRouter()
const authStore = useAuthStore()
const isAuthenticated = computed(() => authStore.state.isAuthenticated)

const searchParams = ref({
  city: '',
  startDate: '',
  endDate: '',
  guests: 1
})

const results = ref([])
const recommendedHotels = ref([]) // Legacy recommended hotels (for initial state)
const error = ref('')
const loading = ref(false)
const hasSearched = ref(false)
const isSticky = ref(false)

// Booking.com-style recommendation system state
const smartRecommendations = ref([]) // New intelligent recommendations (all filtered results)
const displayedRecommendationsCount = ref(9) // Number of recommendations to display
const recommendationsLoading = ref(false)
const showRecommendations = ref(false)
const recommendationParams = ref({
  city: '',
  check_in: '',
  check_out: '',
  guests: 1
})
let recommendationDebounceTimer = null

// All hotels data (loaded once, used for client-side filtering)
const allHotelsData = ref(null)
const hotelsDataLoading = ref(false)
const hotelsDataError = ref(null)
const recommendedSectionExpanded = ref(false)
const recommendedFilters = ref({
  minPrice: null,
  maxPrice: null,
  minRating: '',
  location: '',
  minRooms: ''
})
const recommendedSortBy = ref('default')
const recommendedCurrentPage = ref(1)
const recommendedItemsPerPage = ref(9) // 3 columns × 3 rows

// Locations from database
const allLocations = ref([])
const filteredLocations = ref([])
const showLocationDropdown = ref(false)

// Tags from database
const allTags = ref([])
const tagUsage = ref({ hotel_tags: [], room_tags: [] })

const minDate = computed(() => {
  return new Date().toISOString().split('T')[0]
})

// Simple client-side filters
const filters = ref({
  type: '',
  minPrice: null,
  maxPrice: null,
  services: [],
  tags: []
})

const popularServices = ref([])

// Image fallback
const imageFallback = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'

const getHotelImage = (hotel) => {
  // Use cover_image from hotel or fallback to room images
  return getHotelCoverImage(hotel, imageFallback)
}

const getRecommendedHotelImage = (hotel) => {
  // First check coverImage (from hotelService.getRecommendedHotels)
  if (hotel.coverImage) {
    return hotel.coverImage
  }
  // Then check cover_image field
  return getHotelCoverImage(hotel, imageFallback)
}

const handleImageError = (event) => {
  event.target.src = imageFallback
}

const getStartingPrice = (hotel) => {
  if (hotel.plans && hotel.plans.length > 0) {
    const prices = hotel.plans.map(plan => plan.total_price)
    return Math.min(...prices)
  }
  return 'N/A'
}

const getHotelServices = (hotel) => {
  // Try to infer services from hotel data – adjust as needed to match backend
  const services = []
  if (hotel.services && Array.isArray(hotel.services)) {
    services.push(...hotel.services.map(s => s.name || s.title || s))
  }
  if (hotel.availableServices && Array.isArray(hotel.availableServices)) {
    services.push(...hotel.availableServices.map(s => s.name || s.title || s))
  }
  // Deduplicate and limit for display
  const unique = [...new Set(services)].filter(Boolean)
  return unique.slice(0, 4)
}

const availableTagsForFilter = computed(() => {
  return allTags.value
})

const truncateText = (text, maxLength) => {
  if (!text) return ''
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text
}

const scrollToSearch = () => {
  const searchWrapper = document.querySelector('.hero-search-wrapper')
  if (searchWrapper) {
    searchWrapper.scrollIntoView({ behavior: 'smooth', block: 'center' })
    // Focus on first input
    setTimeout(() => {
      const firstInput = document.getElementById('city')
      if (firstInput) firstInput.focus()
    }, 500)
  }
}

const resultsSection = ref(null)

const handleSearch = async () => {
  error.value = ''
  loading.value = true
  hasSearched.value = true
  showRecommendations.value = false // Hide recommendations when user searches

  try {
    const data = await searchService.search(
      searchParams.value.city,
      searchParams.value.startDate,
      searchParams.value.endDate,
      searchParams.value.guests
    )
    results.value = data
    
    // Scroll to results after loading
    await nextTick()
    if (resultsSection.value && results.value.length > 0) {
      resultsSection.value.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Keresés sikertelen'
    results.value = []
  } finally {
    loading.value = false
  }
}

// Load all hotels data once (cached on frontend)
const loadAllHotelsData = async () => {
  if (allHotelsData.value) {
    return // Already loaded
  }

  hotelsDataLoading.value = true
  try {
    const data = await hotelDataService.getAllHotelsWithRooms()
    allHotelsData.value = data.hotels || []
  } catch (error) {
    console.error('Failed to load hotels data:', error)
    hotelsDataError.value = error
    allHotelsData.value = []
  } finally {
    hotelsDataLoading.value = false
  }
}

// Simple: Load 10 random hotels on page load
const loadRecommendations = async () => {
  if (smartRecommendations.value.length > 0) return // Already loaded
  
  recommendationsLoading.value = true
  
  try {
    const response = await recommendationService.getRecommendations({})
    // Handle response - it should have a 'hotels' array
    if (response && Array.isArray(response.hotels)) {
      smartRecommendations.value = response.hotels
    } else if (Array.isArray(response)) {
      // Fallback: if response is directly an array
      smartRecommendations.value = response
    } else {
      smartRecommendations.value = []
    }
  } catch (error) {
    console.error('Failed to load recommendations:', error)
    smartRecommendations.value = []
  } finally {
    recommendationsLoading.value = false
  }
}

// Client-side filtering and ranking function
const filterAndRankHotels = (hotels, city, checkIn, checkOut, guests) => {
  const now = new Date()
  
  return hotels
    .filter(hotel => {
      // Filter by city
      if (city) {
        const cityLower = city.toLowerCase()
        const locationMatch = hotel.location?.toLowerCase().includes(cityLower)
        const nameMatch = hotel.name?.toLowerCase().includes(cityLower)
        if (!locationMatch && !nameMatch) return false
      }
      
      // Check availability if dates provided
      if (checkIn && checkOut) {
        const hasAvailableRooms = checkHotelAvailability(hotel, checkIn, checkOut, guests)
        if (!hasAvailableRooms) return false
      }
      
      return true
    })
    .map(hotel => {
      // Calculate score for ranking
      const score = calculateHotelScore(hotel, checkIn, checkOut, guests)
      return { ...hotel, score }
    })
    .sort((a, b) => b.score - a.score) // Sort by score descending
    .map(({ score, ...hotel }) => {
      // Format for display
      const availability = getAvailabilityInfo(hotel, checkIn, checkOut, guests)
      return {
        id: hotel.id,
        name: hotel.name,
        location: hotel.location,
        type: hotel.type,
        starRating: hotel.starRating,
        description: hotel.description,
        cover_image: hotel.cover_image,
        price_per_night: hotel.min_price,
        availability_status: availability.status,
        rooms_available: availability.roomsAvailable,
        urgency_message: availability.urgency,
        tags: hotel.tags,
        services: hotel.services,
        search_params: {
          city: hotel.location,
          check_in: checkIn,
          check_out: checkOut,
          guests: guests
        }
      }
    })
}

// Check if hotel has available rooms for date range
const checkHotelAvailability = (hotel, checkIn, checkOut, guests) => {
  if (!checkIn || !checkOut) return true
  
  const startDate = new Date(checkIn)
  const endDate = new Date(checkOut)
  
  // Get rooms that match capacity
  const suitableRooms = hotel.rooms.filter(room => room.capacity >= guests)
  
  if (suitableRooms.length === 0) return false
  
  // Check each room's availability
  for (const room of suitableRooms) {
    const isAvailable = !room.booked_in_bookings.some(booking => {
      const bookingStart = new Date(booking.startDate)
      const bookingEnd = new Date(booking.endDate)
      // Check for overlap
      return startDate < bookingEnd && endDate > bookingStart
    })
    
    if (isAvailable) {
      return true // At least one room is available
    }
  }
  
  return false
}

// Get availability information
const getAvailabilityInfo = (hotel, checkIn, checkOut, guests) => {
  if (!checkIn || !checkOut) {
    return {
      status: 'available',
      roomsAvailable: hotel.total_rooms,
      urgency: null
    }
  }
  
  const startDate = new Date(checkIn)
  const endDate = new Date(checkOut)
  const suitableRooms = hotel.rooms.filter(room => room.capacity >= guests)
  
  let availableCount = 0
  for (const room of suitableRooms) {
    const isAvailable = !room.booked_in_bookings.some(booking => {
      const bookingStart = new Date(booking.startDate)
      const bookingEnd = new Date(booking.endDate)
      return startDate < bookingEnd && endDate > bookingStart
    })
    if (isAvailable) availableCount++
  }
  
  let status = 'available'
  let urgency = null
  
  if (availableCount === 0) {
    status = 'unavailable'
  } else if (availableCount <= 2) {
    status = 'urgent'
    urgency = `Csak ${availableCount} szoba maradt!`
  } else if (availableCount <= 5) {
    status = 'limited'
    urgency = 'Korlátozott elérhetőség'
  }
  
  return { status, roomsAvailable: availableCount, urgency }
}

// Calculate hotel score for ranking
const calculateHotelScore = (hotel, checkIn, checkOut, guests) => {
  let score = 0
  
  // 1. Availability (40%)
  const availability = getAvailabilityInfo(hotel, checkIn, checkOut, guests)
  if (availability.roomsAvailable > 0) {
    const availabilityRatio = availability.roomsAvailable / Math.max(hotel.total_rooms, 1)
    score += Math.min(availabilityRatio * 100, 100) * 0.40
  }
  
  // 2. Price (25%) - lower is better
  if (hotel.min_price > 0) {
    // Normalize price (assuming max price of 500)
    const normalizedPrice = Math.max(0, 1 - (hotel.min_price / 500))
    score += normalizedPrice * 100 * 0.25
  }
  
  // 3. Rating (15%)
  score += ((hotel.starRating || 0) / 5) * 100 * 0.15
  
  // 4. Popularity (10%)
  const popularityScore = Math.min((hotel.booking_count || 0) / 10, 1) * 100
  score += popularityScore * 0.10
  
  // 5. Location (10%) - neutral for now
  score += 10
  
  return score
}

// Computed property for filtered recommendations
const filteredRecommendations = computed(() => {
  let filtered = [...smartRecommendations.value]
  
  // Filter by type
  if (filters.value.type) {
    filtered = filtered.filter(hotel => hotel.type && hotel.type.toLowerCase() === filters.value.type.toLowerCase())
  }
  
  // Filter by price
  if (filters.value.minPrice !== null && filters.value.minPrice !== '') {
    filtered = filtered.filter(hotel => {
      const price = hotel.price_per_night || 0
      return price >= filters.value.minPrice
    })
  }
  if (filters.value.maxPrice !== null && filters.value.maxPrice !== '' && filters.value.maxPrice > 0) {
    filtered = filtered.filter(hotel => {
      const price = hotel.price_per_night || 0
      return price <= filters.value.maxPrice
    })
  }
  
  // Filter by tags
  if (filters.value.tags && filters.value.tags.length > 0) {
    filtered = filtered.filter(hotel => {
      if (!hotel.tags || !Array.isArray(hotel.tags)) return false
      const hotelTagIds = hotel.tags.map(tag => tag.id || tag)
      return filters.value.tags.some(tagId => hotelTagIds.includes(tagId))
    })
  }
  
  return filtered
})

// Load more recommendations
const loadMoreRecommendations = () => {
  displayedRecommendationsCount.value += 9
}

// Removed debounced loading - just load once on mount

const getRecommendationImage = (hotel) => {
  if (hotel.cover_image) {
    const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000'
    return hotel.cover_image.startsWith('/storage/') 
      ? `${baseUrl}${hotel.cover_image}`
      : hotel.cover_image
  }
  return imageFallback
}

const getAvailabilityText = (hotel) => {
  switch (hotel.availability_status) {
    case 'available':
      return 'Elérhető'
    case 'limited':
      return `${hotel.rooms_available} szoba elérhető`
    case 'urgent':
      return `Csak ${hotel.rooms_available} szoba!`
    default:
      return 'Ellenőrizze az elérhetőséget'
  }
}

// Track last params to prevent duplicate calls
const lastRecommendationParams = ref('')

// Removed updateRecommendationParams - no longer needed

// Watch for location selection (only trigger if city changes, not on every keystroke)
watch(() => searchParams.value.city, (newCity, oldCity) => {
  // Removed - no longer needed
}, { debounce: 1000 })

const filteredResults = computed(() => {
  if (!results.value.length) return []

  return results.value.filter(hotel => {
    const price = parseFloat(getStartingPrice(hotel))
    if (!Number.isNaN(filters.value.minPrice) && filters.value.minPrice !== null) {
      if (!Number.isNaN(price) && price < filters.value.minPrice) return false
    }
    if (!Number.isNaN(filters.value.maxPrice) && filters.value.maxPrice !== null && filters.value.maxPrice > 0) {
      if (!Number.isNaN(price) && price > filters.value.maxPrice) return false
    }

    if (filters.value.type && hotel.type && hotel.type.toLowerCase() !== filters.value.type) {
      return false
    }

    if (filters.value.services.length) {
      const hotelServices = getHotelServices(hotel).map(s => s.toLowerCase())
      const required = filters.value.services.map(s => s.toLowerCase())
      const hasAll = required.every(s => hotelServices.includes(s))
      if (!hasAll) return false
    }

    // Tag filter: hotel must have at least one of the selected tags
    if (filters.value.tags.length > 0) {
      const hotelTagIds = hotel.tags ? hotel.tags.map(t => typeof t === 'object' ? t.id : t) : []
      const hasAnyTag = filters.value.tags.some(tagId => hotelTagIds.includes(tagId))
      if (!hasAnyTag) return false
    }

    return true
  })
})

const sortOptions = [
  { value: 'default', label: 'Alapértelmezett' },
  { value: 'price-asc', label: 'Ár: alacsony → magas' },
  { value: 'price-desc', label: 'Ár: magas → alacsony' },
  { value: 'rating-desc', label: 'Értékelés: magas → alacsony' },
  { value: 'rooms-desc', label: 'Szobák száma: több → kevesebb' },
  { value: 'name-asc', label: 'Név: A → Z' }
]

const filteredAndSortedRecommendedHotels = computed(() => {
  let filtered = [...recommendedHotels.value]

  // Apply filters
  if (recommendedFilters.value.minPrice !== null && recommendedFilters.value.minPrice !== '') {
    filtered = filtered.filter(hotel => {
      const price = hotel.startingPrice || 0
      return price >= recommendedFilters.value.minPrice
    })
  }

  if (recommendedFilters.value.maxPrice !== null && recommendedFilters.value.maxPrice !== '') {
    filtered = filtered.filter(hotel => {
      const price = hotel.startingPrice || 0
      return price <= recommendedFilters.value.maxPrice
    })
  }

  if (recommendedFilters.value.minRating) {
    const minRating = parseInt(recommendedFilters.value.minRating)
    filtered = filtered.filter(hotel => {
      const rating = hotel.starRating || 0
      return rating >= minRating
    })
  }

  if (recommendedFilters.value.location) {
    const locationLower = recommendedFilters.value.location.toLowerCase()
    filtered = filtered.filter(hotel => {
      const hotelLocation = (hotel.location || '').toLowerCase()
      const hotelName = (hotel.name || '').toLowerCase()
      return hotelLocation.includes(locationLower) || hotelName.includes(locationLower)
    })
  }

  if (recommendedFilters.value.minRooms) {
    const minRooms = parseInt(recommendedFilters.value.minRooms)
    filtered = filtered.filter(hotel => {
      const roomCount = hotel.roomCount || 0
      return roomCount >= minRooms
    })
  }

  // Apply sorting
  const sorted = [...filtered]
  switch (recommendedSortBy.value) {
    case 'price-asc':
      sorted.sort((a, b) => (a.startingPrice || 0) - (b.startingPrice || 0))
      break
    case 'price-desc':
      sorted.sort((a, b) => (b.startingPrice || 0) - (a.startingPrice || 0))
      break
    case 'rating-desc':
      sorted.sort((a, b) => (b.starRating || 0) - (a.starRating || 0))
      break
    case 'rooms-desc':
      sorted.sort((a, b) => (b.roomCount || 0) - (a.roomCount || 0))
      break
    case 'name-asc':
      sorted.sort((a, b) => {
        const nameA = (a.name || a.location || '').toLowerCase()
        const nameB = (b.name || b.location || '').toLowerCase()
        return nameA.localeCompare(nameB)
      })
      break
    default:
      // Keep original order
      break
  }

  return sorted
})

const paginatedRecommendedHotels = computed(() => {
  const start = (recommendedCurrentPage.value - 1) * recommendedItemsPerPage.value
  const end = start + recommendedItemsPerPage.value
  return filteredAndSortedRecommendedHotels.value.slice(start, end)
})

const totalRecommendedPages = computed(() => {
  return Math.ceil(filteredAndSortedRecommendedHotels.value.length / recommendedItemsPerPage.value)
})

const hasMoreRecommendedPages = computed(() => {
  return recommendedCurrentPage.value < totalRecommendedPages.value
})

const getVisiblePages = computed(() => {
  const total = totalRecommendedPages.value
  const current = recommendedCurrentPage.value
  const pages = []
  
  if (total <= 7) {
    // Show all pages if 7 or fewer
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    // Always show first page
    pages.push(1)
    
    if (current <= 3) {
      // Near the start
      for (let i = 2; i <= 4; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 2) {
      // Near the end
      pages.push('...')
      for (let i = total - 3; i <= total; i++) {
        pages.push(i)
      }
    } else {
      // In the middle
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(total)
    }
  }
  
  return pages
})

const goToRecommendedPage = (page) => {
  if (page >= 1 && page <= totalRecommendedPages.value) {
    recommendedCurrentPage.value = page
    // Scroll to top of hotels grid
    setTimeout(() => {
      const gridElement = document.querySelector('.hotels-grid.recommended')
      if (gridElement) {
        gridElement.scrollIntoView({ behavior: 'smooth', block: 'start' })
      }
    }, 100)
  }
}

const loadMoreRecommendedHotels = () => {
  if (hasMoreRecommendedPages.value) {
    recommendedCurrentPage.value++
  }
}

// Reset to page 1 when filters or sort changes
watch([recommendedFilters, recommendedSortBy], () => {
  recommendedCurrentPage.value = 1
}, { deep: true })

const toggleRecommendedSection = () => {
  recommendedSectionExpanded.value = !recommendedSectionExpanded.value
}

const resetRecommendedFilters = () => {
  recommendedFilters.value = {
    minPrice: null,
    maxPrice: null,
    minRating: '',
    location: '',
    minRooms: ''
  }
  recommendedSortBy.value = 'default'
  recommendedCurrentPage.value = 1
}

const viewHotel = (hotelId) => {
  // Find the hotel in search results
  const hotel = results.value.find(h => h.hotel_id === hotelId)
  if (hotel) {
    // Store search results and params for the hotel detail page
    sessionStorage.setItem('searchResults', JSON.stringify({
      hotel,
      searchParams: searchParams.value
    }))
  }
  router.push(`/hotel/${hotelId}`)
}

// This is the OLD function for legacy recommended hotels - keep it for backward compatibility
const viewRecommendedHotelLegacy = (hotel) => {
  router.push(`/hotel/${hotel.id}`)
}

// NEW function for smart recommendations - properly passes search params
const viewRecommendedHotel = (hotel) => {
  // Always store current search params (from searchParams, not hotel.search_params)
  // This ensures we use the actual user input, not potentially stale data
  const searchResults = {
    hotel: {
      hotel_id: hotel.id,
      name: hotel.name,
      location: hotel.location
    },
    searchParams: {
      city: searchParams.value.city || hotel.search_params?.city || '',
      startDate: searchParams.value.startDate || hotel.search_params?.check_in || '',
      endDate: searchParams.value.endDate || hotel.search_params?.check_out || '',
      guests: searchParams.value.guests || hotel.search_params?.guests || 1
    }
  }
  
  // Store in sessionStorage for hotel detail page
  sessionStorage.setItem('searchResults', JSON.stringify(searchResults))
  
  router.push(`/hotel/${hotel.id}`)
}

const clearSearch = () => {
  hasSearched.value = false
  results.value = []
  searchParams.value.city = ''
}

const showRecommended = () => {
  clearSearch()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const toggleServiceFilter = (service) => {
  const idx = filters.value.services.indexOf(service)
  if (idx === -1) {
    filters.value.services.push(service)
  } else {
    filters.value.services.splice(idx, 1)
  }
}

const toggleTagFilter = (tagId) => {
  const idx = filters.value.tags.indexOf(tagId)
  if (idx === -1) {
    filters.value.tags.push(tagId)
  } else {
    filters.value.tags.splice(idx, 1)
  }
}

const loadTags = async () => {
  try {
    const [tagsData, usageData] = await Promise.all([
      tagService.getAllTags(),
      tagService.getTagUsage()
    ])
    allTags.value = tagsData
    tagUsage.value = usageData
    
    // Update popular services from tags (first 5 tags as popular services)
    popularServices.value = tagsData.slice(0, 5).map(t => t.name)
  } catch (err) {
    console.error('Failed to load tags:', err)
    // Fallback to hardcoded services if tags fail
    popularServices.value = ['Free Wi-Fi', 'Breakfast included', 'Pool', 'Parking', 'Airport shuttle']
  }
}

const loadLocations = async () => {
  try {
    const locations = await searchService.getLocations()
    allLocations.value = locations
    filteredLocations.value = locations
  } catch (err) {
    console.error('Failed to load locations:', err)
    allLocations.value = []
    filteredLocations.value = []
  }
}

const filterLocations = () => {
  const query = searchParams.value.city.toLowerCase().trim()
  if (!query) {
    filteredLocations.value = allLocations.value
  } else {
    filteredLocations.value = allLocations.value.filter(location =>
      location.toLowerCase().includes(query)
    )
  }
  showLocationDropdown.value = true
}

const selectLocation = (location) => {
  searchParams.value.city = location
  showLocationDropdown.value = false
  filteredLocations.value = []
  // Update recommendations when location is selected
  updateRecommendationParams()
}

const handleLocationBlur = () => {
  // Delay hiding dropdown to allow click events to fire
  setTimeout(() => {
    showLocationDropdown.value = false
  }, 200)
}

const loadRecommendedHotels = async () => {
  try {
    loading.value = true
    const hotels = await hotelService.getRecommendedHotels()
    recommendedHotels.value = hotels.filter(h => h.roomCount > 0) // Only show hotels with rooms
  } catch (err) {
    console.error('Failed to load recommended hotels:', err)
    recommendedHotels.value = []
  } finally {
    loading.value = false
  }
}

// Sticky form handler
const handleScroll = () => {
  isSticky.value = window.scrollY > 100
}

onMounted(async () => {
  await loadTags()
  await loadLocations()
  // Removed loadRecommendedHotels() - it was making individual room requests for all hotels
  loadRecommendations() // Load 10 random hotels via optimized endpoint
  window.addEventListener('scroll', handleScroll)
  
  // Set default dates (today and tomorrow)
  const today = new Date()
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)
  
  searchParams.value.startDate = today.toISOString().split('T')[0]
  searchParams.value.endDate = tomorrow.toISOString().split('T')[0]
  
  // Load 10 random hotels on page load
  loadRecommendations()
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
.search-page {
  min-height: 100vh;
  height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 0;
  margin: 0;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow-y: auto;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Home Button */
.home-button {
  position: absolute;
  top: 1.5rem;
  left: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: white;
  color: #667eea;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
  z-index: 100;
}

.home-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  color: #764ba2;
}

.home-icon {
  font-size: 1.1rem;
}

.home-text {
  display: none;
}

@media (min-width: 480px) {
  .home-text {
    display: inline;
  }
}

/* Search Container */
.search-container {
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
  padding: 2rem;
  padding-top: 5rem;
}

.search-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.search-header h1 {
  font-size: 2.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.search-header p {
  font-size: 1.1rem;
  color: #6b7280;
}

/* Search Card */
.search-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  margin-bottom: 2rem;
}

.search-form-booking {
  width: 100%;
}

.search-form-booking {
  width: 100%;
}

.form-row-booking {
  display: grid;
  grid-template-columns: 2fr 1.5fr 1.2fr auto;
  gap: 0;
  align-items: stretch;
  border-radius: 8px;
  overflow: hidden;
  width: 100%;
  border: 1px solid #e5e7eb;
}

.form-group-booking {
  position: relative;
  display: flex;
  align-items: center;
  background: #f9fafb;
  border-right: 1px solid #e5e7eb;
  padding: 0 1rem;
}

.location-input-wrapper {
  position: relative;
}

.location-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  max-height: 300px;
  overflow-y: auto;
  z-index: 1000;
  margin-top: 4px;
}

.location-option {
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
  color: #1f2937;
  font-size: 0.95rem;
}

.location-option:hover {
  background-color: #f3f4f6;
}

.location-option:first-child {
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
}

.location-option:last-child {
  border-bottom-left-radius: 8px;
  border-bottom-right-radius: 8px;
}

.form-group-booking:last-of-type {
  border-right: none;
}

.input-icon {
  font-size: 1.2rem;
  margin-right: 0.75rem;
  color: #6b7280;
  flex-shrink: 0;
}

.booking-input {
  flex: 1;
  padding: 0.875rem 0;
  border: none;
  font-size: 0.95rem;
  color: #1f2937;
  background: transparent;
  outline: none;
  width: 100%;
}

.booking-input::placeholder {
  color: #9ca3af;
}

.date-input-wrapper {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
}

.date-input {
  flex: 1;
  padding: 1rem 0;
  border: none;
  font-size: 1rem;
  color: #1f2937;
  background: transparent;
  outline: none;
}

.date-separator {
  color: #9ca3af;
  font-weight: 500;
}

.input-arrow {
  color: #9ca3af;
  font-size: 0.75rem;
  margin-left: 0.5rem;
  flex-shrink: 0;
}

.btn-search-booking {
  padding: 1rem 2.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  min-width: 140px;
}

.btn-search-booking:hover:not(:disabled) {
  background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-search-booking:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Filters row */
.filters-row-booking {
  display: grid;
  grid-template-columns: 180px 260px minmax(0, 1fr);
  gap: 1.5rem;
  margin-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
  padding-top: 1.5rem;
}

/* Content Area */
.search-content {
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
  padding: 0 2rem 2rem 2rem;
}

.filter-group-booking {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group-booking label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #4b5563;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.filter-select,
.filter-input {
  border-radius: 6px;
  border: 1px solid #d1d5db;
  padding: 0.6rem 0.75rem;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  background: white;
  color: #1f2937;
}

.filter-select:focus,
.filter-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.price-filter-booking {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.price-separator {
  color: #6b7280;
  font-size: 0.85rem;
}

.services-filter-booking {
  overflow: hidden;
}

.services-chips-booking {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.chip-booking {
  border-radius: 20px;
  border: 1px solid #d1d5db;
  padding: 0.4rem 1rem;
  background: white;
  color: #4b5563;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.chip-booking.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: #667eea;
  color: white;
}

.chip-booking:hover {
  border-color: #667eea;
  background: #f3f4f6;
}

.chip-booking.active:hover {
  background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}

/* Search Page */
.search-page {
  width: 100%;
  overflow-x: hidden;
}

/* Content Area */
.search-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  width: 100%;
}

.section-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.section-subtitle {
  color: #7f8c8d;
  margin-bottom: 2rem;
}

/* Hotels Grid */
.hotels-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
}

.hotels-grid.recommended {
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}

.hotels-grid.recommended {
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}

.hotels-grid.compact {
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

/* Hotel Card */
.hotel-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  cursor: pointer;
}

.hotel-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.hotel-card.compact {
  border-radius: 12px;
}

.hotel-image-container {
  position: relative;
  width: 100%;
  height: 240px;
  overflow: hidden;
  background: #e0e0e0;
}

.hotel-card.compact .hotel-image-container {
  height: 180px;
}

.hotel-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.hotel-card:hover .hotel-image {
  transform: scale(1.05);
}

.star-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(255, 255, 255, 0.95);
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  color: #f39c12;
  font-size: 0.9rem;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.hotel-content {
  padding: 1.5rem;
}

.hotel-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 0.75rem;
}

.hotel-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
  flex: 1;
}

.hotel-type {
  font-size: 0.85rem;
  color: #7f8c8d;
  text-transform: capitalize;
  padding: 0.25rem 0.75rem;
  background: #f0f0f0;
  border-radius: 12px;
  margin-left: 0.5rem;
}

.hotel-description {
  color: #7f8c8d;
  font-size: 0.95rem;
  line-height: 1.5;
  margin-bottom: 1rem;
}

.hotel-tags {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.tag {
  font-size: 0.8rem;
  padding: 0.25rem 0.75rem;
  background: #e8f4f8;
  color: #667eea;
  border-radius: 12px;
  font-weight: 500;
}

.hotel-services {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  margin-bottom: 0.5rem;
}

.service-pill {
  font-size: 0.75rem;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  background: #eef2ff;
  color: #4338ca;
  border: 1px solid rgba(129, 140, 248, 0.6);
}

.hotel-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #f0f0f0;
}

.price-info {
  display: flex;
  flex-direction: column;
}

.price-label {
  font-size: 0.75rem;
  color: #7f8c8d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.25rem;
}

.price-amount {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
}

.price-period {
  font-size: 0.9rem;
  font-weight: 400;
  color: #7f8c8d;
}

.btn-view {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.btn-view:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
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

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
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
}

.empty-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-bottom: 3rem;
}

.btn-primary {
  padding: 0.875rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  padding: 0.875rem 2rem;
  background: white;
  color: #667eea;
  border: 2px solid #667eea;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: #f8f9ff;
}

.alternative-hotels {
  margin-top: 3rem;
  padding-top: 3rem;
  border-top: 1px solid #e0e0e0;
}

.alternative-hotels h3 {
  font-size: 1.25rem;
  color: #2c3e50;
  margin-bottom: 1.5rem;
}

.error-message {
  background: #fee;
  color: #c33;
  padding: 1rem;
  border-radius: 8px;
  margin: 1rem 0;
  border: 1px solid #fcc;
}

/* Results Section */
.results-section,
.recommended-section {
  margin-top: 3rem;
}

.section-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.section-subtitle {
  font-size: 1rem;
  color: #6b7280;
  margin-bottom: 2rem;
}

/* Advanced Recommended Section */
.advanced-recommended-section {
  margin-top: 3rem;
  background: white;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.recommended-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.header-content {
  flex: 1;
}

.toggle-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.toggle-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.toggle-btn svg {
  transition: transform 0.3s ease;
}

.toggle-btn.expanded svg {
  transform: rotate(180deg);
}

.recommended-controls {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 16px;
  border: 1px solid #e9ecef;
}

.filters-section,
.sort-section {
  margin-bottom: 1.5rem;
}

.filters-section:last-child,
.sort-section:last-child {
  margin-bottom: 0;
}

.filters-title,
.sort-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 1rem;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group label {
  font-size: 0.9rem;
  font-weight: 500;
  color: #495057;
}

.price-range {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.price-input,
.filter-input,
.filter-select {
  padding: 0.625rem 0.875rem;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.9rem;
  transition: border-color 0.2s;
}

.price-input {
  flex: 1;
  min-width: 80px;
}

.filter-input:focus,
.filter-select:focus,
.price-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-reset-filters {
  padding: 0.625rem 1.25rem;
  background: #6c757d;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-reset-filters:hover {
  background: #5a6268;
}

.sort-options {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.sort-btn {
  padding: 0.625rem 1.25rem;
  background: white;
  border: 2px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  color: #495057;
  cursor: pointer;
  transition: all 0.2s;
}

.sort-btn:hover {
  border-color: #667eea;
  color: #667eea;
}

.sort-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: #667eea;
  color: white;
}

.hotel-card.enhanced {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hotel-card.enhanced:hover {
  transform: translateY(-8px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.room-count-badge {
  position: absolute;
  bottom: 10px;
  right: 10px;
  background: rgba(102, 126, 234, 0.9);
  color: white;
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  backdrop-filter: blur(10px);
}

.no-results {
  text-align: center;
  padding: 3rem;
  color: #6c757d;
  font-size: 1.1rem;
}

/* Slide down animation */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
  max-height: 1000px;
  overflow: hidden;
}

.slide-down-enter-from,
.slide-down-leave-to {
  max-height: 0;
  opacity: 0;
  margin-bottom: 0;
  padding-top: 0;
  padding-bottom: 0;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .search-container {
    padding: 1.5rem;
    padding-top: 4rem;
  }

  .search-content {
    padding: 0 1.5rem 1.5rem 1.5rem;
  }

  .filters-row {
    grid-template-columns: 1fr 1fr;
  }

  .services-filter {
    grid-column: 1 / -1;
  }

  .form-row {
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }
  
  .form-group-button {
    grid-column: 1 / -1;
  }
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }

  .search-content {
    padding: 1rem;
  }

  .hotels-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .hotels-grid.recommended {
    grid-template-columns: 1fr;
  }

  .pagination-controls {
    flex-direction: column;
    gap: 1rem;
  }

  .pagination-pages {
    flex-wrap: wrap;
    justify-content: center;
  }

  .pagination-controls {
    flex-direction: column;
    gap: 1rem;
  }

  .pagination-pages {
    flex-wrap: wrap;
    justify-content: center;
  }

  .hotel-footer {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .btn-view {
    width: 100%;
  }

  .empty-actions {
    flex-direction: column;
  }

  .btn-primary,
  .btn-secondary {
    width: 100%;
  }

  .filters-row {
    grid-template-columns: 1fr;
  }
}@media (max-width: 480px) {
  .hotel-image-container {
    height: 200px;
  }  .section-title {
    font-size: 1.5rem;
  }
}

/* Booking.com-style Recommendation Panel */
.recommendation-panel {
  margin-top: 2rem;
  padding: 2rem;
  background: #f8f9fa;
  border-radius: 16px;
  border: 1px solid #e9ecef;
}

.recommendations-loading {
  padding: 2rem 0;
}

.recommendation-skeletons {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.recommendation-skeleton {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.skeleton-image {
  width: 100%;
  height: 180px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s ease-in-out infinite;
}

.skeleton-content {
  padding: 1.5rem;
}

.skeleton-line {
  height: 12px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s ease-in-out infinite;
  border-radius: 6px;
  margin-bottom: 0.75rem;
}

.skeleton-title {
  width: 70%;
  height: 16px;
}

.skeleton-subtitle {
  width: 50%;
  height: 14px;
}

.skeleton-price {
  width: 40%;
  height: 18px;
  margin-top: 1rem;
}

@keyframes skeleton-loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.recommendations-content {
  width: 100%;
}

.recommendations-header {
  margin-bottom: 2rem;
}

.recommendations-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.recommendations-icon {
  font-size: 1.75rem;
}

.recommendations-subtitle {
  font-size: 1rem;
  font-weight: 400;
  color: #6b7280;
}

.recommendations-description {
  color: #6b7280;
  font-size: 0.95rem;
}

.recommendations-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.recommendation-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  cursor: pointer;
}

.recommendation-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.recommendation-image-container {
  position: relative;
  width: 100%;
  height: 200px;
  overflow: hidden;
  background: #e0e0e0;
}

.recommendation-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.recommendation-card:hover .recommendation-image {
  transform: scale(1.05);
}

.recommendation-urgency {
  position: absolute;
  top: 10px;
  left: 10px;
  background: rgba(220, 38, 38, 0.95);
  color: white;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  backdrop-filter: blur(10px);
}

.recommendation-rating {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(255, 255, 255, 0.95);
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  color: #f39c12;
  font-size: 0.9rem;
  font-weight: 600;
  backdrop-filter: blur(10px);
}

.recommendation-content {
  padding: 1.5rem;
}

.recommendation-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
  line-height: 1.3;
}

.recommendation-location {
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 1rem;
}

.recommendation-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.recommendation-tag {
  padding: 0.25rem 0.75rem;
  background: #f3f4f6;
  color: #4b5563;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
}

.recommendation-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.availability-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
}

.availability-badge.available {
  background: #d1fae5;
  color: #065f46;
}

.availability-badge.limited {
  background: #fef3c7;
  color: #92400e;
}

.availability-badge.urgent {
  background: #fee2e2;
  color: #991b1b;
}

.recommendation-price {
  text-align: right;
}

.recommendation-price .price-amount {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

.recommendation-price .price-period {
  font-size: 0.85rem;
  font-weight: 400;
  color: #6b7280;
}

.recommendation-price .price-on-request {
  font-size: 0.9rem;
  color: #6b7280;
  font-style: italic;
}

.recommendations-empty {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
}

@media (max-width: 768px) {
  .recommendation-panel {
    padding: 1.5rem;
  }

  .recommendations-grid {
    grid-template-columns: 1fr;
  }

  .recommendation-skeletons {
    grid-template-columns: 1fr;
  }
}

/* Load More Button */
.load-more-container {
  display: flex;
  justify-content: center;
  margin-top: 2rem;
  padding: 1rem 0;
}

.btn-load-more {
  padding: 0.875rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.btn-load-more:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
}

.btn-load-more:active {
  transform: translateY(0);
}
</style>