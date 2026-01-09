<template>
  <div class="register-page">
    <!-- Animated Background -->
    <div class="background-container">
      <div class="background-image"></div>
      <div class="gradient-overlay"></div>
      <div class="animated-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
      </div>
    </div>

    <!-- Glass-morphism Card -->
    <div class="glass-card">
      <div class="card-content">
        <div class="welcome-header">
          <div class="travel-icon-wrapper">
            <div class="travel-icon">✈️</div>
            <div class="icon-glow"></div>
          </div>
          <h1>Welcome</h1>
          <p class="welcome-subtitle">Create Your Account</p>
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

          <div class="form-group">
            <label for="userType">Register as</label>
            <div class="input-wrapper">
              <span class="input-icon">🏷️</span>
              <select id="userType" v-model="userType" required class="glass-input glass-select">
                <option value="user">Guest</option>
                <option value="hotel">Hotel Admin</option>
              </select>
              <div class="input-glow"></div>
            </div>
          </div>

          <!-- Hotel-specific fields with smooth transition -->
          <Transition name="slide-fade">
            <div v-if="userType === 'hotel'" class="hotel-fields">
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

          <div class="divider">
            <span>OR</span>
          </div>

          <div class="social-login">
            <button type="button" class="social-btn google">
              <span class="social-icon">G</span>
            </button>
            <button type="button" class="social-btn facebook">
              <span class="social-icon">f</span>
            </button>
            <button type="button" class="social-btn apple">
              <span class="social-icon">🍎</span>
            </button>
          </div>

          <p class="login-link">
            Already have account? <router-link to="/login">Login Now</router-link>
          </p>
        </form>
      </div>

      <!-- City Silhouettes -->
      <div class="city-silhouettes">
        <span class="city-icon">🏛️</span>
        <span class="city-icon">⛪</span>
        <span class="city-icon">🗼</span>
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

const name = ref('')
const email = ref('')
const password = ref('')
const userType = ref('user')
const location = ref('')
const type = ref('hotel')
const starRating = ref(null)
const error = ref('')
const loading = ref(false)

const handleRegister = async () => {
  error.value = ''
  loading.value = true

  let result

  if (userType.value === 'user') {
    result = await authStore.registerUser(name.value, email.value, password.value)
  } else {
    if (!location.value) {
      error.value = 'Location is required for hotel registration'
      loading.value = false
      return
    }
    result = await authStore.registerHotel(
      name.value,
      email.value,
      password.value,
      location.value,
      type.value,
      starRating.value
    )
  }

  if (result.success) {
    if (authStore.state.user.role === 'user') {
      router.push('/bookings')
    } else if (authStore.state.user.role === 'hotel') {
      router.push('/admin/bookings')
    } else {
      router.push('/search')
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
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  position: relative;
  overflow: hidden;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Animated Background */
.background-container {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 0;
}

.background-image {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
  animation: backgroundShift 20s ease-in-out infinite;
}

@keyframes backgroundShift {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.gradient-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, 
    rgba(102, 126, 234, 0.85) 0%, 
    rgba(118, 75, 162, 0.9) 50%,
    rgba(139, 69, 199, 0.85) 100%);
  backdrop-filter: blur(2px);
}

.animated-shapes {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow: hidden;
}

.shape {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.3;
  animation: float 15s ease-in-out infinite;
}

.shape-1 {
  width: 400px;
  height: 400px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  top: -100px;
  left: -100px;
  animation-delay: 0s;
}

.shape-2 {
  width: 300px;
  height: 300px;
  background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
  bottom: -50px;
  right: -50px;
  animation-delay: 5s;
}

.shape-3 {
  width: 250px;
  height: 250px;
  background: linear-gradient(135deg, #06b6d4 0%, #8b5cf6 100%);
  top: 50%;
  right: 10%;
  animation-delay: 10s;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -30px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
}

/* Glass-morphism Card */
.glass-card {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 920px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-radius: 32px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 
    0 8px 32px rgba(0, 0, 0, 0.3),
    0 0 0 1px rgba(255, 255, 255, 0.1) inset,
    0 0 60px rgba(102, 126, 234, 0.3);
  padding: 3rem 2.5rem;
  animation: cardEntrance 0.6s ease-out;
  overflow: hidden;
}

.card-layout {
  display: grid;
  grid-template-columns: 1.1fr 1.2fr;
  gap: 2rem;
  align-items: flex-start;
}

.card-image-column {
  position: relative;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
}

.carousel-slide {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 340px;
  background-size: cover;
  background-position: center;
  transform-origin: center;
  animation: subtleZoom 12s ease-in-out infinite;
}

@keyframes subtleZoom {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.08); }
}

.carousel-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    145deg,
    rgba(15, 23, 42, 0.9) 0%,
    rgba(88, 28, 135, 0.8) 50%,
    rgba(37, 99, 235, 0.6) 100%
  );
}

