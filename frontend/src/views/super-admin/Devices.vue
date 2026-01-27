<template>
  <SuperAdminLayout>
    <div class="devices-page">
      <div class="page-header">
        <h1>Eszköz regisztráció</h1>
        <button @click="openCreateModal" class="btn-primary">
          <span>➕</span> Eszköz regisztrálása
        </button>
      </div>

      <DataTable
        :data="devices"
        :columns="columns"
        :loading="loading"
        search-placeholder="Eszközök keresése..."
        empty-message="Nincs regisztrált eszköz"
        :search-fields="['name', 'hotel.name', 'hotel.location']"
        :on-edit="handleEdit"
        :on-delete="handleDelete"
      >
        <template #cell-hotel="{ value }">
          {{ value?.name || 'N/A' }} <span v-if="value?.location">- {{ value.location }}</span>
        </template>
        <template #cell-is_active="{ value }">
          <span class="status-badge" :class="{ 'status-active': value, 'status-inactive': !value }">
            {{ value ? 'Aktív' : 'Áttekintésre vár' }}
          </span>
        </template>
        <template #actions="{ row }">
          <button @click="handleToggleActive(row)" class="btn-icon" :class="row.is_active ? 'btn-deactivate' : 'btn-activate'" :title="row.is_active ? 'Deaktiválás' : 'Aktiválás'">
            {{ row.is_active ? '⏸️' : '▶️' }}
          </button>
          <button @click="handleRegenerateToken(row)" class="btn-icon btn-regenerate" title="Token újragenerálása">🔄</button>
          <button @click="handleEdit(row)" class="btn-icon btn-edit" title="Szerkesztés">✏️</button>
          <button @click="handleDelete(row)" class="btn-icon btn-delete" title="Törlés">🗑️</button>
        </template>
      </DataTable>

      <!-- Create/Edit Modal -->
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal-content">
            <div class="modal-header">
              <h2>{{ editingDevice ? 'Eszköz szerkesztése' : 'Új eszköz regisztrálása' }}</h2>
              <button class="modal-close" @click="closeModal">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-body">
              <div v-if="error" class="error-message">{{ error }}</div>

              <div class="form-group">
                <label>Eszköz neve *</label>
                <input v-model="form.name" type="text" required placeholder="Adja meg az eszköz azonosítóját/nevét" />
              </div>

              <div class="form-group">
                <label>Szálloda *</label>
                <select v-model="form.hotels_id" required :disabled="editingDevice">
                  <option value="">Válasszon szállodát...</option>
                  <option v-for="hotel in availableHotels" :key="hotel.id" :value="hotel.id">
                    {{ hotel.name }} - {{ hotel.location }}
                  </option>
                </select>
                <small v-if="editingDevice" class="form-hint">A szálloda nem módosítható a regisztráció után</small>
              </div>

              <div v-if="editingDevice" class="form-group">
                <label>Státusz</label>
                <label class="switch">
                  <input v-model="form.is_active" type="checkbox" />
                  <span class="slider"></span>
                  <span class="switch-label">{{ form.is_active ? 'Aktív' : 'Áttekintésre vár' }}</span>
                </label>
                <small class="form-hint">Aktiválja az eszközt az API hozzáférés engedélyezéséhez</small>
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

      <!-- Token Display Modal -->
      <Transition name="modal">
        <div v-if="showTokenModal" class="modal-overlay" @click.self="closeTokenModal">
          <div class="modal-content token-modal">
            <div class="modal-header">
              <h2>🔐 Eszköz token</h2>
              <button class="modal-close" @click="closeTokenModal">×</button>
            </div>
            <div class="modal-body">
              <div class="token-warning">
                <strong>⚠️ Fontos:</strong> Mentsen el ezt a tokent biztonságosan! Később nem lesz lehetősége újra megtekinteni!
              </div>
              <div class="token-display">
                <code class="token-value">{{ displayedToken }}</code>
                <button @click="copyToken" class="btn-copy">📋 Másolás</button>
              </div>
              <div class="token-info">
                <p><strong>Használat:</strong></p>
                <ul>
                  <li>Küldje el az <code>Authorization</code> fejlécben: <code>Bearer {token}</code></li>
                  <li>Vagy lekérdezési paraméterként: <code>?token={token}</code></li>
                </ul>
                <p><strong>Példa:</strong></p>
                <code class="code-block">GET /api/devices/bookings/{hotelId}<br/>Authorization: Bearer {{ displayedToken }}</code>
              </div>
              <div class="modal-footer">
                <button @click="closeTokenModal" class="btn-primary">Elmentettem</button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <ConfirmDialog
        v-model:visible="showDeleteDialog"
        title="Eszköz törlése"
        :message="`Biztosan törölni szeretné ezt az eszközt? Ez a művelet nem vonható vissza, és az eszköz elveszíti az API hozzáférést.`"
        confirm-text="Törlés"
        cancel-text="Mégse"
        confirm-type="danger"
        @confirm="confirmDelete"
      />

      <Toast ref="toast" />
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import SuperAdminLayout from '../../layouts/SuperAdminLayout.vue'
import DataTable from '../../components/DataTable.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import Toast from '../../components/Toast.vue'
import { superAdminService } from '../../services/superAdminService'

