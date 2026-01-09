<template>
  <div class="profile-page">
    <div class="profile-container">
      <div class="profile-header">
        <h1>My Profile</h1>
        <p class="profile-subtitle">Manage your account information</p>
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
      <div v-if="!loading && authStore.state.user" class="profile-form-container">
        <form @submit.prevent="handleSubmit" class="profile-form">
          <div class="form-section">
            <h2 class="section-title">Personal Information</h2>
            
            <div class="form-group">
              <label for="name">Full Name</label>
              <input
                id="name"
                v-model="formData.name"
                type="text"
                class="form-input"
                placeholder="Enter your full name"
                required
              />
            </div>

            <div class="form-group">
              <label for="email">Email Address</label>
              <input
                id="email"
                v-model="formData.email"
                type="email"
                class="form-input"
                placeholder="Enter your email"
                required
              />
            </div>
          </div>

          <div class="form-section">
            <h2 class="section-title">Change Password</h2>
            <p class="section-description">Leave blank if you don't want to change your password</p>
            
            <div class="form-group">
              <label for="password">New Password</label>
              <input
                id="password"
                v-model="formData.password"
                type="password"
                class="form-input"
                placeholder="Enter new password (min. 8 characters)"
                :minlength="8"
              />
            </div>

            <div class="form-group">
              <label for="confirmPassword">Confirm New Password</label>
              <input
                id="confirmPassword"
                v-model="formData.confirmPassword"
                type="password"
                class="form-input"
                placeholder="Confirm new password"
                :minlength="8"
              />
            </div>
          </div>

          <div class="form-actions">
            <button type="button" @click="handleCancel" class="btn-cancel">
              Cancel
            </button>
            <button type="submit" class="btn-submit" :disabled="saving">
              <span v-if="saving">Saving...</span>
              <span v-else>Save Changes</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const showToast = (message, type) => {
  if (window.showToast) {
    window.showToast(message, type)
  }
}

const loading = ref(false)
const saving = ref(false)
const error = ref('')
const successMessage = ref('')

const formData = ref({
  name: '',
  email: '',
  password: '',
  confirmPassword: ''
})

onMounted(() => {
  if (!authStore.state.isAuthenticated) {
    router.push('/login')
    return
  }

  // Load current user data
  if (authStore.state.user) {
    formData.value = {
      name: authStore.state.user.name || '',
      email: authStore.state.user.email || '',
      password: '',
      confirmPassword: ''
    }
  }
})

const handleSubmit = async () => {
  error.value = ''
  successMessage.value = ''

  // Validate password if provided
  if (formData.value.password) {
    if (formData.value.password.length < 8) {
      error.value = 'Password must be at least 8 characters long'
      return
    }
    if (formData.value.password !== formData.value.confirmPassword) {
      error.value = 'Passwords do not match'
      return
    }
  }

  saving.value = true

  try {
    const updateData = {
      name: formData.value.name,
      email: formData.value.email
    }

    // Only include password if it's provided
    if (formData.value.password) {
      updateData.password = formData.value.password
    }

    const result = await authStore.updateUser(authStore.state.user.id, updateData)

    if (result.success) {
      successMessage.value = 'Profile updated successfully!'
      showToast('Profile updated successfully!', 'success')
      
      // Clear password fields
      formData.value.password = ''
      formData.value.confirmPassword = ''

      // Redirect after a short delay
      setTimeout(() => {
        router.push('/search')
      }, 1500)
    } else {
      error.value = result.message || 'Failed to update profile'
      showToast(result.message || 'Failed to update profile', 'error')
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'An error occurred while updating your profile'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const handleCancel = () => {
  router.push('/search')
}
</script>

<style scoped>
.profile-page {
  min-height: calc(100vh - 70px);
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile-container {
  max-width: 700px;
  width: 100%;
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}

.profile-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2.5rem 2rem;
  text-align: center;
}

.profile-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.profile-subtitle {
  font-size: 1.1rem;
  opacity: 0.9;
}

.profile-form-container {
  padding: 2.5rem 2rem;
}

.form-section {
  margin-bottom: 2.5rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 0.75rem;
}

.section-description {
  color: #7f8c8d;
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.form-input {
  width: 100%;
  padding: 0.875rem 1.25rem;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: #f8f9fa;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
  background: white;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #e8f4f8;
}

.btn-cancel {
  padding: 0.875rem 2rem;
  background: white;
  color: #667eea;
  border: 2px solid #667eea;
  border-radius: 10px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel:hover {
  background: #f8f9ff;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(102, 126, 234, 0.2);
}

.btn-submit {
  padding: 0.875rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-submit:disabled {
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
  margin: 1.5rem 2rem;
  border-left: 4px solid #c33;
  font-weight: 500;
}

.success-message {
  background: #efe;
  color: #3c3;
  padding: 1rem 1.5rem;
  border-radius: 10px;
  margin: 1.5rem 2rem;
  border-left: 4px solid #3c3;
  font-weight: 500;
}

@media (max-width: 768px) {
  .profile-page {
    padding: 1rem;
  }

  .profile-container {
    border-radius: 16px;
  }

  .profile-header {
    padding: 2rem 1.5rem;
  }

  .profile-header h1 {
    font-size: 2rem;
  }

  .profile-form-container {
    padding: 2rem 1.5rem;
  }

  .form-actions {
    flex-direction: column;
  }

  .btn-cancel,
  .btn-submit {
    width: 100%;
  }
}
</style>
