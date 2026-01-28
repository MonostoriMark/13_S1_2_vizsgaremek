<template>
  <AdminLayout>
    <div class="services-page">
      <div class="page-header">
        <h1>Szolgáltatások kezelése</h1>
        <button @click="openCreateModal" class="btn-primary">
          <span>➕</span> Szolgáltatás hozzáadása
        </button>
      </div>

      <div class="hotel-selector card">
        <h3>Szálloda kiválasztása</h3>
        <select v-model="selectedHotelId" @change="handleHotelChange" class="hotel-select">
          <option value="">Válasszon szállodát...</option>
          <option v-for="hotel in hotels" :key="hotel.id" :value="hotel.id">
            {{ hotel.name || hotel.location || `Hotel #${hotel.id}` }}
          </option>
        </select>
      </div>

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
    
    // Auto-select if only one hotel
    if (hotels.value.length === 1) {
      selectedHotelId.value = hotels.value[0].id
      await loadServices()
    }
  } catch (err) {
    showToast('A szállodák betöltése sikertelen', 'error')
  }
}

const handleHotelChange = async () => {
  if (selectedHotelId.value) {
    await loadServices()
  } else {
    services.value = []
    loading.value = false
  }
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
