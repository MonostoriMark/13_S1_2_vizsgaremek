<template>
  <div class="register-page">
    <!-- Home Button -->
    <router-link to="/" class="home-button">
      <span class="home-icon">🏠</span>
      <span class="home-text">Home</span>
    </router-link>
    
    <!-- Minimal Card -->
    <div class="register-card">
      <div class="card-content">
        <!-- Step 1: Role Selection -->
        <div v-if="currentStep === 1" class="step-content">
          <div class="welcome-header">
            <div class="travel-icon-wrapper">
              <div class="travel-icon">✈️</div>
            </div>
            <h1>Welcome</h1>
            <p class="welcome-subtitle">Create Your Account</p>
          </div>

          <div class="role-selection-group">
            <label class="role-label">Please select your role</label>
            <p class="role-description">Choose the type of account you want to create</p>
            <div class="role-cards">
              <div
                class="role-card"
                :class="{ active: userType === 'user' }"
                @click="selectRole('user')"
              >
                <div class="role-icon">👤</div>
                <div class="role-name">Guest</div>
              </div>
              <div
                class="role-card"
                :class="{ active: userType === 'hotel' }"
                @click="selectRole('hotel')"
              >
                <div class="role-icon">🏨</div>
                <div class="role-name">Hotel Admin</div>
              </div>
            </div>
            <button
              type="button"
              class="btn-continue"
              :disabled="!userType"
              @click="nextStep"
            >
              Continue
            </button>
            <div class="step-indicators">
              <div class="step-dot active"></div>
              <div class="step-dot"></div>
            </div>
          </div>
        </div>

        <!-- Step 2: Registration Form -->
        <div v-if="currentStep === 2" class="step-content">
          <div class="welcome-header">
            <button type="button" class="btn-back" @click="prevStep">
              ← Back
            </button>
            <h1>Registration Details</h1>
            <p class="welcome-subtitle">Fill in your information</p>
          </div>

          <div v-if="error" class="error-message">
            <span class="error-icon">⚠️</span>
            {{ error }}
          </div>

          <form @submit.prevent="handleRegister" class="register-form">
            <div class="form-group">
              <label for="name">Full Name</label>
              <div class="input-wrapper">
                <span class="input-icon">👤</span>
                <input
                  id="name"
                  v-model="name"
                  type="text"
                  required
                  placeholder="Enter your name"
                  class="glass-input"
                />
                <div class="input-glow"></div>
              </div>
            </div>

            <div class="form-group">
              <label for="email">Email Id</label>
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

            <div class="form-group">
              <label for="password">Password</label>
              <div class="input-wrapper">
                <span class="input-icon">🔒</span>
                <input
                  id="password"
                  v-model="password"
                  type="password"
                  required
                  minlength="8"
                  placeholder="••••••••••"
                  class="glass-input"
                />
                <div class="input-glow"></div>
              </div>
            </div>

            <!-- Hotel-specific fields with smooth transition -->
            <Transition name="slide-fade">
              <div v-if="userType === 'hotel'" class="hotel-fields">
                <div class="form-group">
                  <label for="hotelName">Hotel Name</label>
                  <div class="input-wrapper">
                    <span class="input-icon">🏨</span>
                    <input
                      id="hotelName"
                      v-model="hotelName"
                      type="text"
                      required
                      placeholder="Enter hotel name"
                      class="glass-input"
                    />
                    <div class="input-glow"></div>
                  </div>
                </div>
                <div class="form-group">
                  <label for="location">Location</label>
                  <div class="input-wrapper">
                    <span class="input-icon">📍</span>
                    <input
                      id="location"
                      v-model="location"
                      type="text"
                      required
                      placeholder="City, Country"
                      class="glass-input"
                    />
                    <div class="input-glow"></div>
                  </div>
                </div>
                <div class="form-group">
                  <label for="type">Hotel Type</label>
                  <div class="input-wrapper">
                    <span class="input-icon">🏨</span>
                    <select id="type" v-model="type" required class="glass-input glass-select">
                      <option value="hotel">Hotel</option>
                      <option value="apartment">Apartment</option>
                      <option value="villa">Villa</option>
                      <option value="other">Other</option>
                    </select>
                    <div class="input-glow"></div>
                  </div>
                </div>
                <div class="form-group">
                  <label for="starRating">Star Rating (optional)</label>
                  <div class="input-wrapper">
                    <span class="input-icon">⭐</span>
                    <select id="starRating" v-model.number="starRating" class="glass-input glass-select">
                      <option :value="null">None</option>
                      <option value="1">1 Star</option>
                      <option value="2">2 Stars</option>
                      <option value="3">3 Stars</option>
                      <option value="4">4 Stars</option>
                      <option value="5">5 Stars</option>
                    </select>
                    <div class="input-glow"></div>
                  </div>
                </div>
              </div>
            </Transition>

          <!-- Hotel-specific fields with smooth transition -->
          <Transition name="slide-fade">
            <div v-if="userType === 'hotel'" class="hotel-fields">
              <div class="form-group">
                <label for="hotelName">Hotel Name</label>
                <div class="input-wrapper">
                  <span class="input-icon">🏨</span>
                  <input
                    id="hotelName"
                    v-model="hotelName"
                    type="text"
                    required
                    placeholder="Enter hotel name"
                    class="glass-input"
                  />
                  <div class="input-glow"></div>
                </div>
              </div>
              <div class="form-group">
                <label for="location">Location</label>
                <div class="input-wrapper">
                  <span class="input-icon">📍</span>
                  <input
                    id="location"
                    v-model="location"
                    type="text"
                    required
                    placeholder="City, Country"
                    class="glass-input"
                  />
                  <div class="input-glow"></div>
                </div>
              </div>
              <div class="form-group">
                <label for="type">Hotel Type</label>
                <div class="input-wrapper">
                  <span class="input-icon">🏨</span>
                  <select id="type" v-model="type" required class="glass-input glass-select">
                    <option value="hotel">Hotel</option>
                    <option value="apartment">Apartment</option>
                    <option value="villa">Villa</option>
                    <option value="other">Other</option>
                  </select>
                  <div class="input-glow"></div>
                </div>
              </div>
              <div class="form-group">
                <label for="starRating">Star Rating (optional)</label>
                <div class="input-wrapper">
                  <span class="input-icon">⭐</span>
                  <select id="starRating" v-model.number="starRating" class="glass-input glass-select">
                    <option :value="null">None</option>
                    <option value="1">1 Star</option>
                    <option value="2">2 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="5">5 Stars</option>
                  </select>
                  <div class="input-glow"></div>
                </div>
              </div>
            </div>
          </Transition>

            <button type="submit" class="btn-register" :disabled="loading">
              <span v-if="loading" class="loading-spinner"></span>
              <span v-else>{{ loading ? 'Registering...' : 'REGISTER' }}</span>
              <div class="button-glow"></div>
            </button>

            <p class="login-link">
              Already have account? <router-link to="/login">Login Now</router-link>
            </p>
          </form>
          <div class="step-indicators">
            <div class="step-dot"></div>
            <div class="step-dot active"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const currentStep = ref(1)
