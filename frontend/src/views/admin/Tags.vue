<template>
  <AdminLayout>
    <div class="tags-page">
      <div class="page-header">
        <h1>Szolgáltatás címkék kezelése</h1>
        <button @click="openCreateModal" class="btn-primary">
          <span>➕</span> Címke hozzáadása
        </button>
      </div>

      <p class="page-description">
        Kezelje a megosztott címkéket, amelyeket bármely szálloda vagy szoba használhat. A szállodákhoz kapcsolt címkék nem használhatók szobákon, és fordítva.
      </p>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Címkék betöltése...</p>
      </div>

      <!-- Error State -->
      <div v-if="error" class="error-message">{{ error }}</div>

      <!-- Tags Table -->
      <div v-if="!loading" class="tags-table-container">
        <table class="tags-table minimal-table">
          <thead>
            <tr>
              <th>Címke ID</th>
              <th>Címke neve</th>
              <th>Létrehozó</th>
              <th>Hozzárendelés</th>
              <th>Státusz</th>
              <th>Műveletek</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tag in tags" :key="tag.id">
              <td class="tag-id-cell">#{{ tag.id }}</td>
              <td class="tag-name-cell">
                {{ tag.name }}
              </td>
              <td class="tag-creator-cell">
                <span v-if="tag.user">{{ tag.user.name || tag.user.email }}</span>
                <span v-else class="text-muted">Ismeretlen</span>
              </td>
              <td class="tag-assignment-cell">
                <div class="assignment-info">
                  <span v-if="getTagUsage(tag.id).hotel_count > 0" class="assignment-badge hotel">
                    🏨 {{ getTagUsage(tag.id).hotel_count }} szálloda
                  </span>
                  <span v-if="getTagUsage(tag.id).room_count > 0" class="assignment-badge room">
                    🛏️ {{ getTagUsage(tag.id).room_count }} szoba
                  </span>
                  <span v-if="getTagUsage(tag.id).hotel_count === 0 && getTagUsage(tag.id).room_count === 0" class="assignment-badge none">
                    Nincs hozzárendelés
                  </span>
                </div>
              </td>
              <td class="tag-status-cell">
                <span :class="['status-badge', getTagUsage(tag.id).is_used ? 'status-used' : 'status-available']">
                  {{ getTagUsage(tag.id).is_used ? 'Használatban' : 'Elérhető' }}
                </span>
              </td>
              <td class="tag-actions-cell">
                <div class="table-actions">
                  <button
                    @click="openEditModal(tag)"
                    class="btn-actions btn-edit"
                    title="Címke szerkesztése"
                  >
                    ✏️ Szerkesztés
                  </button>
                  <button
                    @click="deleteTag(tag.id)"
                    class="btn-actions btn-delete"
                    :disabled="!canDeleteTag(tag)"
                    :title="getDeleteTooltip(tag)"
                  >
                    🗑️ Törlés
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!tags.length">
              <td colspan="6" class="empty-row">
                Nincsenek címkék. Hozza létre az első címkéjét a kezdéshez.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Create/Edit Tag Modal -->
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal-content">
            <div class="modal-header">
              <h2>{{ isEditMode ? 'Címke szerkesztése' : 'Új címke létrehozása' }}</h2>
              <button class="modal-close" @click="closeModal">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-body">
              <div v-if="error" class="error-message">{{ error }}</div>

              <div class="form-group">
                <label>Címke neve *</label>
                <input
                  v-model="tagForm.name"
                  type="text"
                  required
                  placeholder="pl.: Ingyenes Wi-Fi, Medence, Parkolás"
                  maxlength="100"
                />
                <p class="form-hint">Ez a címke minden szálloda számára elérhető lesz a szállodáikhoz vagy szobáikhoz.</p>
              </div>

              <div class="modal-footer">
                <button type="button" @click="closeModal" class="btn-secondary">Mégse</button>
                <button type="submit" class="btn-primary" :disabled="saving">
                  {{ saving ? (isEditMode ? 'Frissítés...' : 'Létrehozás...') : (isEditMode ? 'Címke frissítése' : 'Címke létrehozása') }}
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
import { ref, onMounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import Toast from '../../components/Toast.vue'
import { tagService } from '../../services/tagService'
import { useBodyScrollLock } from '../../composables/useBodyScrollLock'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const tags = ref([])
const tagUsage = ref({ usage: {}, hotel_tags: [], room_tags: [] })
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

const getTagUsage = (tagId) => {
  return tagUsage.value.usage[tagId] || {
    is_used: false,
    hotels: [],
    rooms: [],
    hotel_count: 0,
    room_count: 0
  }
}

const isHotelTag = (tagId) => {
  return tagUsage.value.hotel_tags.includes(tagId)
}

const isRoomTag = (tagId) => {
  return tagUsage.value.room_tags.includes(tagId)
}

const canDeleteTag = (tag) => {
  // Can only delete if:
  // 1. User is the creator
  // 2. Tag is not in use
  const isCreator = tag.user_id === authStore.state.user?.id
  const isUsed = getTagUsage(tag.id).is_used
  return isCreator && !isUsed
}

const getDeleteTooltip = (tag) => {
  if (tag.user_id !== authStore.state.user?.id) {
    return 'Csak a saját címkéit törölheti'
  }
  if (getTagUsage(tag.id).is_used) {
    return 'A használatban lévő címke nem törölhető'
  }
  return 'Címke törlése'
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
    error.value = err.response?.data?.message || 'A címkék betöltése sikertelen'
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

// Lock body scroll when modal is open
useBodyScrollLock(showModal)

const handleSubmit = async () => {
  if (!tagForm.value.name.trim()) {
    error.value = 'A címke neve kötelező'
    return
  }

  saving.value = true
  error.value = ''

  try {
    if (isEditMode.value) {
      await tagService.updateTag(editingTagId.value, tagForm.value.name.trim())
      showToast('Címke sikeresen frissítve', 'success')
    } else {
      await tagService.createTag(tagForm.value.name.trim())
      showToast('Címke sikeresen létrehozva', 'success')
    }
    closeModal()
    await loadTags()
  } catch (err) {
    error.value = err.response?.data?.message || (isEditMode.value ? 'A címke frissítése sikertelen' : 'A címke létrehozása sikertelen')
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const deleteTag = async (tagId) => {
  const tag = tags.value.find(t => t.id === tagId)
  if (!tag) return

  if (!canDeleteTag(tag)) {
    if (tag.user_id !== authStore.state.user?.id) {
      showToast('Csak a saját címkéit törölheti', 'warning')
    } else if (getTagUsage(tagId).is_used) {
      showToast('A használatban lévő címke nem törölhető', 'warning')
    }
    return
  }

  if (!confirm('Biztosan törölni szeretné ezt a címkét? Ez a művelet nem vonható vissza.')) {
    return
  }

  try {
    await tagService.deleteTag(tagId)
    showToast('Címke sikeresen törölve', 'success')
    await loadTags()
  } catch (err) {
    const errorMessage = err.response?.data?.message || 'A címke törlése sikertelen'
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

.tags-table-container {
  margin-top: 2rem;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

.tags-table {
  width: 100%;
  border-collapse: collapse;
}

.tags-table thead {
  background: #f9fafb;
}

.tags-table th,
.tags-table td {
  padding: 0.9rem 1rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
  font-size: 0.9rem;
}

.tags-table th {
  font-weight: 600;
  color: #374151;
}

.tags-table tbody tr:hover {
  background: #f9fafb;
}

.tag-id-cell {
  width: 90px;
  font-weight: 600;
  color: #6b7280;
}

.tag-creator-cell {
  color: #4b5563;
  font-size: 0.85rem;
}

.text-muted {
  color: #9ca3af;
  font-style: italic;
}

.tag-assignment-cell {
  min-width: 200px;
}

.assignment-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.assignment-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 500;
  white-space: nowrap;
}

.assignment-badge.hotel {
  background: #eef2ff;
  color: #6366f1;
}

.assignment-badge.room {
  background: #ecfeff;
  color: #06b6d4;
}

.assignment-badge.none {
  background: #f3f4f6;
  color: #6b7280;
  font-style: italic;
}

.tag-status-cell {
  min-width: 120px;
}

.status-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.status-used {
  background: #fef3c7;
  color: #d97706;
}

.status-badge.status-available {
  background: #d1fae5;
  color: #059669;
  color: #4b5563;
}

.tag-name-cell {
  font-weight: 600;
  color: #111827;
}

.tag-usage-cell {
  width: 160px;
}

.tag-actions-cell {
  width: 260px;
}

.table-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-start;
  align-items: center;
  flex-wrap: wrap;
}

.btn-actions {
  padding: 0.45rem 0.9rem;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  transition: all 0.2s ease;
}

.btn-actions.btn-edit {
  background: #e8f4f8;
  color: #2563eb;
}

.btn-actions.btn-edit:hover {
  background: #d0e8f0;
  box-shadow: 0 2px 6px rgba(37, 99, 235, 0.18);
}

.btn-actions.btn-delete {
  background: #fee2e2;
  color: #b91c1c;
}

.btn-actions.btn-delete:hover:not(:disabled) {
  background: #fecaca;
  box-shadow: 0 2px 6px rgba(185, 28, 28, 0.2);
}

.btn-actions.btn-delete:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.empty-row {
  text-align: center;
  padding: 2rem 1rem;
  color: #6b7280;
  font-size: 0.9rem;
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