.carousel-content {
  position: relative;
  z-index: 2;
  height: 100%;
  padding: 2.25rem 2rem;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  color: white;
}

.carousel-title {
  font-size: 1.6rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.carousel-subtitle {
  font-size: 0.95rem;
  opacity: 0.9;
}

.carousel-dots {
  position: absolute;
  bottom: 1.25rem;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 0.5rem;
  z-index: 3;
}

.carousel-dots .dot {
  width: 9px;
  height: 9px;
  border-radius: 999px;
  border: none;
  background: rgba(255, 255, 255, 0.35);
  cursor: pointer;
  padding: 0;
  transition: all 0.25s ease;
}

.carousel-dots .dot.active {
  width: 22px;
  background: #ffffff;
}

.card-form-column {
  position: relative;
}

.glass-card::-webkit-scrollbar {
  width: 8px;
}

.glass-card::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}

.glass-card::-webkit-scrollbar-thumb {
  background: rgba(102, 126, 234, 0.5);
  border-radius: 10px;
}

.glass-card::-webkit-scrollbar-thumb:hover {
  background: rgba(102, 126, 234, 0.7);
}

@keyframes cardEntrance {
  from {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.glass-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, 
    transparent 0%, 
    rgba(255, 255, 255, 0.5) 50%, 
    transparent 100%);
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
  position: relative;
  display: inline-block;
  margin-bottom: 1rem;
}

.travel-icon {
  font-size: 2.5rem;
  filter: drop-shadow(0 0 10px rgba(102, 126, 234, 0.5));
  animation: iconFloat 3s ease-in-out infinite;
  position: relative;
  z-index: 2;
}

@keyframes iconFloat {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-10px) rotate(5deg); }
}

.icon-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 80px;
  height: 80px;
  background: radial-gradient(circle, rgba(102, 126, 234, 0.4) 0%, transparent 70%);
  border-radius: 50%;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.6; }
  50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.3; }
}

.welcome-header h1 {
  font-size: 2.25rem;
  font-weight: 800;
  background: linear-gradient(135deg, #ffffff 0%, #e0d5ff 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0.5rem 0;
  text-shadow: 0 0 30px rgba(102, 126, 234, 0.5);
  letter-spacing: -0.5px;
}

.welcome-subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.95rem;
  font-weight: 500;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

/* Form Styles */
.register-form {
  width: 100%;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.95);
  margin-bottom: 0.75rem;
  font-size: 0.9rem;
  letter-spacing: 0.3px;
}

.input-wrapper {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1.1rem;
  z-index: 3;
  filter: drop-shadow(0 0 5px rgba(102, 126, 234, 0.5));
}

.glass-input,
.glass-select {
  width: 100%;
  padding: 1rem 1rem 1rem 3.5rem;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  font-size: 0.95rem;
  color: white;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  font-weight: 500;
  appearance: none;
}

.glass-select {
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='white' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  padding-right: 3rem;
}

.glass-select option {
  color: #111827;
  background-color: #ffffff;
}

.glass-input::placeholder {
  color: rgba(255, 255, 255, 0.6);
}

.glass-input:focus,
.glass-select:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.4);
  box-shadow: 
    0 0 0 4px rgba(102, 126, 234, 0.3),
    0 0 20px rgba(102, 126, 234, 0.4),
    inset 0 0 20px rgba(255, 255, 255, 0.1);
  transform: translateY(-2px);
}

.input-glow {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.3), rgba(118, 75, 162, 0.3));
  opacity: 0;
  transition: opacity 0.4s ease;
  pointer-events: none;
  z-index: 1;
}

