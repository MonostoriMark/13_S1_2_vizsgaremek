<template>
  <SuperAdminLayout>
    <div class="users-page">
      <div class="page-header">
        <h1>Felhasználók kezelése</h1>
        <button @click="openCreateModal" class="btn-primary">
          <span>➕</span> Felhasználó hozzáadása
        </button>
      </div>

      <DataTable
        :data="users"
        :columns="columns"
        :loading="loading"
        search-placeholder="Felhasználók keresése..."
        empty-message="Nincs felhasználó"
        :search-fields="['name', 'email', 'role']"
        :on-edit="handleEdit"
        :on-delete="handleDelete"
      >
        <template #cell-role="{ value }">
          <span class="role-badge" :class="`role-${value}`">{{ value === 'user' ? 'Felhasználó' : value === 'hotel' ? 'Szálloda admin' : 'Super admin' }}</span>
        </template>
        <template #cell-isVerified="{ value }">
          <span class="verified-badge" :class="{ verified: value, unverified: !value }">
            {{ value ? '✓' : '⚠' }}
          </span>
        </template>
        <template #cell-two_factor_enabled="{ value }">
          <span class="twofa-badge" :class="{ enabled: value }">
            {{ value ? '🔐' : '🔓' }}
          </span>
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
              <h2>{{ editingUser ? 'Felhasználó szerkesztése' : 'Új felhasználó létrehozása' }}</h2>
              <button class="modal-close" @click="closeModal">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-body">
              <div v-if="error" class="error-message">{{ error }}</div>

              <div class="form-group">
                <label>Teljes név *</label>
                <input v-model="form.name" type="text" required placeholder="Adja meg a teljes nevet" />
              </div>

              <div class="form-group">
                <label>Email cím *</label>
                <input v-model="form.email" type="email" required placeholder="Adja meg az email címet" />
              </div>

              <div class="form-group">
                <label>Szerepkör *</label>
                <select v-model="form.role" required>
                  <option value="user">Felhasználó</option>
                  <option value="hotel">Szálloda admin</option>
                  <option value="super_admin">Super admin</option>
                </select>
              </div>

              <div class="form-group">
                <label>Jelszó {{ editingUser ? '(hagyja üresen a jelenlegi megtartásához)' : '*' }}</label>
                <div class="input-wrapper-password">
                  <input 
                    v-model="form.password" 
                    :type="showPassword ? 'text' : 'password'"
                    :required="!editingUser"
                    placeholder="Adja meg a jelszót"
                    :minlength="8"
                  />
                  <button
                    type="button"
                    class="password-toggle"
                    @click="showPassword = !showPassword"
                    :aria-label="showPassword ? 'Jelszó elrejtése' : 'Jelszó megjelenítése'"
                    :title="showPassword ? 'Jelszó elrejtése' : 'Jelszó megjelenítése'"
                  >
                    {{ showPassword ? 'Elrejt' : 'Mutat' }}
                  </button>
                </div>
              </div>

              <div class="form-group">
                <label>Email megerősítve</label>
                <label class="switch">
                  <input v-model="form.isVerified" type="checkbox" />
                  <span class="slider"></span>
                  <span class="switch-label">{{ form.isVerified ? 'Megerősítve' : 'Nincs megerősítve' }}</span>
                </label>
              </div>

              <div class="form-section">
                <h3 class="section-title">Számlázási információk</h3>
                <div class="form-group">
                  <label>Adószám</label>
                  <input v-model="form.tax_number" type="text" placeholder="Adja meg az adószámot" />
                </div>
                <div class="form-group">
                  <label>Bankszámla</label>
                  <input v-model="form.bank_account" type="text" placeholder="Adja meg a bankszámlát" />
                </div>
                <div class="form-group">
                  <label>EU adószám</label>
                  <input v-model="form.eu_tax_number" type="text" placeholder="Adja meg az EU adószámot" />
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
        title="Felhasználó törlése"
        :message="`Biztosan törölni szeretné a ${userToDelete?.name} felhasználót? Ez a művelet nem vonható vissza.`"
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
import { useBodyScrollLock } from '../../composables/useBodyScrollLock'

const users = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteDialog = ref(false)
const editingUser = ref(null)
const userToDelete = ref(null)
const saving = ref(false)
const error = ref('')
const toast = ref(null)
const showPassword = ref(false)

const form = ref({
  name: '',
  email: '',
  role: 'user',
  password: '',
  isVerified: false,
  tax_number: '',
  bank_account: '',
  eu_tax_number: ''
})

const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'name', label: 'Név', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'role', label: 'Szerepkör', sortable: true },
  { key: 'isVerified', label: 'Megerősítve' },
  { key: 'two_factor_enabled', label: '2FA' },
  { key: 'created_at', label: 'Létrehozva', sortable: true }
]