const name = ref('')
const email = ref('')
const password = ref('')
const userType = ref('')
const hotelName = ref('')
const location = ref('')
const type = ref('hotel')
const starRating = ref(null)
const error = ref('')
const loading = ref(false)

const selectRole = (role) => {
  userType.value = role
}

const nextStep = () => {
  if (userType.value) {
    currentStep.value = 2
  }
}

const prevStep = () => {
  currentStep.value = 1
}

const handleRegister = async () => {
  error.value = ''
  loading.value = true

  let result

  if (userType.value === 'user') {
    result = await authStore.registerUser(name.value, email.value, password.value)
  } else {
    if (!hotelName.value) {
      error.value = 'Hotel name is required for hotel registration'
      loading.value = false
      return
    }
    if (!location.value) {
      error.value = 'Location is required for hotel registration'
      loading.value = false
      return
    }
    result = await authStore.registerHotel(
      name.value,
      email.value,
      password.value,
      hotelName.value,
      location.value,
      type.value,
      starRating.value
    )
  }

  if (result.success) {
    if (result.requiresVerification) {
      // Show success message but don't redirect - user needs to verify email
      error.value = ''
      // Show info message instead
      if (window.showToast) {
        window.showToast(result.message || 'Regisztráció sikeres! Kérjük, erősítsd meg az e-mail címedet.', 'info')
      }
      // Redirect to login after a delay
      setTimeout(() => {
        router.push('/login')
      }, 3000)
    } else {
      if (authStore.state.user.role === 'user') {
        router.push('/bookings')
      } else if (authStore.state.user.role === 'hotel') {
        router.push('/admin/bookings')
      } else {
        router.push('/search')
      }
    }
  } else {
    error.value = result.message || 'Registration failed'
  }

  loading.value = false
}
</script>

