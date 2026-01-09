<template>
  <div class="search-page">
    <!-- Sticky Search Form -->
    <div class="search-form-container" :class="{ 'is-sticky': isSticky }">
      <div class="search-form-wrapper">
        <form @submit.prevent="handleSearch" class="search-form">
          <div class="form-row">
            <div class="form-group">
              <label for="city">Where to?</label>
              <input
                id="city"
                v-model="searchParams.city"
                type="text"
                required
                placeholder="City or destination"
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
                placeholder="1"
              />
            </div>
            <div class="form-group form-group-button">
              <button type="submit" class="btn-search" :disabled="loading">
                {{ loading ? 'Searching...' : 'Search' }}
              </button>
            </div>
          </div>
        </form>
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
      <div v-if="hasSearched && results.length > 0" class="results-section">
        <h2 class="section-title">Search Results ({{ results.length }})</h2>
        <div class="hotels-grid">
          <div
            v-for="hotel in results"
            :key="hotel.hotel_id"
            class="hotel-card"
            @click="viewHotel(hotel.hotel_id)"
          >
            <div class="hotel-image-container">
              <img
                :src="getHotelImage(hotel)"
                :alt="hotel.location"
                @error="handleImageError"
                class="hotel-image"
              />
              <div v-if="hotel.starRating" class="star-badge">
                {{ '★'.repeat(hotel.starRating) }}
              </div>
            </div>
            <div class="hotel-content">
              <div class="hotel-header">
                <h3 class="hotel-name">{{ hotel.location }}</h3>
                <span v-if="hotel.type" class="hotel-type">{{ hotel.type }}</span>
              </div>
              <p v-if="hotel.description" class="hotel-description">
                {{ truncateText(hotel.description, 100) }}
              </p>
              <div v-if="hotel.tags && hotel.tags.length > 0" class="hotel-tags">
                <span v-for="tag in hotel.tags.slice(0, 3)" :key="tag" class="tag">{{ tag }}</span>
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
                :alt="hotel.location"
                @error="handleImageError"
                class="hotel-image"
              />
              <div v-if="hotel.starRating" class="star-badge">
                {{ '★'.repeat(hotel.starRating) }}
              </div>
            </div>
            <div class="hotel-content">
              <div class="hotel-header">
                <h3 class="hotel-name">{{ hotel.location }}</h3>
                <span v-if="hotel.type" class="hotel-type">{{ hotel.type }}</span>
              </div>
              <p v-if="hotel.description" class="hotel-description">
                {{ truncateText(hotel.description, 100) }}
              </p>
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
      <div v-if="hasSearched && results.length === 0 && !loading" class="empty-state">
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
                  :alt="hotel.location"
                  @error="handleImageError"
                  class="hotel-image"
                />
              </div>
              <div class="hotel-content">
                <h3 class="hotel-name">{{ hotel.location }}</h3>
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
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { searchService } from '../services/searchService'
import { hotelService } from '../services/hotelService'
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
const recommendedHotels = ref([])
const error = ref('')
const loading = ref(false)
const hasSearched = ref(false)
const isSticky = ref(false)

const minDate = computed(() => {
  return new Date().toISOString().split('T')[0]
})

// Image fallback
const imageFallback = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'

const getHotelImage = (hotel) => {
  // For search results, we don't have direct image access
  // In a real implementation, you'd need to fetch images from rooms
  return imageFallback
}

const getRecommendedHotelImage = (hotel) => {
  if (hotel.coverImage) {
    // URL should already be properly formatted from hotelService
    return hotel.coverImage
  }
  return imageFallback
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

const truncateText = (text, maxLength) => {
  if (!text) return ''
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text
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

onMounted(() => {
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
  background: #f8f9fa;
}

/* Sticky Search Form */
.search-form-container {
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  z-index: 100;
}

.search-form-container.is-sticky {
  position: sticky;
  top: 70px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.search-form-wrapper {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem 2rem;
}

.search-form {
  width: 100%;
}

.form-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 100px 140px;
  gap: 1rem;
  align-items: end;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-group input {
  padding: 0.875rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: white;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group-button {
  align-items: stretch;
}

.btn-search {
  padding: 0.875rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.btn-search:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-search:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Content Area */
.search-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
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
  margin-top: 2rem;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .form-row {
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }
  
  .form-group-button {
    grid-column: 1 / -1;
  }
}

@media (max-width: 768px) {
  .search-form-wrapper {
    padding: 1rem;
  }

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
}

@media (max-width: 480px) {
  .hotel-image-container {
    height: 200px;
  }

  .section-title {
    font-size: 1.5rem;
  }
}
</style>