const loadUsers = async () => {
  loading.value = true
  try {
    const data = await superAdminService.getAllUsers()
    users.value = data.map(user => ({
      ...user,
      created_at: user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'
    }))
  } catch (err) {
    showToast('A felhasználók betöltése sikertelen', 'error')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingUser.value = null
  resetForm()
  showModal.value = true
}

const handleEdit = (user) => {
  editingUser.value = user
  form.value = {
    name: user.name || '',
    email: user.email || '',
    role: user.role || 'user',
    password: '',
    isVerified: user.isVerified || false,
    tax_number: user.tax_number || '',
    bank_account: user.bank_account || '',
    eu_tax_number: user.eu_tax_number || ''
  }
  showModal.value = true
}

const handleDelete = (user) => {
  userToDelete.value = user
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  if (!userToDelete.value) return

  try {
    await superAdminService.deleteUser(userToDelete.value.id)
    showToast('Felhasználó sikeresen törölve', 'success')
    await loadUsers()
  } catch (err) {
    showToast(err.response?.data?.message || 'A felhasználó törlése sikertelen', 'error')
  } finally {
    userToDelete.value = null
  }
}

const handleSubmit = async () => {
  saving.value = true
  error.value = ''

  try {
    const data = {
      name: form.value.name,
      email: form.value.email,
      role: form.value.role,
      isVerified: form.value.isVerified,
      tax_number: form.value.tax_number || null,
      bank_account: form.value.bank_account || null,
      eu_tax_number: form.value.eu_tax_number || null
    }

    if (form.value.password) {
      data.password = form.value.password
    }

    if (editingUser.value) {
      await superAdminService.updateUser(editingUser.value.id, data)
      showToast('Felhasználó sikeresen frissítve', 'success')
    } else {
      await superAdminService.createUser(data)
      showToast('Felhasználó sikeresen létrehozva', 'success')
    }

    closeModal()
    await loadUsers()
  } catch (err) {
    error.value = err.response?.data?.message || 'A felhasználó mentése sikertelen'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingUser.value = null
  resetForm()
  error.value = ''
}

// Lock body scroll when modal is open
useBodyScrollLock(showModal)

const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    role: 'user',
    password: '',
    isVerified: false,
    tax_number: '',
    bank_account: '',
    eu_tax_number: ''
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
  await loadUsers()
})
</script>

<style scoped>
.users-page {
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

.role-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.role-user {
  background: rgba(59, 130, 246, 0.2);
  color: #60a5fa;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.role-hotel {
  background: rgba(168, 85, 247, 0.2);
  color: #a78bfa;
  border: 1px solid rgba(168, 85, 247, 0.3);
}

.role-super_admin {
  background: rgba(239, 68, 68, 0.2);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.verified-badge {
  font-size: 1.2rem;
}

.verified-badge.verified {
  color: #22c55e;
}

.verified-badge.unverified {
  color: #f59e0b;
}

.twofa-badge {
  font-size: 1.2rem;
}

.twofa-badge.enabled {
  color: #667eea;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: rgba(20, 20, 20, 0.95);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
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
  border-bottom: 1px solid rgba(102, 126, 234, 0.2);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #e5e7eb;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: #9ca3af;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: background 0.2s;
}

.modal-close:hover {
  background: rgba(102, 126, 234, 0.1);
  color: #d1d5db;
}

.modal-body {
  padding: 1.5rem;
}

.form-section {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(102, 126, 234, 0.2);
}

.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #d1d5db;
  margin-bottom: 1rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #d1d5db;
  font-size: 0.9rem;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 8px;
  color: #e5e7eb;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  background: rgba(0, 0, 0, 0.5);
}

.switch {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
}

.switch input {
  width: auto;
}

.slider {
  position: relative;
  width: 44px;
  height: 24px;
  background: rgba(102, 126, 234, 0.3);
  border-radius: 12px;
  transition: background 0.3s;
}

.switch input:checked + .slider {
  background: #667eea;
}

.slider::before {
  content: '';
  position: absolute;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: white;
  top: 3px;
  left: 3px;
  transition: transform 0.3s;
}

.switch input:checked + .slider::before {
  transform: translateX(20px);
}

.switch-label {
  color: #d1d5db;
  font-size: 0.9rem;
}

.error-message {
  background: rgba(220, 38, 38, 0.2);
  border: 1px solid rgba(220, 38, 38, 0.4);
  color: #fca5a5;
  padding: 0.875rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
}

.modal-footer {
  padding-top: 1.5rem;
  border-top: 1px solid rgba(102, 126, 234, 0.2);
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: transparent;
  color: #9ca3af;
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  border-color: rgba(102, 126, 234, 0.6);
  color: #d1d5db;
  background: rgba(102, 126, 234, 0.1);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-icon {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  transition: background 0.2s;
}

.btn-edit:hover {
  background: rgba(59, 130, 246, 0.2);
}

.btn-delete:hover {
  background: rgba(239, 68, 68, 0.2);
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.input-wrapper-password {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  z-index: 4;
  color: #667eea;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  border-radius: 4px;
}

.password-toggle:hover {
  background: rgba(102, 126, 234, 0.1);
  color: #764ba2;
}

.password-toggle:focus {
  outline: none;
  background: rgba(102, 126, 234, 0.15);
}
</style>