<style scoped>
.register-page {
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

/* Minimal Card */
.register-card {
  width: 100%;
  max-width: 480px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  padding: 2.5rem 2rem;
  max-height: 90vh;
  overflow-y: auto;
}

.card-content {
  position: relative;
  z-index: 2;
}

/* Welcome Header */
.welcome-header {
  text-align: center;
  margin-bottom: 1.75rem;
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
.register-form {
  width: 100%;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

/* Step Content */
.step-content {
  width: 100%;
}

.btn-back {
  background: none;
  border: none;
  color: #667eea;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  padding: 0.5rem 0;
  margin-bottom: 1rem;
  transition: color 0.2s ease;
}

.btn-back:hover {
  color: #764ba2;
}

/* Role Selection Cards */
.role-selection-group {
  margin-bottom: 1.5rem;
}

.role-label {
  text-align: center;
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
  display: block;
}

.role-description {
  text-align: center;
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 2rem;
}

.role-cards {
  display: flex;
  gap: 1rem;
  justify-content: center;
  align-items: stretch;
}

.role-card {
  flex: 1;
  max-width: 180px;
  min-height: 140px;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.role-card:hover {
  border-color: #d1d5db;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.role-card.active {
  border-color: #667eea;
  border-width: 2px;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.role-icon {
  font-size: 3rem;
  margin-bottom: 0.75rem;
  filter: grayscale(100%);
  opacity: 0.6;
  transition: all 0.2s ease;
}

.role-card.active .role-icon {
  filter: grayscale(0%);
  opacity: 1;
  transform: scale(1.05);
}

.role-name {
  font-size: 0.95rem;
  font-weight: 600;
  color: #9ca3af;
  text-align: center;
  transition: color 0.2s ease;
}

.role-card.active .role-name {
  color: #667eea;
}

/* Continue Button */
.btn-continue {
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
  margin-top: 2rem;
  margin-bottom: 1.5rem;
}

.btn-continue:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-continue:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

/* Step Indicators */
.step-indicators {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 1.5rem;
}

.step-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #e5e7eb;
  transition: all 0.2s ease;
}

.step-dot.active {
  background: #667eea;
  width: 24px;
  border-radius: 4px;
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

.glass-input,
.glass-select {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.75rem;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.95rem;
  color: #1f2937;
  transition: all 0.2s ease;
  appearance: none;
}

.glass-select {
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  padding-right: 3rem;
}

.glass-select option {
  color: #111827;
  background-color: #ffffff;
}

.glass-input::placeholder {
  color: #9ca3af;
}

.glass-input:focus,
.glass-select:focus {
  outline: none;
  background: white;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.input-glow {
  display: none;
}

/* Hotel Fields Transition */
.hotel-fields {
  margin-top: 0.5rem;
}

.slide-fade-enter-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(-20px);
  max-height: 0;
}

.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
  max-height: 0;
}

.slide-fade-enter-to,
.slide-fade-leave-from {
  opacity: 1;
  transform: translateY(0);
  max-height: 500px;
}

/* Button */
.btn-register {
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
  margin-top: 0.5rem;
  margin-bottom: 1.5rem;
  position: relative;
  overflow: hidden;
}

.btn-register:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-register:active:not(:disabled) {
  transform: translateY(0);
}

.btn-register:disabled {
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

/* Divider */
.divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin: 1.5rem 0;
  color: #9ca3af;
  font-size: 0.8rem;
  font-weight: 500;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid #e5e7eb;
}

.divider span {
  padding: 0 1rem;
  background: white;
}

/* Social Buttons */
.social-login {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.social-btn {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: 600;
  padding: 0;
  color: #374151;
}

.social-btn:hover {
  transform: translateY(-2px);
  border-color: #d1d5db;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.social-btn.google {
  color: #4285f4;
}

.social-btn.facebook {
  color: #1877f2;
}

.social-btn.apple {
  color: #000000;
}

/* Login Link */
.login-link {
  text-align: center;
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0;
}

.login-link a {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s ease;
}

.login-link a:hover {
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

.error-icon {
  font-size: 1.1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .register-page {
    padding: 1rem;
  }

  .register-card {
    padding: 2rem 1.5rem;
    max-height: 95vh;
    max-width: 100%;
  }

  .welcome-header h1 {
    font-size: 1.5rem;
  }

  .role-cards {
    gap: 0.75rem;
  }

  .role-card {
    max-width: 150px;
    min-height: 120px;
    padding: 1.25rem 0.75rem;
  }

  .role-icon {
    font-size: 2.5rem;
  }
}

@media (max-width: 480px) {
  .register-page {
    padding: 1rem;
  }

  .register-card {
    padding: 1.5rem 1.25rem;
    max-height: 95vh;
    max-width: 100%;
  }

  .welcome-header h1 {
    font-size: 1.375rem;
  }

  .role-cards {
    flex-direction: column;
    gap: 0.75rem;
  }

  .role-card {
    max-width: 100%;
    min-height: 100px;
    padding: 1rem;
  }

  .role-icon {
    font-size: 2.25rem;
  }
}
</style>
