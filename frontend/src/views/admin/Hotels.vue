<template>
  <AdminLayout>
    <div class="hotels-page">
      <div class="page-header">
        <h1>Szállodák kezelése</h1>
        <button @click="openCreateModal" class="btn-primary">
          <span>➕</span> Szálloda hozzáadása
        </button>
      </div>

      <DataTable
        :data="hotels"
        :columns="columns"
        :loading="loading"
        search-placeholder="Szállodák keresése..."
        empty-message="Nem található szálloda"
        :search-fields="['name', 'location', 'type', 'description']"
        :on-edit="handleEdit"
        :on-delete="handleDelete"
      >
        <template #cell-starRating="{ value }">
          <span class="stars">{{ '★'.repeat(value || 0) }}</span>
        </template>
        <template #actions="{ row }">
          <button @click="handleEdit(row)" class="btn-icon btn-edit" title="Szerkesztés">✏️</button>
          <button @click="handleDelete(row)" class="btn-icon btn-delete" title="Törlés">🗑️</button>
        </template>
      </DataTable>

      <!-- Create/Edit Modal -->
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal-content large">
            <div class="modal-header">
              <h2>{{ editingHotel ? 'Szálloda szerkesztése' : 'Szálloda létrehozása' }}</h2>
              <button class="modal-close" @click="closeModal">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-body">
              <div v-if="error" class="error-message">{{ error }}</div>

              <div class="form-group">
                <label>Szálloda neve *</label>
                <input v-model="form.name" type="text" required placeholder="Adja meg a szálloda nevét" />
              </div>

              <div class="form-group">
                <label>Helyszín/Cím *</label>
                <input v-model="form.location" type="text" required placeholder="Adja meg a helyszínt" />
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Típus</label>
                  <select v-model="form.type">
                    <option value="">Válasszon típust</option>
                    <option value="hotel">Szálloda</option>
                    <option value="apartment">Apartman</option>
                    <option value="villa">Villa</option>
                    <option value="other">Egyéb</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Csillag értékelés</label>
                  <select v-model.number="form.starRating">
                    <option :value="null">Nincs értékelés</option>
                    <option :value="1">1 csillag</option>
                    <option :value="2">2 csillag</option>
                    <option :value="3">3 csillag</option>
                    <option :value="4">4 csillag</option>
                    <option :value="5">5 csillag</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label>Leírás</label>
                <textarea
                  v-model="form.description"
                  rows="4"
                  placeholder="Adja meg a szálloda leírását"
                ></textarea>
              </div>

              <!-- Cover Image Upload -->
              <div v-if="editingHotel" class="form-group">
                <label>Borítókép</label>
                <div class="cover-image-section">
                  <div v-if="coverImagePreview || editingHotel.cover_image" class="cover-image-preview">
                    <img 
                      :src="coverImagePreview || getImageUrl(editingHotel.cover_image)" 
                      alt="Borítókép"
                      class="cover-preview-img"
                      @error="handleImageError"
                    />
                    <button 
                      type="button" 
                      @click="removeCoverImage" 
                      class="remove-cover-btn"
                      :disabled="uploadingCover"
                    >
                      ×
                    </button>
                    <div v-if="uploadingCover" class="upload-overlay">
                      <div class="upload-spinner"></div>
                      <p>Feltöltés...</p>
                    </div>
                  </div>
                  <div v-else class="cover-image-upload">
                    <input
                      ref="coverImageInput"
                      type="file"
                      accept="image/*"
                      @change="handleCoverImageSelect"
                      style="display: none"
                    />
                    <button 
                      type="button" 
                      @click="coverImageInput?.click()" 
                      class="btn-upload-cover"
                      :disabled="uploadingCover"
                    >
                      {{ uploadingCover ? 'Feltöltés...' : '📷 Borítókép feltöltése' }}
                    </button>
                    <p class="upload-hint">Töltse fel a szálloda borítóképét (JPG, PNG, GIF, WebP - Max 4MB)</p>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label>Státusz</label>
                <label class="switch">
                  <input v-model="form.active" type="checkbox" />
                  <span class="slider"></span>
                  <span class="switch-label">{{ form.active ? 'Aktív' : 'Inaktív' }}</span>
                </label>
              </div>

              <!-- Tags Section -->
              <div v-if="editingHotel" class="form-group">
                <label>Címkék</label>
                <div class="tags-section">
                  <div v-if="currentHotelTags.length > 0" class="current-tags">
                    <div
                      v-for="tag in currentHotelTags"
                      :key="tag.id"
                      class="tag-chip"
                    >
                      <span>{{ tag.name }}</span>
                      <button
                        type="button"
                        @click="removeTagFromHotel(tag.id)"
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
                        v-for="tag in availableTagsForHotel"
                        :key="tag.id"
                        type="button"
                        @click="addTagToHotel(tag.id)"
                        class="tag-option"
                        :disabled="savingTags"
                      >
                        + {{ tag.name }}
                      </button>
                    </div>
                    <p v-if="availableTagsForHotel.length === 0" class="no-available-tags">
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
        title="Szálloda törlése"
        :message="`Biztosan törölni szeretné ezt a szállodát? Ez a művelet nem vonható vissza.`"
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
import { ref, onMounted, computed } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import DataTable from '../../components/DataTable.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import Toast from '../../components/Toast.vue'
import { adminService } from '../../services/adminService'
import { tagService } from '../../services/tagService'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const hotels = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteDialog = ref(false)
const editingHotel = ref(null)
const saving = ref(false)
const error = ref('')
const toast = ref(null)
const hotelToDelete = ref(null)

