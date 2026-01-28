<template>
  <AdminLayout>
    <div class="services-page">
      <div class="page-header">
        <h1>Szolgáltatások kezelése</h1>
        <button @click="openCreateModal" class="btn-primary">
          <span>➕</span> Szolgáltatás hozzáadása
        </button>
      </div>

      <!-- Compact hotel selector (like bookings supervision) -->
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

      <!-- If no hotel is yet selected (initial load), we simply hide the selector;
           the first hotel will be auto-selected in script once data arrives. -->

      <!-- Minimal hotel carousel overlay (shared pattern with bookings) -->
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
                        {{ selectedHotelId === hotel.id ? '✓ Kiválasztva' : 'Kiválasztás →' }}
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

      <div v-if="selectedHotel">
        <DataTable
          :data="services"
          :columns="columns"
          :loading="loading"
          search-placeholder="Szolgáltatások keresése..."
          empty-message="Nem található szolgáltatás"
          :search-fields="['name', 'description']"
          :on-edit="handleEdit"
          :on-delete="handleDelete"
        >
          <template #cell-price="{ value }">
            <span v-if="value">€{{ parseFloat(value).toFixed(2) }}</span>
            <span v-else class="text-muted">Ingyenes</span>
          </template>
          <template #actions="{ row }">
            <button @click="handleEdit(row)" class="btn-icon btn-edit" title="Szerkesztés">✏️</button>
            <button @click="handleDelete(row)" class="btn-icon btn-delete" title="Törlés">🗑️</button>
          </template>
        </DataTable>
      </div>

      <!-- Create/Edit Modal -->
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal-content">
            <div class="modal-header">
              <h2>{{ editingService ? 'Szolgáltatás szerkesztése' : 'Szolgáltatás létrehozása' }}</h2>
              <button class="modal-close" @click="closeModal">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-body">
              <div v-if="error" class="error-message">{{ error }}</div>

              <div v-if="!editingService" class="form-group">
                <label>Szálloda kiválasztása *</label>
                <select v-model="form.hotelId" required class="form-select">
                  <option value="">Válasszon szállodát...</option>
                  <option v-for="hotel in hotels" :key="hotel.id" :value="hotel.id">
                    {{ hotel.name || hotel.location || `Hotel #${hotel.id}` }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label>Szolgáltatás neve *</label>
                <input v-model="form.name" type="text" required placeholder="pl. WiFi, Reggeli" />
              </div>

              <div class="form-group">
                <label>Leírás</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  placeholder="Adja meg a szolgáltatás leírását"
                ></textarea>
              </div>

              <div class="form-group">
                <label>Ár (€)</label>
                <input
                  v-model.number="form.price"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="Hagyja üresen ingyenes szolgáltatás esetén"
                />
                <small class="form-hint">Hagyja üresen, ha a szolgáltatás ingyenes</small>
              </div>


              <div class="form-group">
                <label>Szolgáltatás képe</label>
                <ImageUpload
                  v-model="form.images"
                  :max-files="1"
                  @upload="handleImageUpload"
                />
              </div>

              <div class="modal-footer">
                <button type="button" @click="closeModal" class="btn-secondary">Mégse</button>
                <button type="submit" class="btn-primary" :disabled="saving">
                  {{ saving ? 'Mentés...' : 'Mentés' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>

      <ConfirmDialog
        v-model:visible="showDeleteDialog"
        title="Szolgáltatás törlése"
        :message="`Biztosan törölni szeretné ezt a szolgáltatást? Ez a művelet nem vonható vissza.`"
        confirm-text="Törlés"
        cancel-text="Mégse"
        confirm-type="danger"
        @confirm="confirmDelete"
      />

      <Toast ref="toast" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import DataTable from '../../components/DataTable.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import ImageUpload from '../../components/ImageUpload.vue'
import Toast from '../../components/Toast.vue'
import { adminService } from '../../services/adminService'
import { useAuthStore } from '../../stores/auth'
import { useBodyScrollLock } from '../../composables/useBodyScrollLock'

const authStore = useAuthStore()
const hotels = ref([])
const services = ref([])
const loading = ref(true)
const showHotelCarousel = ref(false)
const currentHotelIndex = ref(0)
const showModal = ref(false)
const showDeleteDialog = ref(false)
const editingService = ref(null)
const saving = ref(false)
const error = ref('')
const toast = ref(null)
const serviceToDelete = ref(null)
const selectedHotelId = ref(null)

const selectedHotel = computed(() => {
  return hotels.value.find(h => h.id === selectedHotelId.value)
})

const form = ref({
  hotelId: null,
  name: '',
  description: '',
  price: null,
  images: []
})

const columns = [
  { key: 'name', label: 'Szolgáltatás neve', sortable: true },
  { key: 'description', label: 'Leírás' },
  { key: 'price', label: 'Ár', sortable: true }
]

const loadHotels = async () => {
  try {
    const data = await adminService.getHotels()
    hotels.value = data.filter(h => h.user_id === authStore.state.user?.id)
    
    // Auto-select first hotel if none selected yet
    if (hotels.value.length > 0 && !selectedHotelId.value) {
      selectedHotelId.value = hotels.value[0].id
      currentHotelIndex.value = 0
      await loadServices()
    } else if (selectedHotelId.value) {
      const index = hotels.value.findIndex(h => h.id === selectedHotelId.value)
      if (index >= 0) currentHotelIndex.value = index
    }
  } catch (err) {
    showToast('A szállodák betöltése sikertelen', 'error')
  }
}

const handleHotelChange = async () => {
  if (selectedHotelId.value) {
    const index = hotels.value.findIndex(h => h.id === selectedHotelId.value)
    if (index >= 0) currentHotelIndex.value = index
    await loadServices()
  } else {
    services.value = []
    loading.value = false
  }
}

// Hotel carousel helpers (shared UX with bookings supervision)
const openHotelCarousel = () => {
  if (!hotels.value.length) return
  const index = hotels.value.findIndex(h => h.id === selectedHotelId.value)
  currentHotelIndex.value = index >= 0 ? index : 0
  showHotelCarousel.value = true
}

const closeHotelCarousel = () => {
  showHotelCarousel.value = false
}

const selectHotelFromCarousel = async (hotelId) => {
  selectedHotelId.value = hotelId
  await loadServices()
  const index = hotels.value.findIndex(h => h.id === hotelId)
  if (index >= 0) currentHotelIndex.value = index
  showHotelCarousel.value = false
}

const previousHotel = () => {
  if (!hotels.value.length) return
  currentHotelIndex.value =
    (currentHotelIndex.value - 1 + hotels.value.length) % hotels.value.length
}

const nextHotel = () => {
  if (!hotels.value.length) return
  currentHotelIndex.value =
    (currentHotelIndex.value + 1) % hotels.value.length
}

const goToHotel = (index) => {
  if (index < 0 || index >= hotels.value.length) return
  currentHotelIndex.value = index
}

const getImageUrl = (relativePath) => {
  if (!relativePath) return ''
  const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || ''
  if (relativePath.startsWith('http')) return relativePath
  return `${baseUrl}${relativePath}`
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
}

const loadServices = async () => {
  if (!selectedHotelId.value) return

  loading.value = true
  try {
    services.value = await adminService.getServicesByHotelId(selectedHotelId.value)
  } catch (err) {
    // If endpoint doesn't exist, show empty array
    if (err.response?.status === 404) {
      services.value = []
    } else {
      showToast('A szolgáltatások betöltése sikertelen', 'error')
    }
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingService.value = null
  resetForm()
  showModal.value = true
}

const handleEdit = (service) => {
  editingService.value = service
  form.value = {
    name: service.name || '',
    description: service.description || '',
    price: service.price || null,
    images: []
  }
  showModal.value = true
}

const handleDelete = (service) => {
  serviceToDelete.value = service
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  if (!serviceToDelete.value) return

  try {
    await adminService.deleteService(serviceToDelete.value.id)
    showToast('Szolgáltatás sikeresen törölve', 'success')
    await loadServices()
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to delete service', 'error')
  } finally {
    serviceToDelete.value = null
  }
}

const handleImageUpload = async (imageObj) => {
  // Image upload for services - implementation depends on backend
  try {
    // This would need to be implemented based on backend API
    showToast('Image upload for services not yet implemented', 'info')
  } catch (err) {
    showToast('Failed to upload image', 'error')
    throw err
  }
}

const handleSubmit = async () => {
  if (!editingService.value && !form.value.hotelId) {
    showToast('Kérjük, válasszon szállodát', 'warning')
    return
  }

  const hotelId = editingService.value ? selectedHotel.value?.id : form.value.hotelId
  if (!hotelId) {
    showToast('Kérjük, válasszon szállodát', 'warning')
    return
  }

  saving.value = true
  error.value = ''

  try {
    if (editingService.value) {
      await adminService.updateService(editingService.value.id, {
        name: form.value.name,
        description: form.value.description,
        price: form.value.price || null
      })
      showToast('Szolgáltatás sikeresen frissítve', 'success')
    } else {
      await adminService.createService(hotelId, {
        name: form.value.name,
        description: form.value.description,
        price: form.value.price || null
      })
      showToast('Szolgáltatás sikeresen létrehozva', 'success')
    }
    closeModal()
    await loadServices()
  } catch (err) {
    error.value = err.response?.data?.message || 'A szolgáltatás mentése sikertelen'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingService.value = null
  resetForm()
  error.value = ''
}

// Lock body scroll when modal is open
useBodyScrollLock(showModal)

const resetForm = () => {
  form.value = {
    hotelId: null,
    name: '',
    description: '',
    price: null,
    images: []
  }
}

const showToast = (message, type) => {
  if (toast.value) {
    toast.value.showToast(message, type)
  } else if (window.showToast) {
    window.showToast(message, type)
  }
}

const handleHotelsUpdated = async () => {
  await loadHotels()
}

onMounted(() => {
  loadHotels()
  window.addEventListener('hotels-updated', handleHotelsUpdated)
})

onUnmounted(() => {
  window.removeEventListener('hotels-updated', handleHotelsUpdated)
})
</script>

<style scoped>
.services-page {
  max-width: 1400px;
}

/* Shared compact hotel selector + carousel styles (aligned with bookings list) */
.hotel-selector-compact {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  background: white;
  border-radius: 10px;
  border: 1px solid #e9ecef;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
  max-width: 1400px;
  margin-left: auto;
  margin-right: auto;
  gap: 1rem;
}

.hotel-selector-compact.header-compact {
  max-width: 420px;
  margin: 0 0 2rem 0;
  flex-shrink: 0;
  padding: 0.625rem 0.875rem;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.hotel-compact-info {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  flex: 1;
  min-width: 0;
}

.hotel-compact-icon {
  font-size: 2rem;
  flex-shrink: 0;
  line-height: 1;
}

.hotel-compact-details {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  min-width: 0;
  flex: 1;
}

.hotel-compact-name {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
  line-height: 1.3;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.hotel-compact-location {
  font-size: 0.85rem;
  color: #6c757d;
  margin: 0;
  line-height: 1.3;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.hotel-change-btn {
  padding: 0.625rem 1.125rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  white-space: nowrap;
  flex-shrink: 0;
}

.hotel-change-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 10px rgba(102, 126, 234, 0.35);
}

.hotel-change-btn svg {
  width: 16px;
  height: 16px;
}

.hotel-carousel-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
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

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.hotel-selector {
  margin-bottom: 2rem;
  padding: 1.5rem;
}

.hotel-selector h3 {
  margin: 0 0 1rem 0;
  font-size: 1.1rem;
  color: #2c3e50;
}

.hotel-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.95rem;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow: auto;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #2c3e50;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: #7f8c8d;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.modal-close:hover {
  background-color: #ecf0f1;
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #2c3e50;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #667eea;
}

.form-hint {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.85rem;
  color: #7f8c8d;
}

.switch {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.switch input[type="checkbox"] {
  width: 48px;
  height: 24px;
  appearance: none;
  background-color: #ccc;
  border-radius: 12px;
  position: relative;
  cursor: pointer;
  transition: background-color 0.3s;
}

.switch input[type="checkbox"]:checked {
  background-color: #667eea;
}

.switch input[type="checkbox"]::before {
  content: '';
  position: absolute;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: white;
  top: 2px;
  left: 2px;
  transition: transform 0.3s;
}

.switch input[type="checkbox"]:checked::before {
  transform: translateX(24px);
}

.switch-label {
  font-weight: 500;
  color: #2c3e50;
}

.modal-footer {
  padding-top: 1.5rem;
  border-top: 1px solid #e0e0e0;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background-color: #ecf0f1;
  color: #2c3e50;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-secondary:hover {
  background-color: #d5dbdb;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
}

.badge-success {
  background-color: #d4edda;
  color: #155724;
}

.badge-secondary {
  background-color: #e2e3e5;
  color: #383d41;
}

.text-muted {
  color: #7f8c8d;
  font-style: italic;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active .modal-content,
.modal-leave-active .modal-content {
  transition: transform 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
  transform: scale(0.9);
}
</style>
