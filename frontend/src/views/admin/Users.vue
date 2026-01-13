<template>
  <AdminLayout>
    <div class="users-page">
      <div class="page-header">
        <h1>My Profile</h1>
        <p class="page-subtitle">Manage your account information and invoice details</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading profile...</p>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="error-message">{{ error }}</div>

      <!-- Success Message -->
      <div v-if="successMessage" class="success-message">{{ successMessage }}</div>

      <!-- Profile Form -->
      <div v-if="!loading && userData" class="profile-card">
        <form @submit.prevent="handleSubmit" class="profile-form">
          <div class="form-section">
            <h3 class="section-title">Personal Information</h3>
            
            <div class="form-group">
              <label>Full Name *</label>
              <input v-model="form.name" type="text" required placeholder="Enter full name" />
            </div>

            <div class="form-group">
              <label>Email Address *</label>
              <input v-model="form.email" type="email" required placeholder="Enter email address" />
            </div>

            <div class="form-group">
              <label>Role</label>
              <input v-model="form.role" type="text" disabled />
              <small class="form-hint">Role cannot be changed</small>
            </div>

            <div class="form-group">
              <label>New Password</label>
              <input 
                v-model="form.password" 
                type="password" 
                placeholder="Leave blank to keep current password"
                :minlength="8"
              />
              <small class="form-hint">Minimum 8 characters. Leave blank to keep current password.</small>
            </div>
          </div>

          <div class="form-section">
            <h3 class="section-title">Invoice Information</h3>
            <p class="section-description">These fields are used for invoice generation</p>
            
            <div class="form-group">
              <label>Tax Number (Adószám)</label>
              <input v-model="form.tax_number" type="text" placeholder="Enter tax number" />
            </div>

            <div class="form-group">
              <label>Bank Account (Bankszámlaszám)</label>
              <input v-model="form.bank_account" type="text" placeholder="Enter bank account number" />
            </div>

            <div class="form-group">
              <label>EU Tax Number (Közösségi adószám)</label>
              <input v-model="form.eu_tax_number" type="text" placeholder="Enter EU tax number (optional)" />
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>

      <Toast ref="toast" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import Toast from '../../components/Toast.vue'
import { adminService } from '../../services/adminService'
import { authService } from '../../services/authService'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const userData = ref(null)
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const successMessage = ref('')
const toast = ref(null)

const form = ref({
  name: '',
  email: '',
  role: '',
  password: '',
  tax_number: '',
  bank_account: '',
  eu_tax_number: ''
})

const loadUserData = async () => {
  loading.value = true
  try {
    // Fetch complete user data from API to get invoice fields
    const userDataFromApi = await authService.getMe()
    userData.value = userDataFromApi
    
    // Populate form with complete user data including invoice fields
    form.value = {
      name: userDataFromApi.name || '',
      email: userDataFromApi.email || '',
      role: userDataFromApi.role || '',
      password: '',
      tax_number: userDataFromApi.tax_number || '',
      bank_account: userDataFromApi.bank_account || '',
      eu_tax_number: userDataFromApi.eu_tax_number || ''
    }
    
    // Also update auth store with complete data
    if (authStore.state.user) {
      authStore.state.user.tax_number = userDataFromApi.tax_number
      authStore.state.user.bank_account = userDataFromApi.bank_account
      authStore.state.user.eu_tax_number = userDataFromApi.eu_tax_number
      localStorage.setItem('auth_user', JSON.stringify(authStore.state.user))
    }
  } catch (err) {
    showToast('Failed to load profile data', 'error')
    // Fallback to auth store data if API fails
    if (authStore.state.user) {
      userData.value = authStore.state.user
      form.value = {
        name: authStore.state.user.name || '',
        email: authStore.state.user.email || '',
        role: authStore.state.user.role || '',
        password: '',
        tax_number: authStore.state.user.tax_number || '',
        bank_account: authStore.state.user.bank_account || '',
        eu_tax_number: authStore.state.user.eu_tax_number || ''
      }
    }
  } finally {
    loading.value = false
  }
}

const handleSubmit = async () => {
  saving.value = true
  error.value = ''
  successMessage.value = ''

  try {
    const updateData = {
      name: form.value.name,
      email: form.value.email,
      tax_number: form.value.tax_number || null,
      bank_account: form.value.bank_account || null,
      eu_tax_number: form.value.eu_tax_number || null
    }

    // Only include password if it's provided
    if (form.value.password && form.value.password.length > 0) {
      if (form.value.password.length < 8) {
        error.value = 'Password must be at least 8 characters long'
        saving.value = false
        return
      }
      updateData.password = form.value.password
    }

    const updatedUser = await adminService.updateAdminProfile(updateData)
    
    // Update auth store with new data
    if (authStore.state.user) {
      authStore.state.user.name = updatedUser.name
      authStore.state.user.email = updatedUser.email
      authStore.state.user.tax_number = updatedUser.tax_number
      authStore.state.user.bank_account = updatedUser.bank_account
      authStore.state.user.eu_tax_number = updatedUser.eu_tax_number
      localStorage.setItem('auth_user', JSON.stringify(authStore.state.user))
    }
    
    // Update form with saved values (especially invoice fields)
    form.value.tax_number = updatedUser.tax_number || ''
    form.value.bank_account = updatedUser.bank_account || ''
    form.value.eu_tax_number = updatedUser.eu_tax_number || ''
    userData.value = updatedUser

    successMessage.value = 'Profile updated successfully!'
    showToast('Profile updated successfully!', 'success')
    
    // Clear password field
    form.value.password = ''
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update profile'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
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
  await loadUserData()
})
</script>

<style scoped>
.users-page {
  max-width: 900px;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  color: #6b7280;
  font-size: 1rem;
  margin: 0;
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
  background: #fee2e2;
  color: #dc2626;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  border: 1px solid #fecaca;
  font-size: 0.875rem;
}

.success-message {
  background: #d1fae5;
  color: #065f46;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  border: 1px solid #a7f3d0;
  font-size: 0.875rem;
}

.profile-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 2rem;
}

.profile-form {
  display: flex;
  flex-direction: column;
}

.form-section {
  margin-bottom: 2.5rem;
}

.form-section:last-of-type {
  margin-bottom: 0;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e0e0e0;
}

.section-description {
  color: #6b7280;
  font-size: 0.875rem;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
  font-size: 0.9rem;
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: border-color 0.2s, box-shadow 0.2s;
  background: #f9fafb;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  background: white;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input:disabled {
  background: #f3f4f6;
  cursor: not-allowed;
}

.form-hint {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.8rem;
  color: #6b7280;
}

.form-actions {
  padding-top: 1.5rem;
  border-top: 1px solid #e0e0e0;
  display: flex;
  justify-content: flex-end;
  margin-top: 2rem;
}

.btn-primary {
  padding: 0.75rem 2rem;
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
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
