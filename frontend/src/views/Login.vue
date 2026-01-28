<template>
  <div class="login-page">
    <!-- Home Button -->
    <router-link to="/" class="home-button">
      <span class="home-icon">🏠</span>
      <span class="home-text">Kezdőlap</span>
    </router-link>
    
    <!-- Minimal Card -->
    <div class="login-card">
      <div class="card-content">
            <div class="welcome-header">
              <div class="travel-icon-wrapper">
                <div class="travel-icon">✈️</div>
                <div class="icon-glow"></div>
              </div>
              <h1>Üdvözöljük</h1>
              <p class="welcome-subtitle">Bejelentkezés e-mail címmel</p>
            </div>

            <div v-if="error" class="error-message">
              <span class="error-icon">⚠️</span>
              <div class="error-text">{{ error }}</div>
              <button 
                v-if="error.includes('erősítsd meg') || error.includes('megerősít') || error.includes('E-mail')" 
                @click="resendVerificationEmail"
                class="btn-resend-verification"
                :disabled="resendingEmail"
              >
                {{ resendingEmail ? 'Küldés...' : 'E-mail újraküldése' }}
              </button>
            </div>

            <form @submit.prevent="handleLogin" class="login-form">
              <div class="form-group">
                <label for="email">E-mail cím</label>
                <div class="input-wrapper">
                  <span class="input-icon">✉️</span>
                  <input
                    id="email"
                    v-model="email"
                    type="email"
                    required
                    placeholder="pelda@email.com"
                    class="glass-input"
                  />
                  <div class="input-glow"></div>
                </div>
              </div>

              <div class="form-group">
                <label for="password">Jelszó</label>
                <div class="input-wrapper">
                  <span class="input-icon">🔒</span>
                  <input
                    id="password"
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    placeholder="••••••••••"
                    class="glass-input"
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
                  <div class="input-glow"></div>
                </div>
              </div>

              <div class="forgot-password">
                <router-link to="/forgot-password" class="forgot-link">Elfelejtetted a jelszavad?</router-link>
              </div>

              <button type="submit" class="btn-login" :disabled="loading">
                <span v-if="loading" class="loading-spinner"></span>
                <span v-else>{{ loading ? 'Bejelentkezés...' : 'BEJELENTKEZÉS' }}</span>
                <div class="button-glow"></div>
              </button>

              <p class="register-link">
                Nincs még fiókod? <router-link to="/register">Regisztrálj most</router-link>
              </p>
            </form>
          </div>
        </div>

      <!-- 2FA Setup Prompt -->
      <TwoFactorPrompt 
        :visible="show2FAPrompt"
        @enable="handleEnable2FA"
        @skip="handleSkip2FA"
      />
    
    <!-- Slideshow Section -->
    <div class="login-slideshow">
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
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { authService } from '../services/authService'
import TwoFactorPrompt from '../components/TwoFactorPrompt.vue'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const resendingEmail = ref(false)
const show2FAPrompt = ref(false)
const showPassword = ref(false)

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

const handleLogin = async () => {
  error.value = ''
  loading.value = true

  const result = await authStore.login(email.value, password.value)

  if (result.success) {
    // For hotel admins: force 2FA setup, don't allow skipping
    if (authStore.state.user.role === 'hotel' && !authStore.state.user.two_factor_enabled) {
      // Redirect directly to profile to enable 2FA
      router.push('/admin/users')
      loading.value = false
      return
    }
    
    // Show 2FA prompt if user doesn't have it enabled (except super_admin and hotel)
    if (result.show_2fa_prompt && !authStore.state.user.two_factor_enabled && authStore.state.user.role !== 'hotel') {
      show2FAPrompt.value = true
      // Don't navigate yet - wait for user to enable or skip
      loading.value = false
      return
    }

    // Navigate based on role
    if (authStore.state.user.role === 'super_admin') {
      router.push('/super-admin/dashboard')
    } else if (authStore.state.user.role === 'user') {
      router.push('/bookings')
    } else if (authStore.state.user.role === 'hotel') {
      router.push('/admin/bookings')
    } else {
      router.push('/search')
    }
  } else if (result.requires_2fa_setup) {
    // Redirect to 2FA setup
    router.push({
      path: '/two-factor-auth',
      query: {
        setup: 'true',
        qr_code: result.qr_code,
        secret: result.two_factor_secret,
        email: email.value,
        password: password.value
      }
    })
  } else if (result.requires_2fa) {
    // Redirect to 2FA verification
    router.push({
      path: '/two-factor-auth',
      query: {
        email: email.value,
        password: password.value
      }
    })
  } else {
    error.value = result.message || 'Bejelentkezés sikertelen'
    // If email not verified, show resend option
    if (result.email_verified === false || result.message?.includes('erősítsd meg')) {
      // Error message already includes instructions, resend button will show automatically
    }
  }

  loading.value = false
}