const devices = ref([])
const availableHotels = ref([])
const loading = ref(true)
const showModal = ref(false)
const showTokenModal = ref(false)
const showDeleteDialog = ref(false)
const editingDevice = ref(null)
const deviceToDelete = ref(null)
const saving = ref(false)
const error = ref('')
const displayedToken = ref('')
const toast = ref(null)

const form = ref({
  hotels_id: '',
  name: '',
  is_active: false
})

const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'name', label: 'Eszköz neve', sortable: true },
  { key: 'hotel', label: 'Szálloda' },
  { key: 'is_active', label: 'Státusz', sortable: true },
  { key: 'created_at', label: 'Regisztrálva', sortable: true }
]

const loadDevices = async () => {
  loading.value = true
  try {
    const data = await superAdminService.getAllDevices()
    devices.value = data
  } catch (err) {
    showToast('Az eszközök betöltése sikertelen', 'error')
  } finally {
    loading.value = false
  }
}

const loadHotels = async () => {
  try {
    const data = await superAdminService.getAllHotels()
    // Filter out hotels that already have devices (only when creating)
    if (!editingDevice.value) {
      const hotelsWithDevices = devices.value.map(d => d.hotels_id)
      availableHotels.value = data.filter(h => !hotelsWithDevices.includes(h.id))
    } else {
      availableHotels.value = data
    }
  } catch (err) {
    console.error('Failed to load hotels:', err)
  }
}

const openCreateModal = async () => {
  editingDevice.value = null
  resetForm()
  await loadHotels()
  showModal.value = true
}

const handleEdit = async (device) => {
  editingDevice.value = device
  form.value = {
    hotels_id: device.hotels_id || '',
    name: device.name || '',
    is_active: device.is_active || false
  }
  await loadHotels()
  showModal.value = true
}

const handleToggleActive = async (device) => {
  const action = device.is_active ? 'deaktiválni' : 'aktiválni'
  if (!confirm(`Biztosan ${action} szeretné az "${device.name}" eszközt?`)) {
    return
  }

  try {
    await superAdminService.updateDevice(device.id, {
      is_active: !device.is_active
    })
    showToast(`Eszköz sikeresen ${device.is_active ? 'deaktiválva' : 'aktiválva'}`, 'success')
    await loadDevices()
  } catch (err) {
    showToast(err.response?.data?.message || `Az eszköz ${action} sikertelen`, 'error')
  }
}

const handleRegenerateToken = async (device) => {
  if (!confirm(`Biztosan újragenerálni szeretné a tokent az "${device.name}" eszközhöz? A régi token többé nem fog működni.`)) {
    return
  }

  try {
    const response = await superAdminService.regenerateDeviceToken(device.id)
    displayedToken.value = response.token
    showTokenModal.value = true
    showToast('Token sikeresen újragenerálva', 'success')
    await loadDevices()
  } catch (err) {
    showToast(err.response?.data?.message || 'A token újragenerálása sikertelen', 'error')
  }
}

const handleDelete = (device) => {
  deviceToDelete.value = device
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  if (!deviceToDelete.value) return

  try {
    await superAdminService.deleteDevice(deviceToDelete.value.id)
    showToast('Eszköz sikeresen törölve', 'success')
    await loadDevices()
  } catch (err) {
    showToast(err.response?.data?.message || 'Az eszköz törlése sikertelen', 'error')
  } finally {
    deviceToDelete.value = null
  }
}

