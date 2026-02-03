<template>
  <AdminLayout>
    <div class="company-info-page">
      <div class="page-header">
        <h1>💼 Cégadatok / Számlázási információk</h1>
        <p class="page-description">Itt állíthatja be a számlázáshoz szükséges cégadatokat minden szállodához.</p>
      </div>

      <!-- Hotel selector -->
      <div v-if="selectedHotel" class="hotel-selector-compact header-compact">
        <div class="hotel-compact-info">
          <div class="hotel-compact-icon">🏨</div>
          <div class="hotel-compact-details">
            <div class="hotel-compact-name">
              {{ selectedHotel.name || `Szálloda #${selectedHotel.id}` }}
            </div>
            <div class="hotel-compact-location">
              📍 {{ selectedHotel.location || 'Helyszín nincs megadva' }}
            </div>
          </div>
        </div>
        <button
          v-if="hotels.length > 1"
          @click="openHotelCarousel"
          class="hotel-change-btn"
          title="Szálloda váltása"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 4V10H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M23 20V14H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14L18.36 18.36A9 9 0 0 1 3.51 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Váltás</span>
        </button>
      </div>

      <!-- Hotel carousel overlay -->
      <Transition name="fade">
        <div
          v-if="hotels.length > 1 && showHotelCarousel"
          class="hotel-carousel-overlay"
          @click.self="closeHotelCarousel"
        >
          <div class="hotel-carousel-container-minimal">
            <div class="hotel-carousel-header-minimal">
              <h3 class="carousel-title-minimal">🏨 Szálloda kiválasztása</h3>
              <button @click="closeHotelCarousel" class="carousel-close-btn-minimal">×</button>
            </div>
            
            <div class="hotel-carousel-wrapper-minimal">
              <button
                @click="previousHotel"
                class="carousel-nav-btn-modern carousel-prev-modern"
                title="Előző szálloda"
              >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M15 18L9 12L15 6" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
              
              <div class="hotel-carousel-minimal">
                <div
                  class="hotel-card-carousel-minimal"
                  :style="{ transform: `translateX(-${currentHotelIndex * 100}%)` }"
                >
                  <div
                    v-for="hotel in hotels"
                    :key="hotel.id"
                    class="hotel-card-item-minimal"
                    @click="selectHotelFromCarousel(hotel.id)"
                    :class="{ selected: selectedHotelId === hotel.id }"
                  >
                    <div class="hotel-card-image-minimal">
                      <img
                        v-if="hotel.cover_image"
                        :src="getImageUrl(hotel.cover_image)"
                        :alt="hotel.name || 'Hotel'"
                        class="hotel-cover-image-minimal"
                        @error="handleImageError"
                      />
                      <div v-else class="hotel-image-placeholder-minimal">
                        <span class="hotel-icon-minimal">🏨</span>
                      </div>
                    </div>
                    <div class="hotel-card-content-minimal">
                      <h4 class="hotel-card-name-minimal">
                        {{ hotel.name || `Szálloda #${hotel.id}` }}
                      </h4>
                      <p class="hotel-card-location-minimal">
                        📍 {{ hotel.location || 'Helyszín nincs megadva' }}
                      </p>
                      <button class="hotel-select-btn-minimal">
                        {{ selectedHotelId === hotel.id ? '✓ Kiválasztva' : 'Kiválasztás' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              
              <button
                @click="nextHotel"
                class="carousel-nav-btn-modern carousel-next-modern"
                title="Következő szálloda"
              >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M9 18L15 12L9 6" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </div>
            
            <div class="carousel-indicators-minimal">
              <button
                v-for="(hotel, index) in hotels"
                :key="hotel.id"
                @click="goToHotel(index)"
                class="carousel-indicator-minimal"
                :class="{ active: currentHotelIndex === index }"
              ></button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Loading state -->
      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Adatok betöltése...</p>
      </div>

      <!-- Form -->
      <div v-else-if="selectedHotel" class="company-info-form">
        <form @submit.prevent="handleSubmit" class="form-container">
          <div class="form-section">
            <h3 class="section-title">Számlázási adatok</h3>
            <p class="section-description">Ezek a mezők kötelezőek a számlák kiállításához.</p>
            
            <div class="form-group">
              <label>Adószám <span class="required">*</span></label>
              <input 
                v-model="form.tax_number" 
                type="text" 
                placeholder="12345678 vagy HU12345678"
                required
                :class="{ 'input-error': formErrors.tax_number }"
                @blur="validateTaxNumber"
              />
              <div v-if="formErrors.tax_number" class="field-error">{{ formErrors.tax_number }}</div>
              <span class="field-hint">8 számjegyű magyar adószám vagy EU ÁFA szám (pl. HU12345678)</span>
            </div>

            <div class="form-group">
              <label>Bankszámlaszám <span class="required">*</span></label>
              <input 
                v-model="form.bank_account" 
                type="text" 
                placeholder="Adja meg a bankszámlaszámot" 
                required
              />
              <span class="field-hint">Kötelező a számlák kiállításához</span>
            </div>

            <div class="form-group">
              <label>Közösségi adószám (EU adószám) <span class="required">*</span></label>
              <input 
                v-model="form.eu_tax_number" 
                type="text" 
                placeholder="Adja meg a közösségi adószámot" 
                required
              />
              <span class="field-hint">Kötelező a számlák kiállításához</span>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Mentés...' : 'Mentés' }}
            </button>
            <button type="button" @click="resetForm" class="btn-secondary" :disabled="saving">
              Mégse
            </button>
          </div>
        </form>
      </div>

      <!-- No hotel selected -->
      <div v-else class="no-hotel-message">
        <p>Nincs kiválasztott szálloda.</p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import { adminService } from '../../services/adminService'
import { useAuthStore } from '../../stores/auth'
import { useBodyScrollLock } from '../../composables/useBodyScrollLock'

const authStore = useAuthStore()

const loading = ref(true)
const saving = ref(false)
const hotels = ref([])
const selectedHotelId = ref(null)
const currentHotelIndex = ref(0)
const showHotelCarousel = ref(false)

const selectedHotel = computed(() => {
  if (!selectedHotelId.value) return null
  return hotels.value.find(h => h.id === selectedHotelId.value)
})

const form = ref({
  tax_number: '',
  bank_account: '',
  eu_tax_number: ''
})

const formErrors = ref({
  tax_number: ''
})

useBodyScrollLock(showHotelCarousel)

const validateTaxNumber = () => {
  formErrors.value.tax_number = ''
  if (!form.value.tax_number?.trim()) {
    return
  }
  
  const taxNumber = form.value.tax_number.trim().replace(/\s/g, '')
  const hungarianTaxPattern = /^\d{8}$/
  const euVatPattern = /^[A-Z]{2}[A-Z0-9]{2,12}$/
  
  if (!hungarianTaxPattern.test(taxNumber) && !euVatPattern.test(taxNumber)) {
    formErrors.value.tax_number = 'Érvénytelen adószám formátum. Használjon 8 számjegyű magyar adószámot vagy EU ÁFA számot (pl. HU12345678)'
  }
}

const loadHotels = async () => {
  loading.value = true
  try {
    const data = await adminService.getHotels()
    // getHotels returns an array directly, not an object with hotels property
    hotels.value = Array.isArray(data) ? data : (data.hotels || [])
    
    // Filter to only show hotels owned by the current user
    // The API should already filter this for authenticated hotel admins, but let's be safe
    const currentUserId = authStore.state.user?.id
    if (currentUserId) {
      hotels.value = hotels.value.filter(hotel => {
        const hotelUserId = hotel.user_id || hotel.user?.id
        return hotelUserId && (hotelUserId === currentUserId || hotelUserId == currentUserId)
      })
    } else {
      hotels.value = []
    }
    
    // Auto-select first hotel if available
    if (hotels.value.length > 0 && !selectedHotelId.value) {
      selectedHotelId.value = hotels.value[0].id
      currentHotelIndex.value = 0
      await loadHotelBillingInfo()
    } else if (selectedHotelId.value) {
      // Update carousel index if hotel is already selected
      const index = hotels.value.findIndex(h => h.id === selectedHotelId.value)
      if (index >= 0) currentHotelIndex.value = index
    }
  } catch (err) {
    console.error('Failed to load hotels:', err)
    hotels.value = []
  } finally {
    loading.value = false
  }
}

const loadHotelBillingInfo = async () => {
  if (!selectedHotel.value || !selectedHotel.value.id) {
    console.warn('No hotel selected or hotel ID missing')
    return
  }
  
  loading.value = true
  try {
    console.log('Loading billing info for hotel ID:', selectedHotel.value.id)
    const data = await adminService.getHotelBillingInfo(selectedHotel.value.id)
    form.value = {
      tax_number: data.tax_number || '',
      bank_account: data.bank_account || '',
      eu_tax_number: data.eu_tax_number || ''
    }
  } catch (err) {
    console.error('Failed to load hotel billing info:', err)
    console.error('Error response:', err.response?.data)
    // Initialize with empty values
    form.value = {
      tax_number: '',
      bank_account: '',
      eu_tax_number: ''
    }
  } finally {
    loading.value = false
  }
}

const handleSubmit = async () => {
  validateTaxNumber()
  if (formErrors.value.tax_number) {
    return
  }
  
  if (!selectedHotel.value) return
  
  if (!selectedHotel.value || !selectedHotel.value.id) {
    alert('Nincs kiválasztott szálloda!')
    return
  }
  
  saving.value = true
  try {
    console.log('Updating hotel billing info for hotel ID:', selectedHotel.value.id)
    console.log('Form data:', form.value)
    
    await adminService.updateHotelBillingInfo(selectedHotel.value.id, {
      tax_number: form.value.tax_number,
      bank_account: form.value.bank_account,
      eu_tax_number: form.value.eu_tax_number
    })
    
    alert('Számlázási adatok sikeresen mentve!')
    
    // Trigger event to recheck billing data in AdminLayout
    window.dispatchEvent(new CustomEvent('hotel-billing-updated'))
  } catch (err) {
    console.error('Failed to save hotel billing info:', err)
    console.error('Error response:', err.response?.data)
    const errorMsg = err.response?.data?.error || err.response?.data?.message || err.message || 'Ismeretlen hiba'
    alert('Hiba történt a mentés során: ' + errorMsg)
  } finally {
    saving.value = false
  }
}

const resetForm = () => {
  loadHotelBillingInfo()
}

const openHotelCarousel = () => {
  // Set current index to selected hotel
  if (selectedHotelId.value) {
    const index = hotels.value.findIndex(h => h.id === selectedHotelId.value)
    if (index >= 0) currentHotelIndex.value = index
  }
  showHotelCarousel.value = true
}

const closeHotelCarousel = () => {
  showHotelCarousel.value = false
}

const selectHotelFromCarousel = async (hotelId) => {
  selectedHotelId.value = hotelId
  const index = hotels.value.findIndex(h => h.id === hotelId)
  if (index >= 0) currentHotelIndex.value = index
  closeHotelCarousel()
  await loadHotelBillingInfo()
}

const nextHotel = () => {
  if (hotels.value.length === 0) return
  // Loop: if at last hotel, go to first
  if (currentHotelIndex.value >= hotels.value.length - 1) {
    currentHotelIndex.value = 0
  } else {
    currentHotelIndex.value++
  }
}

const previousHotel = () => {
  if (hotels.value.length === 0) return
  // Loop: if at first hotel, go to last
  if (currentHotelIndex.value === 0) {
    currentHotelIndex.value = hotels.value.length - 1
  } else {
    currentHotelIndex.value--
  }
}

const goToHotel = (index) => {
  currentHotelIndex.value = index
}

const getImageUrl = (imagePath) => {
  if (!imagePath) return null
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath
  }
  return `${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'}/storage/${imagePath}`
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
}

onMounted(async () => {
  await loadHotels()
})
</script>

<style scoped>
.company-info-page {
  padding: 2rem;
  max-width: 900px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.page-description {
  color: #7f8c8d;
  font-size: 1rem;
}

.hotel-selector-compact {
  background: white;
  border-radius: 12px;
  padding: 1rem 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.hotel-compact-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.hotel-compact-icon {
  font-size: 2rem;
}

.hotel-compact-details {
  display: flex;
  flex-direction: column;
}

.hotel-compact-name {
  font-weight: 600;
  color: #2c3e50;
  font-size: 1.1rem;
}

.hotel-compact-location {
  color: #7f8c8d;
  font-size: 0.9rem;
}

.hotel-change-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.hotel-change-btn:hover {
  background: #5568d3;
}

.hotel-carousel-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 9999;
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.hotel-carousel-container-minimal {
  width: 100%;
  max-width: 500px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.hotel-carousel-header-minimal {
  padding: 1rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.carousel-title-minimal {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
}

.carousel-close-btn-minimal {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  font-size: 1.5rem;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  font-weight: 600;
}

.carousel-close-btn-minimal:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

.hotel-carousel-wrapper-minimal {
  position: relative;
  display: flex;
  align-items: center;
  padding: 1.5rem 0;
  background: #f8f9fa;
  min-height: 280px;
}

.hotel-carousel-minimal {
  flex: 1;
  overflow: hidden;
  position: relative;
}

.hotel-card-carousel-minimal {
  display: flex;
  transition: transform 0.4s ease;
}

.hotel-card-item-minimal {
  min-width: 100%;
  padding: 0 1.5rem;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.hotel-card-item-minimal.selected .hotel-select-btn-minimal {
  background: #10b981;
}

.hotel-card-image-minimal {
  width: 100%;
  max-width: 360px;
  height: 180px;
  border-radius: 12px;
  overflow: hidden;
  background: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
}

.hotel-cover-image-minimal {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hotel-image-placeholder-minimal {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  color: #adb5bd;
  font-size: 2.5rem;
}

.hotel-card-content-minimal {
  text-align: center;
}

.hotel-card-name-minimal {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 0.25rem;
  color: #2c3e50;
}

.hotel-card-location-minimal {
  font-size: 0.9rem;
  color: #6c757d;
  margin: 0 0 0.75rem;
}

.hotel-select-btn-minimal {
  padding: 0.6rem 1.4rem;
  border-radius: 999px;
  border: none;
  background: #667eea;
  color: #fff;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.hotel-select-btn-minimal:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.35);
}

.carousel-nav-btn-modern {
  width: 44px;
  height: 44px;
  border-radius: 999px;
  border: none;
  background: rgba(15, 23, 42, 0.85);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.4);
}

.carousel-nav-btn-modern:hover {
  transform: translateY(-1px);
  background: rgba(15, 23, 42, 0.95);
}

.carousel-prev-modern {
  margin-left: 1rem;
}

.carousel-next-modern {
  margin-right: 1rem;
}

.carousel-indicators-minimal {
  display: flex;
  justify-content: center;
  gap: 0.4rem;
  padding: 0.75rem 1rem 1.25rem;
  background: #ffffff;
}

.carousel-indicator-minimal {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  border: none;
  background: #d1d5db;
  cursor: pointer;
  transition: all 0.2s ease;
}

.carousel-indicator-minimal.active {
  width: 18px;
  background: #4f46e5;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
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

.company-info-form {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.form-section {
  margin-bottom: 2rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.section-description {
  color: #7f8c8d;
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.form-group input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.2s;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.input-error {
  border-color: #ef4444 !important;
  background: #fef2f2;
}

.field-error {
  color: #ef4444;
  font-size: 0.85rem;
  margin-top: 0.25rem;
  font-weight: 500;
}

.field-hint {
  display: block;
  color: #7f8c8d;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.required {
  color: #ef4444;
  font-weight: 700;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e0e0e0;
}

.btn-primary {
  padding: 0.75rem 2rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover:not(:disabled) {
  background: #5568d3;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  padding: 0.75rem 2rem;
  background: white;
  color: #667eea;
  border: 2px solid #667eea;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover:not(:disabled) {
  background: #f8f9ff;
}

.no-hotel-message {
  text-align: center;
  padding: 4rem;
  color: #7f8c8d;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