const resendVerificationEmail = async () => {
  if (!email.value) {
    error.value = 'Kérjük, add meg az e-mail címedet'
    return
  }
  
  resendingEmail.value = true
  try {
    const { authService } = await import('../services/authService')
    await authService.resendVerificationEmail(email.value)
    if (window.showToast) {
      window.showToast('Megerősítő e-mail elküldve! Kérjük, ellenőrizd az e-mail fiókodat.', 'success')
    }
    error.value = 'Megerősítő e-mail elküldve! Kérjük, ellenőrizd az e-mail fiókodat.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Hiba történt az e-mail küldése során'
  } finally {
    resendingEmail.value = false
  }
}

const handleEnable2FA = async () => {
  show2FAPrompt.value = false

  try {
    // Kérjünk a backendtől egy 2FA secretet + QR kódot a jelenlegi, már bejelentkezett userhez
    const data = await authService.enable2FA()

    // Vigyük át a felhasználót a 2FA beállító oldalra, ahol meg tudja erősíteni a kódot
    router.push({
      path: '/two-factor-auth',
      query: {
        setup: 'true',
        qr_code: data.qr_code,
        secret: data.two_factor_secret,
        email: email.value,
        password: password.value
      }
    })
  } catch (err) {
    // Ha valamiért nem sikerül, viselkedjünk elegánsan és jelezzük a hibát
    if (window.showToast) {
      window.showToast(
        err.response?.data?.message || 'A 2FA engedélyezése sikertelen. Később a profil oldalról is megpróbálhatod.',
        'error'
      )
    }

    // Biztonsági fallback: navigáljunk a szerep szerinti főoldalra
    if (authStore.state.user.role === 'user') {
      router.push('/bookings')
    } else if (authStore.state.user.role === 'hotel') {
      router.push('/admin/bookings')
    } else {
      router.push('/search')
    }
  }
}

const handleSkip2FA = () => {
  show2FAPrompt.value = false
  // Navigate based on role
  if (authStore.state.user.role === 'super_admin') {
    router.push('/super-admin/dashboard')
  } else if (authStore.state.user.role === 'user') {
    router.push('/bookings')
  } else if (authStore.state.user.role === 'hotel') {
    router.push('/admin/bookings')
  } else {
    router.push('/search')
  }
}
</script>

<style scoped>
.login-page {
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

/* Slideshow Section */
.login-slideshow {
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

/* Minimal Card */
.login-card {
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
.login-form {
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

.input-glow {
  display: none;
}

.forgot-password {
  text-align: right;
  margin-bottom: 1.5rem;
}

.forgot-link {
  color: #667eea;
  font-size: 0.875rem;
  text-decoration: none;
  transition: color 0.2s ease;
  font-weight: 500;
}

.forgot-link:hover {
  color: #764ba2;
}

/* Button */
.btn-login {
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

.btn-login:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-login:active:not(:disabled) {
  transform: translateY(0);
}

.btn-login:disabled {
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

/* Register Link */
.register-link {
  text-align: center;
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0;
}

.register-link a {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s ease;
}

.register-link a:hover {
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
  flex-direction: column;
  gap: 0.5rem;
}

.error-message .error-icon {
  font-size: 1.1rem;
  align-self: flex-start;
}

.error-text {
  flex: 1;
}

.btn-resend-verification {
  margin-top: 0.5rem;
  padding: 0.5rem 1rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  align-self: flex-start;
}

.btn-resend-verification:hover:not(:disabled) {
  background: #764ba2;
  transform: translateY(-1px);
}

.btn-resend-verification:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-resend-verification {
  margin-top: 0.75rem;
  padding: 0.5rem 1rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-resend-verification:hover:not(:disabled) {
  background: #764ba2;
  transform: translateY(-1px);
}

.btn-resend-verification:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 768px) {
  .login-page {
    flex-direction: column;
    padding: 1rem;
  }

  .login-slideshow {
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

  .login-card {
    padding: 2rem 1.5rem;
    max-width: 100%;
    order: 1;
  }

  .welcome-header h1 {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .login-page {
    flex-direction: column;
    padding: 1rem;
  }

  .login-slideshow {
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

  .login-card {
    padding: 1.5rem 1.25rem;
    max-width: 100%;
    order: 1;
  }  .welcome-header h1 {
    font-size: 1.375rem;
  }
}
</style>
