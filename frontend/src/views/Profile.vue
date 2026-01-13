<template>
  <div class="profile-page">
    <!-- Home Button -->
    <router-link to="/" class="home-button">
      <span class="home-icon">🏠</span>
      <span class="home-text">Home</span>
    </router-link>
    
    <!-- Profile Card -->
    <div class="profile-container" >
      <div class="profile-header">
        <div class="travel-icon-wrapper">
          <div class="travel-icon">👤</div>
        </div>
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
import { ref, onMounted, onUnmounted } from 'vue'
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

// Slideshow
const slideshowImages = [
  'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&auto=format&fit=crop'
]

const currentSlide = ref(0)
let slideshowInterval = null

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

  // Auto-advance slideshow every 5 seconds
  slideshowInterval = setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % slideshowImages.length
  }, 5000)
})

onUnmounted(() => {
  if (slideshowInterval) {
    clearInterval(slideshowInterval)
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
  min-height: 100vh;
  height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 0;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow-y: auto;
}

/* Home Button */
.home-button {
  position: absolute;
  top: 1.5rem;
  left: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  color: #667eea;
  text-decoration: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: 100;
}

.home-button:hover {
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  color: #764ba2;
}

.home-icon {
  font-size: 1.2rem;
}

.home-text {
  display: none;
}

@media (min-width: 769px) {
  .home-text {
    display: inline;
  }
}

.profile-container {
  flex: 1;
  max-width: 550px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  max-height: 85vh;
  overflow-y: auto;
}

.profile-header {
  padding: 1.75rem 1.5rem 1rem;
  text-align: center;
  background: white;
}

.travel-icon-wrapper {
  display: inline-block;
  margin-bottom: 0.75rem;
}

.travel-icon {
  font-size: 2rem;
}

.profile-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0.5rem 0;
}

.profile-subtitle {
  color: #6b7280;
  font-size: 0.9rem;
  font-weight: 500;
  margin-top: 0.25rem;
}

.profile-form-container {
  padding: 0 1.5rem 1.75rem;
}

.form-section {
  margin-bottom: 1.75rem;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.75rem;
}

.section-description {
  color: #6b7280;
  font-size: 0.875rem;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
  background: #f9fafb;
  color: #1f2937;
}

.form-input::placeholder {
  color: #9ca3af;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
  background: white;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 2px solid #e8f4f8;
}

.btn-cancel {
  padding: 0.75rem 1.5rem;
  background: white;
  color: #667eea;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel:hover {
  background: #f9fafb;
  border-color: #d1d5db;
  transform: translateY(-1px);
}

.btn-submit {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-1px);
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
  background: #fee2e2;
  color: #dc2626;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  margin: 0 2rem 1.5rem;
  border: 1px solid #fecaca;
  font-size: 0.875rem;
  font-weight: 500;
}

.success-message {
  background: #d1fae5;
  color: #065f46;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  margin: 0 2rem 1.5rem;
  border: 1px solid #a7f3d0;
  font-size: 0.875rem;
  font-weight: 500;
}

/* Slideshow Section */
.profile-slideshow {
  flex: 1;
  max-width: 450px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

.slideshow-container {
  position: relative;
  width: 100%;
  max-height: 500px;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.slide {
  display: none;
  width: 100%;
  height: 100%;
}

.slide.active {
  display: block;
  animation: fadeIn 1s ease-in-out;
}

.slide img {
  width: 100%;
  height: auto;
  max-height: 600px;
  object-fit: cover;
  display: block;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.slideshow-dots {
  position: absolute;
  bottom: 1rem;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 0.5rem;
  z-index: 10;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  transition: all 0.3s ease;
}

.dot:hover {
  background: rgba(255, 255, 255, 0.8);
}

.dot.active {
  background: white;
  width: 24px;
  border-radius: 5px;
}

@media (max-width: 768px) {
  .profile-page {
    flex-direction: column;
    padding: 1rem;
  }

  .home-button {
    top: 1rem;
    left: 1rem;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
  }

  .profile-container {
    max-width: 100%;
    order: 1;
  }

  .profile-header {
    padding: 2rem 1.5rem 1rem;
  }

  .profile-header h1 {
    font-size: 1.5rem;
  }

  .profile-form-container {
    padding: 0 1.25rem 1.5rem;
  }

  .profile-slideshow {
    max-width: 100%;
    padding: 1rem;
    order: 2;
  }

  .slideshow-container {
    max-height: 300px;
  }

  .slide img {
    max-height: 300px;
  }

  .form-actions {
    flex-direction: column;
  }

  .btn-cancel,
  .btn-submit {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .profile-page {
    flex-direction: column;
    padding: 1rem;
  }

  .home-button {
    top: 1rem;
    left: 1rem;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
  }

  .profile-container {
    max-width: 100%;
    order: 1;
  }

  .profile-header {
    padding: 1.5rem 1.25rem 0.75rem;
  }

  .profile-header h1 {
    font-size: 1.375rem;
  }

  .profile-form-container {
    padding: 0 1.25rem 1.5rem;
  }

  .profile-slideshow {
    max-width: 100%;
    padding: 0.5rem;
    order: 2;
  }

  .slideshow-container {
    max-height: 250px;
  }

  .slide img {
    max-height: 250px;
  }
}
</style>