.glass-input:focus + .input-glow,
.glass-select:focus + .input-glow {
  opacity: 1;
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
  padding: 1.125rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 16px;
  font-size: 1rem;
  font-weight: 700;
  letter-spacing: 1px;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  margin-top: 0.5rem;
  margin-bottom: 1.5rem;
  position: relative;
  overflow: hidden;
  text-transform: uppercase;
  box-shadow: 
    0 4px 15px rgba(102, 126, 234, 0.4),
    0 0 0 1px rgba(255, 255, 255, 0.1) inset;
}

.btn-register::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s ease;
}

.btn-register:hover::before {
  left: 100%;
}

.btn-register:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 
    0 8px 25px rgba(102, 126, 234, 0.5),
    0 0 30px rgba(102, 126, 234, 0.4),
    0 0 0 1px rgba(255, 255, 255, 0.2) inset;
}

.btn-register:active:not(:disabled) {
  transform: translateY(-1px);
}

.btn-register:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.button-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100%;
  height: 100%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
  border-radius: 16px;
  opacity: 0;
  transition: opacity 0.4s ease;
  pointer-events: none;
}

.btn-register:hover .button-glow {
  opacity: 1;
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
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
  font-weight: 600;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.divider span {
  padding: 0 1rem;
  background: transparent;
}

/* Social Buttons */
.social-login {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.social-btn {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.3);
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  font-weight: 700;
  padding: 0;
  position: relative;
  overflow: hidden;
}

.social-btn::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(0);
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  transition: transform 0.4s ease;
}

.social-btn:hover::before {
  transform: translate(-50%, -50%) scale(1);
}

.social-btn:hover {
  transform: translateY(-3px) scale(1.05);
  border-color: rgba(255, 255, 255, 0.5);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.social-btn.google {
  color: white;
}

.social-btn.google .social-icon {
  background: linear-gradient(135deg, #4285f4 0%, #34a853 50%, #fbbc05 75%, #ea4335 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  position: relative;
  z-index: 2;
}

.social-btn.facebook {
  background: rgba(24, 119, 242, 0.3);
  border-color: rgba(24, 119, 242, 0.5);
  color: white;
}

.social-btn.apple {
  background: rgba(0, 0, 0, 0.3);
  border-color: rgba(255, 255, 255, 0.3);
  color: white;
}

/* Login Link */
.login-link {
  text-align: center;
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.9rem;
  margin: 0;
}

.login-link a {
  color: white;
  text-decoration: none;
  font-weight: 700;
  transition: all 0.3s ease;
  text-shadow: 0 0 10px rgba(102, 126, 234, 0.8);
}

.login-link a:hover {
  text-shadow: 0 0 20px rgba(102, 126, 234, 1);
  transform: scale(1.05);
  display: inline-block;
}

/* Error Message */
.error-message {
  background: rgba(231, 76, 60, 0.2);
  backdrop-filter: blur(10px);
  color: white;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
  border: 1px solid rgba(231, 76, 60, 0.4);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  animation: shake 0.5s ease;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-10px); }
  75% { transform: translateX(10px); }
}

.error-icon {
  font-size: 1.2rem;
}

/* City Silhouettes */
.city-silhouettes {
  display: flex;
  justify-content: center;
  gap: 2rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  opacity: 0.4;
}

.city-icon {
  font-size: 1.5rem;
  filter: drop-shadow(0 0 5px rgba(102, 126, 234, 0.5));
  animation: cityFloat 4s ease-in-out infinite;
}

.city-icon:nth-child(2) {
  animation-delay: 1.3s;
}

.city-icon:nth-child(3) {
  animation-delay: 2.6s;
}

@keyframes cityFloat {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

/* Responsive Design */
@media (max-width: 768px) {
  .register-page {
    padding: 1rem;
  }

  .glass-card {
    padding: 2.5rem 2rem;
    border-radius: 24px;
    max-height: 85vh;
  }

  .welcome-header h1 {
    font-size: 1.75rem;
  }

  .travel-icon {
    font-size: 2rem;
  }
}

@media (max-width: 480px) {
  .register-page {
    padding: 0;
  }

  .glass-card {
    border-radius: 0;
    min-height: 100vh;
    padding: 2rem 1.5rem;
    max-height: 100vh;
  }

  .welcome-header h1 {
    font-size: 1.5rem;
  }

  .glass-input,
  .glass-select {
    padding: 0.875rem 0.875rem 0.875rem 3rem;
  }
}
</style>
