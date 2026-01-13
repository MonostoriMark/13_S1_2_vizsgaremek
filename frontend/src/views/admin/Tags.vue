<template>
  <AdminLayout>
    <div class="tags-page">
      <div class="page-header">
        <h1>Service Tags Management</h1>
        <button @click="openCreateModal" class="btn-primary">
          <span>➕</span> Add Tag
        </button>
      </div>

      <p class="page-description">
        Manage shared tags that can be used by any hotel or room. Tags linked to hotels cannot be used on rooms, and vice versa.
      </p>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading tags...</p>
      </div>

      <!-- Error State -->
      <div v-if="error" class="error-message">{{ error }}</div>

      <!-- Tags Grid -->
      <div v-if="!loading && tags.length > 0" class="tags-grid">
        <div
          v-for="tag in tags"
          :key="tag.id"
          class="tag-card"
        >
          <div class="tag-header">
            <h3 class="tag-name">{{ tag.name }}</h3>
            <div class="tag-usage">
              <span v-if="isHotelTag(tag.id)" class="usage-badge hotel">Hotel</span>
              <span v-else-if="isRoomTag(tag.id)" class="usage-badge room">Room</span>
              <span v-else class="usage-badge available">Available</span>
            </div>
          </div>
          <div class="tag-actions">
            <button
              @click="openEditModal(tag)"
              class="btn-edit"
              :title="'Edit tag'"
            >
              ✏️ Edit
            </button>
            <button
              @click="deleteTag(tag.id)"
              class="btn-delete"
              :disabled="isHotelTag(tag.id) || isRoomTag(tag.id)"
              :title="(isHotelTag(tag.id) || isRoomTag(tag.id)) ? 'Cannot delete tag that is in use' : 'Delete tag'"
            >
              🗑️ Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && tags.length === 0" class="empty-state">
        <div class="empty-icon">🏷️</div>
        <h2>No Tags Found</h2>
        <p>Create your first tag to get started.</p>
      </div>

      <!-- Create/Edit Tag Modal -->
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal-content">
            <div class="modal-header">
              <h2>{{ isEditMode ? 'Edit Tag' : 'Create New Tag' }}</h2>
              <button class="modal-close" @click="closeModal">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-body">
              <div v-if="error" class="error-message">{{ error }}</div>

              <div class="form-group">
                <label>Tag Name *</label>
                <input
                  v-model="tagForm.name"
                  type="text"
                  required
                  placeholder="e.g., Free Wi-Fi, Pool, Parking"
                  maxlength="100"
                />
                <p class="form-hint">This tag will be available for all hotels to use on their hotels or rooms.</p>
              </div>

              <div class="modal-footer">
                <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary" :disabled="saving">
                  {{ saving ? (isEditMode ? 'Updating...' : 'Creating...') : (isEditMode ? 'Update Tag' : 'Create Tag') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>

      <Toast ref="toast" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import Toast from '../../components/Toast.vue'
import { tagService } from '../../services/tagService'

const tags = ref([])
const tagUsage = ref({ hotel_tags: [], room_tags: [] })
const loading = ref(true)
const showModal = ref(false)
const saving = ref(false)
const error = ref('')
const toast = ref(null)
const isEditMode = ref(false)
const editingTagId = ref(null)

const tagForm = ref({
  name: ''
})

const isHotelTag = (tagId) => {
  return tagUsage.value.hotel_tags.includes(tagId)
}

const isRoomTag = (tagId) => {
  return tagUsage.value.room_tags.includes(tagId)
}

const loadTags = async () => {
  loading.value = true
  error.value = ''
  try {
    const [tagsData, usageData] = await Promise.all([
      tagService.getAllTags(),
      tagService.getTagUsage()
    ])
    tags.value = tagsData
    tagUsage.value = usageData
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load tags'
    showToast(error.value, 'error')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  isEditMode.value = false
  editingTagId.value = null
  tagForm.value.name = ''
  error.value = ''
  showModal.value = true
}

const openEditModal = (tag) => {
  isEditMode.value = true
  editingTagId.value = tag.id
  tagForm.value.name = tag.name
  error.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  isEditMode.value = false
  editingTagId.value = null
  tagForm.value.name = ''
  error.value = ''
}

const handleSubmit = async () => {
  if (!tagForm.value.name.trim()) {
    error.value = 'Tag name is required'
    return
  }

  saving.value = true
  error.value = ''

  try {
    if (isEditMode.value) {
      await tagService.updateTag(editingTagId.value, tagForm.value.name.trim())
      showToast('Tag updated successfully', 'success')
    } else {
      await tagService.createTag(tagForm.value.name.trim())
      showToast('Tag created successfully', 'success')
    }
    closeModal()
    await loadTags()
  } catch (err) {
    error.value = err.response?.data?.message || (isEditMode.value ? 'Failed to update tag' : 'Failed to create tag')
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const deleteTag = async (tagId) => {
  if (isHotelTag(tagId) || isRoomTag(tagId)) {
    showToast('Cannot delete tag that is currently in use', 'warning')
    return
  }

  if (!confirm('Are you sure you want to delete this tag? This action cannot be undone.')) {
    return
  }

  try {
    await tagService.deleteTag(tagId)
    showToast('Tag deleted successfully', 'success')
    await loadTags()
  } catch (err) {
    const errorMessage = err.response?.data?.message || 'Failed to delete tag'
    showToast(errorMessage, 'error')
  }
}

const showToast = (message, type) => {
  if (toast.value) {
    toast.value.showToast(message, type)
  } else if (window.showToast) {
    window.showToast(message, type)
  }
}

onMounted(() => {
  loadTags()
})
</script>

<style scoped>
.tags-page {
  max-width: 1400px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
}

.page-description {
  color: #7f8c8d;
  font-size: 0.95rem;
  margin-bottom: 2rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 10px;
  border-left: 4px solid #667eea;
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
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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
  padding: 1rem 1.5rem;
  border-radius: 10px;
  margin-bottom: 1.5rem;
  border-left: 4px solid #c33;
}

.tags-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.tag-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border: 2px solid transparent;
  display: flex;
  flex-direction: column;
  min-height: 120px;
}

.tag-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border-color: #667eea;
}

.tag-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  gap: 0.75rem;
  flex-wrap: nowrap;
}

.tag-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
  flex: 1;
  min-width: 0;
  word-break: break-word;
  line-height: 1.3;
}

.tag-usage {
  flex-shrink: 0;
  display: flex;
  align-items: center;
}

.usage-badge {
  display: inline-block;
  padding: 0.4rem 0.85rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
  line-height: 1;
  min-width: fit-content;
}

.usage-badge.hotel {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.usage-badge.room {
  background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
  color: white;
}

.usage-badge.available {
  background: #e8f4f8;
  color: #667eea;
}

.tag-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.btn-edit {
  padding: 0.5rem 1rem;
  background: #e8f4f8;
  color: #667eea;
  border: 1px solid #b3d9e6;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  flex: 1;
  text-align: center;
}

.btn-edit:hover {
  background: #d0e8f0;
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(102, 126, 234, 0.2);
}

.btn-delete {
  padding: 0.5rem 1rem;
  background: #fee;
  color: #c33;
  border: 1px solid #fcc;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  flex: 1;
  text-align: center;
}

.btn-delete:hover:not(:disabled) {
  background: #fcc;
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(204, 51, 51, 0.2);
}

.btn-delete:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f5f5f5;
  color: #999;
  border-color: #ddd;
}

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
}

/* Modal Styles */
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
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  max-width: 500px;
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
  font-size: 1.5rem;
  color: #7f8c8d;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.modal-close:hover {
  background: #f0f0f0;
  color: #2c3e50;
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.form-group input {
  width: 100%;
  padding: 0.875rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-hint {
  font-size: 0.85rem;
  color: #7f8c8d;
  margin-top: 0.5rem;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e0e0e0;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
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

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .tags-grid {
    grid-template-columns: 1fr;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .btn-primary {
    width: 100%;
    justify-content: center;
  }
}
</style>
