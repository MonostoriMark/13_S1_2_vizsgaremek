<template>
  <div class="forgot-password-page">
    <!-- Home Button -->
    <router-link to="/" class="home-button">
      <span class="home-icon">🏠</span>
      <span class="home-text">Home</span>
    </router-link>
    
    <!-- Card -->
    <div class="forgot-password-card">
      <div class="card-content">
        <div class="welcome-header">
          <div class="travel-icon-wrapper">
            <div class="travel-icon">🔐</div>
          </div>
          <h1>Forgot Password</h1>
          <p class="welcome-subtitle">Enter your email to receive a password reset link</p>
        </div>

        <div v-if="error" class="error-message">
          <span class="error-icon">⚠️</span>
          <div class="error-text">{{ error }}</div>
        </div>

        <div v-if="successMessage" class="success-message">
          <span class="success-icon">✓</span>
          <div class="success-text">{{ successMessage }}</div>
        </div>

        <form @submit.prevent="handleSubmit" class="forgot-password-form" v-if="!successMessage">
          <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-wrapper">
              <span class="input-icon">✉️</span>
              <input
                id="email"
                v-model="email"
                type="email"
                required
                placeholder="thisuix@mail.com"
                class="glass-input"
              />
              <div class="input-glow"></div>
            </div>
          </div>

          <button type="submit" class="btn-submit" :disabled="loading">
            <span v-if="loading" class="loading-spinner"></span>
            <span v-else>{{ loading ? 'Sending...' : 'SEND RESET LINK' }}</span>
            <div class="button-glow"></div>
          </button>

          <p class="back-link">
            Remember your password? <router-link to="/login">Back to Login</router-link>
          </p>
        </form>
      </div>
    </div>
    
    <!-- Slideshow Section -->
    <div class="forgot-password-slideshow">
      <div class="slideshow-container">
        <div 
          v-for="(image, index) in slideshowImages" 
          :key="index"
          class="slide"
          :class="{ active: currentSlide === index }"
        >
          <img :src="image" :alt="`Hotel ${index + 1}`" />
        </div>
        <!-- Navigation dots -->
        <div class="slideshow-dots">
          <span 
            v-for="(image, index) in slideshowImages" 
            :key="index"
            class="dot"
            :class="{ active: currentSlide === index }"
            @click="currentSlide = index"
          ></span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { authService } from '../services/authService'

const email = ref('')
const error = ref('')
const successMessage = ref('')
const loading = ref(false)

// Slideshow
const slideshowImages = [
  'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&auto=format&fit=crop'
]

const currentSlide = ref(0)
let slideshowInterval = null

onMounted(() => {
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
  loading.value = true

  try {
    const result = await authService.requestPasswordReset(email.value)
    
    if (result.success || result.message) {
      successMessage.value = result.message || 'Password reset link has been sent to your email address. Please check your inbox.'
      if (window.showToast) {
        window.showToast(successMessage.value, 'success')
      }
    } else {
      error.value = result.message || 'Failed to send password reset link'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'An error occurred. Please try again later.'
    if (window.showToast) {
      window.showToast(error.value, 'error')
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.forgot-password-page {
  min-height: 100vh;
  height: 100vh;
  width: 100vw;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  margin: 0;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow-y: auto;
  gap: 2rem;
}

/* Home Button */
.home-button {
  position: absolute;
  top: 1.5rem;
  left: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: white;
  color: #667eea;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
  z-index: 100;
}

.home-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  color: #764ba2;
}

.home-icon {
  font-size: 1.1rem;
}

.home-text {
  display: none;
}

@media (min-width: 480px) {
  .home-text {
    display: inline;
  }
}

/* Card */
.forgot-password-card {
  flex: 1;
  max-width: 420px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  padding: 2.5rem 2rem;
}

.card-content {
  position: relative;
  z-index: 2;
}

/* Welcome Header */
.welcome-header {
  text-align: center;
  margin-bottom: 2rem;
}

.travel-icon-wrapper {
  display: inline-block;
  margin-bottom: 0.75rem;
}

.travel-icon {
  font-size: 2rem;
}

.welcome-header h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0.5rem 0;
}

.welcome-subtitle {
  color: #6b7280;
  font-size: 0.9rem;
  font-weight: 500;
}

/* Form Styles */
.forgot-password-form {
  width: 100%;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.input-wrapper {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1rem;
  z-index: 3;
  color: #9ca3af;
}

.glass-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.75rem;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.95rem;
  color: #1f2937;
  transition: all 0.2s ease;
}

.glass-input::placeholder {
  color: #9ca3af;
}

.glass-input:focus {
  outline: none;
  background: white;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.input-glow {
  display: none;
}

/* Button */
.btn-submit {
  width: 100%;
  padding: 0.875rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: 1.5rem;
  position: relative;
  overflow: hidden;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-submit:active:not(:disabled) {
  transform: translateY(0);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.button-glow {
  display: none;
}

.loading-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Back Link */
.back-link {
  text-align: center;
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0;
}

.back-link a {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s ease;
}

.back-link a:hover {
  color: #764ba2;
}

/* Error Message */
.error-message {
  background: #fee2e2;
  color: #dc2626;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
  border: 1px solid #fecaca;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.error-message .error-icon {
  font-size: 1.1rem;
}

.error-text {
  flex: 1;
}

/* Success Message */
.success-message {
  background: #d1fae5;
  color: #065f46;
  padding: 0.875rem 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
  border: 1px solid #a7f3d0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.success-message .success-icon {
  font-size: 1.1rem;
  font-weight: bold;
}

.success-text {
  flex: 1;
}

/* Slideshow Section */
.forgot-password-slideshow {
  flex: 1;
  max-width: 500px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.slideshow-container {
  position: relative;
  width: 100%;
  max-height: 600px;
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

/* Responsive Design */
@media (max-width: 768px) {
  .forgot-password-page {
    flex-direction: column;
    padding: 1rem;
  }

  .forgot-password-card {
    padding: 2rem 1.5rem;
    max-width: 100%;
    order: 1;
  }

  .welcome-header h1 {
    font-size: 1.5rem;
  }

  .forgot-password-slideshow {
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
}

@media (max-width: 480px) {
  .forgot-password-page {
    flex-direction: column;
    padding: 1rem;
  }

  .forgot-password-card {
    padding: 1.5rem 1.25rem;
    max-width: 100%;
    order: 1;
  }

  .welcome-header h1 {
    font-size: 1.375rem;
  }

  .forgot-password-slideshow {
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