// Tags management
const allTags = ref([])
const tagUsage = ref({ hotel_tags: [], room_tags: [] })
const currentHotelTags = ref([])
const savingTags = ref(false)

const form = ref({
  name: '',
  location: '',
  type: '',
  starRating: null,
  description: '',
  active: true
})

const coverImageInput = ref(null)
const coverImagePreview = ref(null)
const uploadingCover = ref(false)

const columns = [
  { key: 'name', label: 'Szálloda neve', sortable: true },
  { key: 'location', label: 'Helyszín', sortable: true },
  { key: 'type', label: 'Típus', sortable: true },
  { key: 'starRating', label: 'Értékelés', sortable: true },
  { key: 'description', label: 'Leírás' }
]

const loadHotels = async () => {
  loading.value = true
  try {
    const data = await adminService.getHotels()
    // Filter to show only hotels belonging to the current user
    hotels.value = data.filter(h => h.user_id === authStore.state.user?.id).map(hotel => ({
      ...hotel,
      tags: hotel.tags || []
    }))
  } catch (err) {
    showToast('A szállodák betöltése sikertelen', 'error')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingHotel.value = null
  resetForm()
  showModal.value = true
}

const handleEdit = async (hotel) => {
  editingHotel.value = hotel
  form.value = {
    name: hotel.name || '',
    location: hotel.location || '',
    type: hotel.type || '',
    starRating: hotel.starRating || null,
    description: hotel.description || '',
    active: true // Note: active field may need to be added to backend
  }
  
  // Reset cover image preview
  coverImagePreview.value = null
  
  // Load hotel tags - ensure we have proper tag objects
  if (hotel.tags && Array.isArray(hotel.tags)) {
    // Normalize tags - handle both object format and string format
    currentHotelTags.value = hotel.tags.map(tag => {
      if (typeof tag === 'object' && tag !== null) {
        return tag // Already an object with id, name, etc.
      }
      return { id: null, name: tag } // String format, convert to object
    })
  } else {
    currentHotelTags.value = []
  }
  
  await loadTags()
  showModal.value = true
}

const handleDelete = (hotel) => {
  hotelToDelete.value = hotel
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  if (!hotelToDelete.value) return

  try {
    await adminService.deleteHotel(hotelToDelete.value.id)
    showToast('Szálloda sikeresen törölve', 'success')
    await loadHotels()
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to delete hotel', 'error')
  } finally {
    hotelToDelete.value = null
  }
}

const handleSubmit = async () => {
  saving.value = true
  error.value = ''

  try {
    if (editingHotel.value) {
      await adminService.updateHotel(editingHotel.value.id, {
        name: form.value.name,
        location: form.value.location,
        type: form.value.type,
        starRating: form.value.starRating,
        description: form.value.description
      })
      showToast('Szálloda sikeresen frissítve', 'success')
    } else {
      await adminService.createHotel({
        name: form.value.name,
        location: form.value.location,
        type: form.value.type,
        starRating: form.value.starRating,
        description: form.value.description
      })
      showToast('Szálloda sikeresen létrehozva', 'success')
      // Refresh hotel list to update dropdowns in other pages
      window.dispatchEvent(new CustomEvent('hotels-updated'))
    }
    closeModal()
    await loadHotels()
  } catch (err) {
    error.value = err.response?.data?.message || 'A szálloda mentése sikertelen'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingHotel.value = null
  currentHotelTags.value = []
  resetForm()
  error.value = ''
  // Clean up preview URL
  if (coverImagePreview.value && coverImagePreview.value.startsWith('blob:')) {
    URL.revokeObjectURL(coverImagePreview.value)
  }
  coverImagePreview.value = null
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

const availableTagsForHotel = computed(() => {
  // Filter out tags that are already used on rooms (exclusivity)
  return allTags.value.filter(tag => {
    // Don't show tags already assigned to this hotel
    if (currentHotelTags.value.some(t => t.id === tag.id)) {
      return false
    }
    // Don't show tags that are used on any room
    return !tagUsage.value.room_tags.includes(tag.id)
  })
})

const addTagToHotel = async (tagId) => {
  if (!editingHotel.value) return
  
  savingTags.value = true
  try {
    await tagService.addTagsToHotel(editingHotel.value.id, [tagId])
    const tag = allTags.value.find(t => t.id === tagId)
    if (tag) {
      currentHotelTags.value.push(tag)
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

const removeTagFromHotel = async (tagId) => {
  if (!editingHotel.value) return
  
  savingTags.value = true
  try {
    await tagService.removeTagFromHotel(editingHotel.value.id, tagId)
    currentHotelTags.value = currentHotelTags.value.filter(t => t.id !== tagId)
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
    name: '',
    location: '',
    type: '',
    starRating: null,
    description: '',
    active: true
  }
  coverImagePreview.value = null
}

const getImageUrl = (imagePath) => {
  if (!imagePath) return null
  // If already a full URL, return as is
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath
  }
  // If relative path starting with /storage/, construct full URL
  if (imagePath.startsWith('/storage/')) {
    const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000'
    return `${baseUrl}${imagePath}`
  }
  return imagePath
}

const handleCoverImageSelect = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  // Validate file
  const maxSize = 4 * 1024 * 1024 // 4MB
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']

  if (!validTypes.includes(file.type)) {
    showToast('Érvénytelen fájltípus. Csak JPG, PNG, GIF és WebP formátumok engedélyezettek.', 'error')
    return
  }

  if (file.size > maxSize) {
    showToast('A fájl mérete meghaladja a 4MB korlátot.', 'error')
    return
  }

  if (!editingHotel.value) {
    showToast('Kérjük, először mentse el a szállodát a borítókép feltöltése előtt', 'warning')
    return
  }

  // Create preview
  coverImagePreview.value = URL.createObjectURL(file)

  // Upload immediately
  uploadingCover.value = true
  try {
    const result = await adminService.uploadHotelCoverImage(editingHotel.value.id, file)
    showToast('Borítókép sikeresen feltöltve', 'success')
    // Update the hotel object
    if (editingHotel.value) {
      editingHotel.value.cover_image = result.cover_image
    }
    // Reload hotels to get updated data
    await loadHotels()
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to upload cover image', 'error')
    coverImagePreview.value = null
  } finally {
    uploadingCover.value = false
    // Reset input
    if (coverImageInput.value) {
      coverImageInput.value.value = ''
    }
  }
}

const removeCoverImage = async () => {
  if (!editingHotel.value) return

  // Note: We'd need a delete endpoint for cover images
  // For now, just clear the preview
  coverImagePreview.value = null
    showToast('Borítókép eltávolítva. Töltse fel egy újat a lecseréléshez.', 'info')
}

const handleImageError = (event) => {
  // If image fails to load, hide it
  event.target.style.display = 'none'
}

const showToast = (message, type) => {
  if (toast.value) {
    toast.value.showToast(message, type)
  } else if (window.showToast) {
    window.showToast(message, type)
  }
}

onMounted(async () => {
  await loadHotels()
  await loadTags()
})
</script>

<style scoped>
.hotels-page {
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

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
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

.modal-content.large {
  max-width: 800px;
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
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
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
  border: 2px solid #667eea;
  color: #667eea;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tag-option:hover:not(:disabled) {
  background: #667eea;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
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

/* Cover Image Section */
.cover-image-section {
  margin-top: 0.5rem;
}

.cover-image-preview {
  position: relative;
  width: 100%;
  max-width: 500px;
  aspect-ratio: 16/9;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid #e0e0e0;
  background: #f5f5f5;
}

.cover-preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.remove-cover-btn {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: rgba(231, 76, 60, 0.9);
  color: white;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  line-height: 1;
}

.remove-cover-btn:hover:not(:disabled) {
  background-color: #e74c3c;
  transform: scale(1.1);
}

.remove-cover-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.upload-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
}

.upload-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 0.5rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.cover-image-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 2rem;
  border: 2px dashed #bdc3c7;
  border-radius: 8px;
  background: #f8f9fa;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cover-image-upload:hover {
  border-color: #667eea;
  background: #e8f4f8;
}

.btn-upload-cover {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-upload-cover:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-upload-cover:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.upload-hint {
  font-size: 0.85rem;
  color: #7f8c8d;
  margin: 0;
  text-align: center;
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

.stars {
  color: #f39c12;
  font-size: 1.1rem;
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
