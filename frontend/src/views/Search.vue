<template>
  <div class="search-page">
    <!-- Home Button -->
    <router-link to="/" class="home-button" v-if="!isAuthenticated">
      <span class="home-icon">🏠</span>
      <span class="home-text">Home</span>
    </router-link>
    
    <!-- Clean Search Section -->
    <div class="search-container">
      <div class="search-header">
        <h1 style="margin-top: 50px;">Find Your Perfect Stay</h1>
        <p>Discover amazing hotels, apartments, and villas</p>
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
                  placeholder="Where are you going?"
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
                  :placeholder="`${searchParams.guests || 1} guest${(searchParams.guests || 1) > 1 ? 's' : ''}`"
                  class="booking-input"
                />
                <span class="input-arrow">▼</span>
              </div>
              <button type="submit" class="btn-search-booking" :disabled="loading">
                {{ loading ? 'Searching...' : 'Search' }}
              </button>
            </div>
          </form>

          <!-- Filter bar below search -->
          <div class="filters-row-booking">
            <div class="filter-group-booking">
              <label for="typeFilter">Type</label>
              <select id="typeFilter" v-model="filters.type" class="filter-select">
                <option value="">Any</option>
                <option value="hotel">Hotel</option>
                <option value="apartment">Apartment</option>
                <option value="villa">Villa</option>
              </select>
            </div>
            <div class="filter-group-booking">
              <label>Price / night</label>
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
              <label>Tags</label>
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
        </div>

      <!-- Content Area -->
      <div class="search-content">
      <!-- Loading State -->
      <div v-if="loading && !hasSearched" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading recommended hotels...</p>
      </div>

      <!-- Error State -->
      <div v-if="error" class="error-message">{{ error }}</div>

      <!-- Search Results -->
      <div v-if="hasSearched && filteredResults.length > 0" class="results-section">
        <h2 class="section-title">Search Results ({{ filteredResults.length }})</h2>
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
                  <span class="price-label">Starting from</span>
                  <span class="price-amount">
                    {{ getStartingPrice(hotel) }} €
                    <span class="price-period">/night</span>
                  </span>
                </div>
                <button class="btn-view" @click.stop="viewHotel(hotel.hotel_id)">
                  View Details
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recommended Hotels (Initial State) -->
      <div v-if="!hasSearched && recommendedHotels.length > 0" class="recommended-section">
        <h2 class="section-title">Recommended Hotels</h2>
        <p class="section-subtitle">Discover amazing places to stay</p>
        <div class="hotels-grid">
          <div
            v-for="hotel in recommendedHotels"
            :key="hotel.id"
            class="hotel-card"
            @click="viewRecommendedHotel(hotel)"
          >
            <div class="hotel-image-container">
              <img
                :src="getRecommendedHotelImage(hotel)"
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
              <div class="hotel-footer">
                <div class="price-info">
                  <span class="price-label">Starting from</span>
                  <span class="price-amount" v-if="hotel.startingPrice">
                    {{ hotel.startingPrice }} €
                    <span class="price-period">/night</span>
                  </span>
                  <span class="price-amount" v-else>Price on request</span>
                </div>
                <button class="btn-view" @click.stop="viewRecommendedHotel(hotel)">
                  View Hotel
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State (No Search Results) -->
      <div v-if="hasSearched && filteredResults.length === 0 && !loading" class="empty-state">
        <div class="empty-icon">🏨</div>
        <h2>No hotels found</h2>
        <p>We couldn't find any hotels matching your search criteria.</p>
        <div class="empty-actions">
          <button @click="clearSearch" class="btn-primary">Try Different Dates</button>
          <button @click="showRecommended" class="btn-secondary">View Recommended Hotels</button>
        </div>
        <div v-if="recommendedHotels.length > 0" class="alternative-hotels">
          <h3>You might also like:</h3>
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
                    {{ hotel.startingPrice }} €<span class="price-period">/night</span>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { searchService } from '../services/searchService'
import { hotelService } from '../services/hotelService'
import { tagService } from '../services/tagService'
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
const recommendedHotels = ref([])
const error = ref('')
const loading = ref(false)
const hasSearched = ref(false)
const isSticky = ref(false)

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

const viewRecommendedHotel = (hotel) => {
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
  loadRecommendedHotels()
  window.addEventListener('scroll', handleScroll)
  
  // Set default dates (today and tomorrow)
  const today = new Date()
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)
  
  searchParams.value.startDate = today.toISOString().split('T')[0]
  searchParams.value.endDate = tomorrow.toISOString().split('T')[0]
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
</style>