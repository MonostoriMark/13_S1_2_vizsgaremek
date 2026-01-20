<template>
  <AdminLayout>
    <div class="rooms-page">
      <div class="page-header">
        <h1>Szobák kezelése</h1>
        <button @click="openCreateModal" class="btn-primary">
          <span>➕</span> Szoba hozzáadása
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
          :data="rooms"
          :columns="columns"
          :loading="loading"
          search-placeholder="Szobák keresése..."
          empty-message="Nem található szoba"
          :search-fields="['name', 'description']"
          :on-edit="handleEdit"
          :on-delete="handleDelete"
        >
          <template #cell-pricePerNight="{ value }">
            €{{ parseFloat(value || 0).toFixed(2) }}
          </template>
          <template #cell-capacity="{ value }">
            {{ value }} vendég
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
          <div class="modal-content large">
            <div class="modal-header">
              <h2>{{ editingRoom ? 'Szoba szerkesztése' : 'Szoba létrehozása' }}</h2>
              <button class="modal-close" @click="closeModal">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-body">
              <div v-if="error" class="error-message">{{ error }}</div>

              <div v-if="!editingRoom" class="form-group">
                <label>Szálloda kiválasztása *</label>
                <select v-model="form.hotelId" required class="form-select">
                  <option value="">Válasszon szállodát...</option>
                  <option v-for="hotel in hotels" :key="hotel.id" :value="hotel.id">
                    {{ hotel.name || hotel.location || `Hotel #${hotel.id}` }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label>Szoba neve/száma *</label>
                <input v-model="form.name" type="text" required placeholder="pl. 101-es szoba" />
              </div>

              <div class="form-group">
                <label>Leírás *</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  required
                  placeholder="Adja meg a szoba leírását"
                ></textarea>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Kapacitás (vendég) *</label>
                  <input v-model.number="form.capacity" type="number" min="1" required />
                </div>
                <div class="form-group">
                  <label>Ár/éjszaka (€) *</label>
                  <input v-model.number="form.pricePerNight" type="number" min="0" step="0.01" required />
                </div>
              </div>

              <div class="form-group">
                <label>Alapár (€) *</label>
                <input v-model.number="form.basePrice" type="number" min="0" step="0.01" required />
              </div>

              <div class="form-group">
                <label>Szoba képek</label>
                <ImageUpload
                  v-model="form.images"
                  :max-files="10"
                  @upload="handleImageUpload"
                />
              </div>

              <!-- Tags Section -->
              <div v-if="editingRoom" class="form-group">
                <label>Tags</label>
                <div class="tags-section">
                  <div v-if="currentRoomTags.length > 0" class="current-tags">
                    <div
                      v-for="tag in currentRoomTags"
                      :key="tag.id"
                      class="tag-chip room"
                    >
                      <span>{{ tag.name }}</span>
                      <button
                        type="button"
                        @click="removeTagFromRoom(tag.id)"
                        class="tag-remove"
                        :disabled="savingTags"
                      >
                        ×
                      </button>
                    </div>
                  </div>
                  <div v-else class="no-tags">Nincs hozzárendelt címke</div>
                  
                  <div class="add-tags-section">
                    <label class="add-tags-label">Címkék hozzáadása</label>
                    <div class="available-tags">
                      <button
                        v-for="tag in availableTagsForRoom"
                        :key="tag.id"
                        type="button"
                        @click="addTagToRoom(tag.id)"
                        class="tag-option"
                        :disabled="savingTags"
                      >
                        + {{ tag.name }}
                      </button>
                    </div>
                    <p v-if="availableTagsForRoom.length === 0" class="no-available-tags">
                      Nincs elérhető címke. Hozzon létre címkéket a Címkék kezelése oldalon.
                    </p>
                  </div>
                </div>
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
        title="Szoba törlése"
        :message="`Biztosan törölni szeretné ezt a szobát? Ez a művelet nem vonható vissza.`"
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
import { tagService } from '../../services/tagService'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const hotels = ref([])
const rooms = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteDialog = ref(false)
const editingRoom = ref(null)
const saving = ref(false)
const error = ref('')
const toast = ref(null)
const roomToDelete = ref(null)
const selectedHotelId = ref(null)

// Tags management
const allTags = ref([])
const tagUsage = ref({ hotel_tags: [], room_tags: [] })
const currentRoomTags = ref([])
const savingTags = ref(false)

const selectedHotel = computed(() => {
  return hotels.value.find(h => h.id === selectedHotelId.value)
})

const form = ref({
  hotelId: null,
  name: '',
  description: '',
  capacity: 1,
  pricePerNight: 0,
  basePrice: 0,
  images: []
})

const columns = [
  { key: 'name', label: 'Szoba neve', sortable: true },
  { key: 'description', label: 'Leírás' },
  { key: 'capacity', label: 'Kapacitás', sortable: true },
  { key: 'pricePerNight', label: 'Ár/éjszaka', sortable: true, type: 'currency' }
]

const loadHotels = async () => {
  try {
    const data = await adminService.getHotels()
    hotels.value = data.filter(h => h.user_id === authStore.state.user?.id)
    
    // Auto-select if only one hotel
    if (hotels.value.length === 1) {
      selectedHotelId.value = hotels.value[0].id
      await loadRooms()
    }
  } catch (err) {
    showToast('A szállodák betöltése sikertelen', 'error')
  }
}

const handleHotelChange = async () => {
  if (selectedHotelId.value) {
    await loadRooms()
  } else {
    rooms.value = []
    loading.value = false
  }
}

const loadRooms = async () => {
  if (!selectedHotelId.value) return

  loading.value = true
  try {
    const roomsData = await adminService.getRoomsByHotelId(selectedHotelId.value)
    rooms.value = roomsData.map(room => ({
      ...room,
      tags: room.tags || room.serviceTags || []
    }))
  } catch (err) {
    showToast('A szobák betöltése sikertelen', 'error')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  if (!selectedHotel.value) {
    showToast('Kérjük, először válasszon szállodát', 'warning')
    return
  }
  editingRoom.value = null
  resetForm()
  showModal.value = true
}

const handleEdit = async (room) => {
  editingRoom.value = room
  form.value = {
    name: room.name || '',
    description: room.description || '',
    capacity: room.capacity || 1,
    pricePerNight: room.pricePerNight || 0,
    basePrice: room.basePrice || 0,
    images: []
  }

    // Load room images
  try {
    const images = await adminService.getRoomImages(room.id)
    // Convert image URLs to full URLs for display
    form.value.images = images.map(img => {
      let imageUrl = img.url
      if (imageUrl && imageUrl.startsWith('/storage/')) {
        const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000'
        imageUrl = `${baseUrl}${imageUrl}`
      }
      return { id: img.id, url: imageUrl }
    })
  } catch (err) {
    console.error('Failed to load room images:', err)
  }

  // Load room tags - ensure we have proper tag objects
  if (room.tags && Array.isArray(room.tags)) {
    // Normalize tags - handle both object format and string format
    currentRoomTags.value = room.tags.map(tag => {
      if (typeof tag === 'object' && tag !== null) {
        return tag // Already an object with id, name, etc.
      }
      return { id: null, name: tag } // String format, convert to object
    })
  } else if (room.serviceTags && Array.isArray(room.serviceTags)) {
    currentRoomTags.value = room.serviceTags.map(tag => {
      if (typeof tag === 'object' && tag !== null) {
        return tag
      }
      return { id: null, name: tag }
    })
  } else {
    currentRoomTags.value = []
  }

  await loadTags()
  showModal.value = true
}

const handleDelete = (room) => {
  roomToDelete.value = room
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  if (!roomToDelete.value) return

  try {
    await adminService.deleteRoom(roomToDelete.value.id)
    showToast('Szoba sikeresen törölve', 'success')
    await loadRooms()
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to delete room', 'error')
  } finally {
    roomToDelete.value = null
  }
}

const handleImageUpload = async (imageObj) => {
  // Resolve/reject the promise if it exists
  const resolvePromise = imageObj._uploadPromise?.resolve
  const rejectPromise = imageObj._uploadPromise?.reject

  try {
    if (!selectedHotel.value) {
      const error = 'Please select a hotel first'
      showToast(error, 'warning')
      imageObj.uploading = false
      if (rejectPromise) rejectPromise(new Error(error))
      return
    }

    const roomId = editingRoom.value?.id
    if (!roomId) {
      const error = 'Please select or create a room first. Images can only be uploaded when editing an existing room.'
      showToast(error, 'warning')
      imageObj.uploading = false
      if (rejectPromise) rejectPromise(new Error(error))
      return
    }

    // Check if file exists
    if (!imageObj.file) {
      const error = 'No file found in image object'
      console.error('ImageObj:', imageObj)
      throw new Error(error)
    }

    // Verify file is a File object
    if (!(imageObj.file instanceof File) && !(imageObj.file instanceof Blob)) {
      console.error('Invalid file type:', typeof imageObj.file, imageObj.file)
      throw new Error('Invalid file object. Please try selecting the image again.')
    }

    console.log('Uploading file:', {
      name: imageObj.file.name,
      size: imageObj.file.size,
      type: imageObj.file.type,
      roomId: roomId
    })

    // Upload image and link to room
    const result = await adminService.uploadImage(imageObj.file, [roomId])
    if (result && result.image) {
      // Convert relative URL to full URL for display
      let imageUrl = result.image.url
      if (imageUrl && imageUrl.startsWith('/storage/')) {
        const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000'
        imageUrl = `${baseUrl}${imageUrl}`
      }
      imageObj.id = result.image.id
      imageObj.url = imageUrl
      imageObj.preview = imageUrl
      imageObj.progress = 100
      imageObj.uploading = false
      showToast('Kép sikeresen feltöltve', 'success')
      if (resolvePromise) resolvePromise()
    } else {
      throw new Error('Invalid response from server')
    }
  } catch (err) {
    imageObj.uploading = false
    imageObj.error = err.message || 'Upload failed'
    
    // Extract detailed error message
    let errorMessage = 'Failed to upload image'
    if (err.response?.data) {
      if (err.response.data.errors) {
        // Laravel validation errors
        const errors = err.response.data.errors
        const errorText = Object.values(errors).flat().join(', ')
        errorMessage = errorText || err.response.data.message || errorMessage
      } else if (err.response.data.message) {
        errorMessage = err.response.data.message
      }
    } else if (err.message) {
      errorMessage = err.message
    }
    
    showToast(errorMessage, 'error')
    console.error('Image upload error:', err.response?.data || err)
    
    // Reject the promise
    if (rejectPromise) {
      rejectPromise(err)
    } else {
      // Re-throw so the component can handle it
      throw err
    }
  }
}

const handleSubmit = async () => {
  if (!editingRoom.value && !form.value.hotelId) {
    showToast('Please select a hotel', 'warning')
    return
  }

  const hotelId = editingRoom.value ? selectedHotel.value?.id : form.value.hotelId
  if (!hotelId) {
    showToast('Please select a hotel', 'warning')
    return
  }

  saving.value = true
  error.value = ''

  try {
    if (editingRoom.value) {
      await adminService.updateRoom(editingRoom.value.id, {
        name: form.value.name,
        description: form.value.description,
        capacity: form.value.capacity,
        pricePerNight: form.value.pricePerNight,
        basePrice: form.value.basePrice
      })
      showToast('Szoba sikeresen frissítve', 'success')
    } else {
      await adminService.createRoom(hotelId, {
        name: form.value.name,
        description: form.value.description,
        capacity: form.value.capacity,
        pricePerNight: form.value.pricePerNight,
        basePrice: form.value.basePrice
      })
      showToast('Szoba sikeresen létrehozva', 'success')
    }
    closeModal()
    await loadRooms()
  } catch (err) {
    error.value = err.response?.data?.message || 'A szoba mentése sikertelen'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingRoom.value = null
  currentRoomTags.value = []
  resetForm()
  error.value = ''
}

const loadTags = async () => {
  try {
    const [tagsData, usageData] = await Promise.all([
      tagService.getAllTags(),
      tagService.getTagUsage()
    ])
    allTags.value = tagsData
    tagUsage.value = usageData
  } catch (err) {
    console.error('Failed to load tags:', err)
  }
}

const availableTagsForRoom = computed(() => {
  // Filter out tags that are already used on hotels (exclusivity)
  return allTags.value.filter(tag => {
    // Don't show tags already assigned to this room
    if (currentRoomTags.value.some(t => t.id === tag.id)) {
      return false
    }
    // Don't show tags that are used on any hotel
    return !tagUsage.value.hotel_tags.includes(tag.id)
  })
})

const addTagToRoom = async (tagId) => {
  if (!editingRoom.value) return
  
  savingTags.value = true
  try {
    await tagService.addTagsToRoom(editingRoom.value.id, [tagId])
    const tag = allTags.value.find(t => t.id === tagId)
    if (tag) {
      currentRoomTags.value.push(tag)
    }
    // Update tag usage
    const usage = await tagService.getTagUsage()
    tagUsage.value = usage
    showToast('Címke sikeresen hozzáadva', 'success')
  } catch (err) {
    const message = err.response?.data?.message || 'Failed to add tag'
    showToast(message, 'error')
  } finally {
    savingTags.value = false
  }
}

const removeTagFromRoom = async (tagId) => {
  if (!editingRoom.value) return
  
  savingTags.value = true
  try {
    await tagService.removeTagFromRoom(editingRoom.value.id, tagId)
    currentRoomTags.value = currentRoomTags.value.filter(t => t.id !== tagId)
    // Update tag usage
    const usage = await tagService.getTagUsage()
    tagUsage.value = usage
    showToast('Címke sikeresen eltávolítva', 'success')
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to remove tag', 'error')
  } finally {
    savingTags.value = false
  }
}

const resetForm = () => {
  form.value = {
    hotelId: null,
    name: '',
    description: '',
    capacity: 1,
    pricePerNight: 0,
    basePrice: 0,
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

onMounted(async () => {
  await loadHotels()
  await loadTags()
  window.addEventListener('hotels-updated', handleHotelsUpdated)
})

onUnmounted(() => {
  window.removeEventListener('hotels-updated', handleHotelsUpdated)
})
</script>

<style scoped>
.rooms-page {
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
  max-width: 800px;
  width: 90%;
  max-height: 90vh;
  overflow: auto;
}

.modal-content.large {
  max-width: 900px;
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

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

/* Tags Section */
.tags-section {
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  padding: 1rem;
  background: #f8f9fa;
}

.current-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1rem;
  min-height: 2rem;
}

.tag-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
  color: white;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.tag-chip.room {
  background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
}

.tag-remove {
  background: rgba(255, 255, 255, 0.3);
  border: none;
  color: white;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1.1rem;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
  padding: 0;
}

.tag-remove:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.5);
}

.tag-remove:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.no-tags {
  color: #7f8c8d;
  font-style: italic;
  margin-bottom: 1rem;
  padding: 0.5rem;
}

.add-tags-section {
  border-top: 1px solid #e0e0e0;
  padding-top: 1rem;
}

.add-tags-label {
  display: block;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.75rem;
  font-size: 0.9rem;
}

.available-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tag-option {
  padding: 0.5rem 1rem;
  background: white;
  border: 2px solid #06b6d4;
  color: #06b6d4;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tag-option:hover:not(:disabled) {
  background: #06b6d4;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(6, 182, 212, 0.3);
}

.tag-option:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.no-available-tags {
  color: #7f8c8d;
  font-size: 0.85rem;
  font-style: italic;
  margin-top: 0.5rem;
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
