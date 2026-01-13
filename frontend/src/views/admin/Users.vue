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

            <div class="form-group">
              <div class="two-factor-toggle">
                <div class="toggle-info">
                  <label>Two-Factor Authentication</label>
                  <span class="toggle-status" :class="{ enabled: form.two_factor_enabled }">
                    {{ form.two_factor_enabled ? 'Enabled' : 'Disabled' }}
                  </span>
                </div>
                <label class="switch-toggle">
                  <input
                    v-model="form.two_factor_enabled"
                    type="checkbox"
                    @change="handle2FAToggle"
                    :disabled="twoFALoading"
                  />
                  <span class="slider-toggle"></span>
                </label>
                <p v-if="form.two_factor_enabled && authStore.state.user?.role === 'hotel'" class="twofa-required-note">
                  ⚠️ Hotel administrators cannot disable 2FA for security reasons.
                </p>
                <p v-if="!form.two_factor_enabled && authStore.state.user?.role === 'hotel'" class="twofa-required-note">
                  ⚠️ Hotel administrators must enable 2FA to access the admin panel. The setup modal will appear automatically.
                </p>
              </div>
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

          <div class="form-section danger-zone">
            <h3 class="section-title danger-title">Danger Zone</h3>
            <p class="section-description">Irreversible and destructive actions</p>
            
            <div class="form-group">
              <button @click="showDeleteAccountModal = true" class="btn-delete-account">
                <span class="delete-icon">🗑️</span>
                Delete My Account
              </button>
              <p class="delete-warning">Once you delete your account, there is no going back. Please be certain.</p>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>

              <!-- 2FA Setup Modal -->
      <Transition name="modal">
        <div v-if="show2FASetup" class="modal-overlay" :class="{ 'non-dismissible': authStore.state.user?.role === 'hotel' && !(form.two_factor_enabled || authStore.state.user?.two_factor_enabled) }" @click.self="handleModalClick">
          <div class="modal-content-2fa">
            <div class="modal-header-2fa">
              <h3>Set Up Two-Factor Authentication</h3>
              <button v-if="!(authStore.state.user?.role === 'hotel' && !(form.two_factor_enabled || authStore.state.user?.two_factor_enabled))" class="modal-close" @click="close2FASetup">×</button>
            </div>
            <div class="modal-body-2fa">
              <div v-if="twoFAError" class="error-message">{{ twoFAError }}</div>
              <div v-if="twoFASuccess" class="success-message">{{ twoFASuccess }}</div>
              
              <div v-if="!twoFAQRCode" class="setup-instructions">
                <p>Click the button below to generate a QR code for your authenticator app.</p>
                <button @click="generate2FAQR" class="btn-generate-qr" :disabled="twoFALoading">
                  {{ twoFALoading ? 'Generating...' : 'Generate QR Code' }}
                </button>
              </div>
              
              <div v-if="twoFAQRCode" class="qr-setup">
                <div class="qr-container">
                  <img :src="twoFAQRCode" alt="QR Code" class="qr-code-image" />
                </div>
                <div class="secret-info">
                  <p class="info-text">Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)</p>
                  <div class="secret-box">
                    <label>Secret Key:</label>
                    <div class="secret-value">{{ twoFASecret }}</div>
                    <button @click="copySecret" class="btn-copy">📋 Copy</button>
                  </div>
                </div>
                <div class="form-group">
                  <label>Enter 6-digit code from your app</label>
                  <input
                    v-model="twoFACode"
                    type="text"
                    maxlength="6"
                    placeholder="000000"
                    class="code-input"
                    @input="format2FACode"
                  />
                </div>
                <button @click="verifyAndEnable2FA" class="btn-verify-2fa" :disabled="twoFACode.length !== 6 || twoFALoading">
                  {{ twoFALoading ? 'Verifying...' : 'Verify & Enable' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Disable 2FA Modal -->
      <Transition name="modal">
        <div v-if="show2FADisable" class="modal-overlay" @click.self="close2FADisable">
          <div class="modal-content-2fa">
            <div class="modal-header-2fa">
              <h3>Disable Two-Factor Authentication</h3>
              <button class="modal-close" @click="close2FADisable">×</button>
            </div>
            <div class="modal-body-2fa">
              <div v-if="twoFAError" class="error-message">{{ twoFAError }}</div>
              <p class="warning-text">⚠️ Disabling 2FA will reduce the security of your account.</p>
              <div class="form-group">
                <label>Enter your password to confirm</label>
                <input
                  v-model="disablePassword"
                  type="password"
                  placeholder="Enter your password"
                  class="form-input"
                />
              </div>
              <div class="modal-footer-2fa">
                <button @click="close2FADisable" class="btn-secondary">Cancel</button>
                <button @click="confirmDisable2FA" class="btn-danger" :disabled="!disablePassword || twoFALoading">
                  {{ twoFALoading ? 'Disabling...' : 'Disable 2FA' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Delete Account Modal -->
      <Transition name="modal">
        <div v-if="showDeleteAccountModal" class="modal-overlay" @click.self="closeDeleteAccountModal">
          <div class="modal-content-2fa">
            <div class="modal-header-2fa">
              <h3>Delete Account</h3>
              <button class="modal-close" @click="closeDeleteAccountModal">×</button>
            </div>
            <div class="modal-body-2fa">
              <div v-if="deleteAccountError" class="error-message">{{ deleteAccountError }}</div>
              <p class="warning-text">⚠️ This action cannot be undone. This will permanently delete your account and remove all of your data from our servers.</p>
              <div class="form-group">
                <label>Enter your password to confirm</label>
                <input
                  v-model="deleteAccountPassword"
                  type="password"
                  placeholder="Enter your password"
                  class="form-input"
                />
              </div>
              <div class="modal-footer-2fa">
                <button @click="closeDeleteAccountModal" class="btn-secondary">Cancel</button>
                <button @click="confirmDeleteAccount" class="btn-danger" :disabled="!deleteAccountPassword || deletingAccount">
                  {{ deletingAccount ? 'Deleting...' : 'Delete Account' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <Toast ref="toast" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AdminLayout from '../../layouts/AdminLayout.vue'
import Toast from '../../components/Toast.vue'
import { adminService } from '../../services/adminService'
import { authService } from '../../services/authService'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()

const authStore = useAuthStore()
const userData = ref(null)
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const successMessage = ref('')
const toast = ref(null)

// 2FA state
const show2FASetup = ref(false)
const twoFALoading = ref(false)
const twoFAError = ref('')
const twoFASuccess = ref('')
const twoFAQRCode = ref('')
const twoFASecret = ref('')
const twoFACode = ref('')
const disablePassword = ref('')
const show2FADisable = ref(false)

// Account Deletion
const showDeleteAccountModal = ref(false)
const deleteAccountPassword = ref('')
const deletingAccount = ref(false)
const deleteAccountError = ref('')

const form = ref({
  name: '',
  email: '',
  role: '',
  password: '',
  tax_number: '',
  bank_account: '',
  eu_tax_number: '',
  two_factor_enabled: false
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
                  eu_tax_number: userDataFromApi.eu_tax_number || '',
                  two_factor_enabled: userDataFromApi.two_factor_enabled || false
                }
    
    // Also update auth store with complete data
    if (authStore.state.user) {
      authStore.state.user.tax_number = userDataFromApi.tax_number
      authStore.state.user.bank_account = userDataFromApi.bank_account
      authStore.state.user.eu_tax_number = userDataFromApi.eu_tax_number
      authStore.state.user.two_factor_enabled = userDataFromApi.two_factor_enabled || false
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
        eu_tax_number: authStore.state.user.eu_tax_number || '',
        two_factor_enabled: authStore.state.user.two_factor_enabled || false
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

// 2FA Methods
const handle2FAToggle = async () => {
  // When toggle is turned ON (enabling 2FA)
  if (form.value.two_factor_enabled) {
    // Enable 2FA - show setup modal
    show2FASetup.value = true
    twoFAQRCode.value = ''
    twoFASecret.value = ''
    twoFACode.value = ''
    twoFAError.value = ''
    twoFASuccess.value = ''
    
    // For hotel admins, auto-generate QR code
    if (authStore.state.user?.role === 'hotel' && !twoFAQRCode.value) {
      await generate2FAQR()
    }
  } else {
    // When toggle is turned OFF (disabling 2FA)
    // Hotel admins cannot disable 2FA
    if (authStore.state.user?.role === 'hotel') {
      showToast('Hotel administrators cannot disable 2FA for security reasons', 'error')
      form.value.two_factor_enabled = true
      return
    }
    // Disable 2FA (for regular users only)
    show2FADisable.value = true
    disablePassword.value = ''
    twoFAError.value = ''
  }
}

const generate2FAQR = async () => {
  twoFALoading.value = true
  twoFAError.value = ''
  twoFASuccess.value = ''
  
  try {
    const data = await authService.enable2FA()
    twoFAQRCode.value = data.qr_code
    twoFASecret.value = data.two_factor_secret
  } catch (err) {
    twoFAError.value = err.response?.data?.message || 'Failed to generate QR code'
    form.value.two_factor_enabled = false
  } finally {
    twoFALoading.value = false
  }
}

const format2FACode = (e) => {
  twoFACode.value = e.target.value.replace(/\D/g, '').slice(0, 6)
}

const copySecret = () => {
  navigator.clipboard.writeText(twoFASecret.value)
  twoFASuccess.value = 'Secret copied to clipboard!'
  setTimeout(() => {
    twoFASuccess.value = ''
  }, 2000)
}

const verifyAndEnable2FA = async () => {
  if (twoFACode.value.length !== 6) {
    twoFAError.value = 'Please enter a 6-digit code'
    return
  }

  twoFALoading.value = true
  twoFAError.value = ''
  twoFASuccess.value = ''

  try {
    await authService.verifyAndEnable2FA(twoFACode.value)
    twoFASuccess.value = '2FA enabled successfully!'
    showToast('2FA enabled successfully!', 'success')
    
    // Update auth store
    if (authStore.state.user) {
      authStore.state.user.two_factor_enabled = true
      localStorage.setItem('auth_user', JSON.stringify(authStore.state.user))
    }
    
    // Reload user data to update 2FA status
    await loadUserData()
    
    // Update auth store immediately
    if (authStore.state.user) {
      authStore.state.user.two_factor_enabled = true
      localStorage.setItem('auth_user', JSON.stringify(authStore.state.user))
    }
    
    // Update form and auth store to reflect enabled state
    form.value.two_factor_enabled = true
    if (authStore.state.user) {
      authStore.state.user.two_factor_enabled = true
      localStorage.setItem('auth_user', JSON.stringify(authStore.state.user))
    }
    
    setTimeout(() => {
      close2FASetup()
      // Reload to remove blocker and allow full access
      window.location.reload()
    }, 1500)
  } catch (err) {
    twoFAError.value = err.response?.data?.message || 'Invalid code. Please try again.'
    form.value.two_factor_enabled = false
  } finally {
    twoFALoading.value = false
  }
}

const confirmDisable2FA = async () => {
  if (!disablePassword.value) {
    twoFAError.value = 'Please enter your password'
    return
  }

  twoFALoading.value = true
  twoFAError.value = ''

  try {
    await authService.disable2FA(disablePassword.value)
    twoFASuccess.value = '2FA disabled successfully!'
    showToast('2FA disabled successfully!', 'success')
    
    // Update auth store
    if (authStore.state.user) {
      authStore.state.user.two_factor_enabled = false
      localStorage.setItem('auth_user', JSON.stringify(authStore.state.user))
    }
    
    form.value.two_factor_enabled = false
    
    setTimeout(() => {
      close2FADisable()
      loadUserData()
    }, 1500)
  } catch (err) {
    twoFAError.value = err.response?.data?.message || 'Failed to disable 2FA'
    form.value.two_factor_enabled = true
  } finally {
    twoFALoading.value = false
  }
}

const handleModalClick = () => {
  // Prevent closing if hotel admin and 2FA not enabled
  const has2FA = form.value.two_factor_enabled || authStore.state.user?.two_factor_enabled
  if (authStore.state.user?.role === 'hotel' && !has2FA) {
    return
  }
  close2FASetup()
}

const close2FASetup = () => {
  // Don't allow closing if hotel admin hasn't enabled 2FA
  const has2FA = form.value.two_factor_enabled || authStore.state.user?.two_factor_enabled
  if (authStore.state.user?.role === 'hotel' && !has2FA) {
    return
  }
  show2FASetup.value = false
  twoFAQRCode.value = ''
  twoFASecret.value = ''
  twoFACode.value = ''
  twoFAError.value = ''
  twoFASuccess.value = ''
  // Reset toggle if 2FA wasn't actually enabled
  if (!form.value.two_factor_enabled && !authStore.state.user?.two_factor_enabled) {
    form.value.two_factor_enabled = false
  }
}

const close2FADisable = () => {
  show2FADisable.value = false
  disablePassword.value = ''
  twoFAError.value = ''
  form.value.two_factor_enabled = true
}

// Account Deletion
const confirmDeleteAccount = async () => {
  if (!deleteAccountPassword.value) {
    deleteAccountError.value = 'Please enter your password'
    return
  }

  deletingAccount.value = true
  deleteAccountError.value = ''

  try {
    await authService.deleteAccount(authStore.state.user.id, deleteAccountPassword.value)
    showToast('Account deleted successfully', 'success')
    
    // Logout and redirect
    await authStore.logout()
    setTimeout(() => {
      router.push('/login')
    }, 1500)
  } catch (err) {
    deleteAccountError.value = err.response?.data?.message || 'Failed to delete account'
    showToast(deleteAccountError.value, 'error')
  } finally {
    deletingAccount.value = false
  }
}

const closeDeleteAccountModal = () => {
  showDeleteAccountModal.value = false
  deleteAccountPassword.value = ''
  deleteAccountError.value = ''
}

onMounted(async () => {
  await loadUserData()
  
  // Auto-open 2FA setup modal for hotel admins if 2FA is not enabled
  // Check both form value and auth store value
  const has2FA = form.value.two_factor_enabled || authStore.state.user?.two_factor_enabled
  if (authStore.state.user?.role === 'hotel' && !has2FA) {
    // Small delay to ensure page is fully loaded
    setTimeout(async () => {
      show2FASetup.value = true
      // Auto-generate QR code
      await generate2FAQR()
    }, 300)
  }
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

/* 2FA Toggle Styles */
.two-factor-toggle {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.toggle-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.toggle-info label {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.95rem;
}

.toggle-status {
  font-size: 0.85rem;
  color: #6b7280;
}

.toggle-status.enabled {
  color: #22c55e;
  font-weight: 600;
}

.switch-toggle {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 26px;
}

.switch-toggle input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider-toggle {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: 0.3s;
  border-radius: 26px;
}

.slider-toggle:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
}

.switch-toggle input:checked + .slider-toggle {
  background-color: #667eea;
}

.switch-toggle input:checked + .slider-toggle:before {
  transform: translateX(24px);
}

.switch-toggle input:disabled + .slider-toggle {
  opacity: 0.5;
  cursor: not-allowed;
}

/* 2FA Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(6px);
}

.modal-overlay.non-dismissible {
  cursor: not-allowed;
}

.modal-overlay.non-dismissible .modal-content-2fa {
  cursor: default;
}

.modal-content-2fa {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow: auto;
}

.modal-header-2fa {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header-2fa h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #1f2937;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: #6b7280;
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
  background: #f3f4f6;
  color: #1f2937;
}

.modal-body-2fa {
  padding: 1.5rem;
}

.setup-instructions {
  text-align: center;
  margin-bottom: 1.5rem;
}

.setup-instructions p {
  color: #6b7280;
  margin-bottom: 1rem;
}

.btn-generate-qr {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-generate-qr:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-generate-qr:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.qr-setup {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.qr-container {
  display: flex;
  justify-content: center;
  padding: 1.5rem;
  background: #f9fafb;
  border-radius: 8px;
}

.qr-code-image {
  width: 200px;
  height: 200px;
  border-radius: 8px;
}

.secret-info {
  background: #f9fafb;
  border-radius: 8px;
  padding: 1rem;
}

.info-text {
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 1rem;
  text-align: center;
}

.secret-box {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.secret-box label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1f2937;
}

.secret-value {
  font-family: 'Courier New', monospace;
  background: white;
  padding: 0.75rem;
  border-radius: 6px;
  color: #667eea;
  font-size: 0.9rem;
  letter-spacing: 2px;
  border: 1px solid #e5e7eb;
}

.btn-copy {
  padding: 0.5rem 1rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  transition: background 0.2s;
}

.btn-copy:hover {
  background: #5568d3;
}

.code-input {
  width: 100%;
  padding: 1rem;
  background: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  color: #1f2937;
  font-size: 1.5rem;
  font-weight: 600;
  letter-spacing: 0.5rem;
  text-align: center;
  font-family: 'Courier New', monospace;
  transition: all 0.3s;
}

.code-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  background: white;
}

.btn-verify-2fa {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 8px;
  color: white;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-verify-2fa:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-verify-2fa:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.warning-text {
  background: #fef3c7;
  border: 1px solid #fbbf24;
  color: #92400e;
  padding: 0.875rem;
  border-radius: 8px;
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
}

.modal-footer-2fa {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: transparent;
  color: #6b7280;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  border-color: #d1d5db;
  color: #1f2937;
  background: #f9fafb;
}

.btn-danger {
  padding: 0.75rem 1.5rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-danger:hover:not(:disabled) {
  background: #dc2626;
}

.btn-danger:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.danger-zone {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 2px solid #fee2e2;
}

.danger-title {
  color: #dc2626 !important;
}

.btn-delete-account {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  border: none;
  border-radius: 12px;
  color: white;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
}

.btn-delete-account:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
}

.delete-icon {
  font-size: 1.25rem;
}

.delete-warning {
  margin-top: 0.75rem;
  color: #6b7280;
  font-size: 0.85rem;
  text-align: center;
}

.twofa-required-note {
  margin-top: 0.5rem;
  color: #dc2626;
  font-size: 0.85rem;
  font-weight: 500;
  text-align: center;
  padding: 0.5rem;
  background: rgba(254, 226, 226, 0.5);
  border-radius: 6px;
  border-left: 3px solid #dc2626;
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