const handleSubmit = async () => {
  saving.value = true
  error.value = ''

  try {
    if (editingDevice.value) {
      await superAdminService.updateDevice(editingDevice.value.id, form.value)
      showToast('Eszköz sikeresen frissítve', 'success')
      closeModal()
      await loadDevices()
    } else {
      const response = await superAdminService.createDevice(form.value)
      // Show token modal after creation
      displayedToken.value = response.token
      showTokenModal.value = true
      showToast('Eszköz sikeresen regisztrálva', 'success')
      closeModal()
      await loadDevices()
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Az eszköz mentése sikertelen'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingDevice.value = null
  resetForm()
  error.value = ''
}

const closeTokenModal = () => {
  showTokenModal.value = false
  displayedToken.value = ''
}

const copyToken = async () => {
  if (!displayedToken.value) {
    showToast('Nincs másolható token', 'error')
    return
  }
  
  try {
    // Try modern clipboard API first
    if (navigator.clipboard && navigator.clipboard.writeText) {
      await navigator.clipboard.writeText(displayedToken.value)
      showToast('Token másolva a vágólapra', 'success')
    } else {
      // Fallback for older browsers
      const textArea = document.createElement('textarea')
      textArea.value = displayedToken.value
      textArea.style.position = 'fixed'
      textArea.style.left = '-999999px'
      textArea.style.top = '-999999px'
      document.body.appendChild(textArea)
      textArea.focus()
      textArea.select()
      try {
        document.execCommand('copy')
        showToast('Token másolva a vágólapra', 'success')
      } catch (err) {
        showToast('Nem sikerült másolni a tokent', 'error')
      }
      document.body.removeChild(textArea)
    }
  } catch (err) {
    // Final fallback
    const textArea = document.createElement('textarea')
    textArea.value = displayedToken.value
    textArea.style.position = 'fixed'
    textArea.style.left = '-999999px'
    textArea.style.top = '-999999px'
    document.body.appendChild(textArea)
    textArea.select()
    try {
      document.execCommand('copy')
      showToast('Token másolva a vágólapra', 'success')
    } catch (err2) {
      showToast('Nem sikerült másolni a tokent', 'error')
    }
    document.body.removeChild(textArea)
  }
}

const resetForm = () => {
  form.value = {
    hotels_id: '',
    name: '',
    is_active: false
  }
}

const showToast = (message, type) => {
  if (toast.value) {
    toast.value.showToast(message, type)
  } else if (window.showToast) {
    window.showToast(message, type)
  }
}

onMounted(async () => {
  await loadDevices()
})
</script>

<style scoped>
.devices-page {
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
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
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

.btn-activate {
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.3);
  color: #22c55e;
}

.btn-activate:hover {
  background: rgba(34, 197, 94, 0.2);
}

.btn-deactivate {
  background: rgba(251, 191, 36, 0.1);
  border: 1px solid rgba(251, 191, 36, 0.3);
  color: #fbbf24;
}

.btn-deactivate:hover {
  background: rgba(251, 191, 36, 0.2);
}

.btn-regenerate {
  background: rgba(59, 130, 246, 0.1);
  border: 1px solid rgba(59, 130, 246, 0.3);
  color: #3b82f6;
}

.btn-regenerate:hover {
  background: rgba(59, 130, 246, 0.2);
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-active {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.status-inactive {
  background: rgba(251, 191, 36, 0.2);
  color: #fbbf24;
  border: 1px solid rgba(251, 191, 36, 0.3);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: #1a1a1a;
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.token-modal {
  max-width: 700px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid rgba(102, 126, 234, 0.2);
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.modal-close {
  background: none;
  border: none;
  color: #9ca3af;
  font-size: 2rem;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: all 0.2s;
}

.modal-close:hover {
  background: rgba(220, 38, 38, 0.1);
  color: #fca5a5;
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
  color: #e5e7eb;
  font-weight: 500;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  background: rgba(30, 30, 30, 0.8);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 6px;
  color: #e5e7eb;
  font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.form-hint {
  display: block;
  margin-top: 0.25rem;
  color: #9ca3af;
  font-size: 0.85rem;
}

.switch {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
}

.switch input[type="checkbox"] {
  width: auto;
  margin: 0;
}

.switch-label {
  color: #e5e7eb;
  font-weight: 500;
}

.error-message {
  background: rgba(220, 38, 38, 0.1);
  border: 1px solid rgba(220, 38, 38, 0.3);
  color: #fca5a5;
  padding: 0.75rem;
  border-radius: 6px;
  margin-bottom: 1rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(102, 126, 234, 0.2);
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: rgba(107, 114, 128, 0.1);
  border: 1px solid rgba(107, 114, 128, 0.3);
  border-radius: 6px;
  color: #d1d5db;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: rgba(107, 114, 128, 0.2);
}

.token-warning {
  background: rgba(251, 191, 36, 0.1);
  border: 1px solid rgba(251, 191, 36, 0.3);
  color: #fbbf24;
  padding: 1rem;
  border-radius: 6px;
  margin-bottom: 1.5rem;
}

.token-display {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.token-value {
  flex: 1;
  background: rgba(30, 30, 30, 0.8);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 6px;
  padding: 1rem;
  color: #22c55e;
  font-family: 'Courier New', monospace;
  font-size: 0.9rem;
  word-break: break-all;
  overflow-wrap: break-word;
}

.btn-copy {
  padding: 0.75rem 1rem;
  background: rgba(102, 126, 234, 0.1);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 6px;
  color: #667eea;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
  white-space: nowrap;
}

.btn-copy:hover {
  background: rgba(102, 126, 234, 0.2);
}

.token-info {
  background: rgba(30, 30, 30, 0.5);
  border-radius: 6px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.token-info p {
  margin: 0.5rem 0;
  color: #e5e7eb;
}

.token-info ul {
  margin: 0.5rem 0;
  padding-left: 1.5rem;
  color: #d1d5db;
}

.token-info code {
  background: rgba(0, 0, 0, 0.3);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  color: #22c55e;
  font-family: 'Courier New', monospace;
  font-size: 0.85rem;
}

.code-block {
  display: block;
  padding: 1rem;
  margin-top: 0.5rem;
  white-space: pre-wrap;
  word-break: break-all;
}

.btn-icon {
  padding: 0.5rem;
  background: rgba(107, 114, 128, 0.1);
  border: 1px solid rgba(107, 114, 128, 0.3);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 1rem;
  margin-right: 0.5rem;
}

.btn-icon:hover {
  background: rgba(107, 114, 128, 0.2);
}

.btn-edit {
  color: #3b82f6;
}

.btn-delete {
  color: #ef4444;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
