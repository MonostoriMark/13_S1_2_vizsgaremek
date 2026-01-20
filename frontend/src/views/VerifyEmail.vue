<template>
  <div class="verify-email-page">
    <!-- Home Button -->
    <router-link to="/" class="home-button">
      <span class="home-icon">🏠</span>
      <span class="home-text">Kezdőlap</span>
    </router-link>

    <!-- Verification Card -->
    <div class="verify-card">
      <div class="card-content">
        <div class="welcome-header">
          <div class="travel-icon-wrapper">
            <div class="travel-icon" :class="{ 'success-icon': success, 'error-icon': error }">
              <span v-if="loading">✉️</span>
              <span v-else-if="success">✓</span>
              <span v-else-if="error">⚠️</span>
            </div>
            <div class="icon-glow" v-if="success"></div>
          </div>
          <h1 v-if="loading">E-mail megerősítése</h1>
          <h1 v-else-if="success">Sikeres megerősítés!</h1>
          <h1 v-else-if="error">Hiba történt</h1>
          <p class="welcome-subtitle" v-if="loading">Kérjük, várj egy pillanatot...</p>
          <p class="welcome-subtitle" v-else-if="success">E-mail címed sikeresen megerősítve</p>
          <p class="welcome-subtitle" v-else-if="error">Nem sikerült megerősíteni az e-mail címed</p>
        </div>

        <div v-if="loading" class="loading-state">
          <div class="loading-spinner"></div>
          <p class="loading-text">E-mail cím megerősítése folyamatban...</p>
        </div>

        <div v-else-if="success" class="success-state">
          <div class="success-content">
            <div class="success-icon-large">✓</div>
            <p class="success-message">{{ message }}</p>
            <p class="redirect-text">Átirányítás a bejelentkezési oldalra <span class="countdown">{{ countdown }}</span> másodperc múlva...</p>
            <router-link to="/login" class="btn-login">
              <span>Bejelentkezés</span>
              <div class="button-glow"></div>
            </router-link>
          </div>
        </div>

        <div v-else-if="error" class="error-state">
          <div class="error-content">
            <div class="error-icon-large">⚠️</div>
            <p class="error-message">{{ error }}</p>
            <div class="error-actions">
              <router-link to="/login" class="btn-back-login">
                <span>Vissza a bejelentkezéshez</span>
              </router-link>
              <button @click="resendVerification" class="btn-resend" :disabled="resending">
                <span v-if="resending">Küldés...</span>
                <span v-else>E-mail újraküldése</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Slideshow Section -->
    <div class="verify-slideshow">
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
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'
import { authService } from '../services/authService'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const success = ref(false)
const error = ref('')
const message = ref('')
const countdown = ref(5)
const resending = ref(false)

// Slideshow
const slideshowImages = [
  'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&auto=format&fit=crop'
]

const currentSlide = ref(0)
let slideshowInterval = null
let countdownInterval = null

onMounted(async () => {
  // Start slideshow
  slideshowInterval = setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % slideshowImages.length
  }, 5000)

  const token = route.params.token || route.query.token

  if (!token) {
    error.value = 'Érvénytelen megerősítési link.'
    loading.value = false
    return
  }

  try {
    const response = await api.get(`/auth/verify-email/${token}`)
    success.value = true
    message.value = response.data.message || 'E-mail cím sikeresen megerősítve! Most már bejelentkezhetsz.'
    
    // Start countdown
    countdownInterval = setInterval(() => {
      countdown.value--
      if (countdown.value <= 0) {
        clearInterval(countdownInterval)
        router.push('/login')
      }
    }, 1000)
  } catch (err) {
    error.value = err.response?.data?.message || 'Érvénytelen vagy lejárt megerősítési link.'
  } finally {
    loading.value = false
  }
})

onUnmounted(() => {
  if (slideshowInterval) {
    clearInterval(slideshowInterval)
  }
  if (countdownInterval) {
    clearInterval(countdownInterval)
  }
})

const resendVerification = async () => {
  // Try to get email from query params or prompt user
  const email = route.query.email
  if (!email) {
    error.value = 'Kérjük, használd a bejelentkezési oldalon található "E-mail újraküldése" funkciót.'
    return
  }

  resending.value = true
  try {
    await authService.resendVerificationEmail(email)
    if (window.showToast) {
      window.showToast('Megerősítő e-mail elküldve! Kérjük, ellenőrizd az e-mail fiókodat.', 'success')
    }
    message.value = 'Megerősítő e-mail elküldve! Kérjük, ellenőrizd az e-mail fiókodat.'
    error.value = ''
  } catch (err) {
    error.value = err.response?.data?.message || 'Hiba történt az e-mail küldése során'
  } finally {
    resending.value = false
  }
}
</script>

<style scoped>
.verify-email-page {
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
}

.home-icon {
  font-size: 1.2rem;
}

.verify-card {
  flex: 1;
  max-width: 400px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.8);
  z-index: 10;
}

.card-content {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.welcome-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.travel-icon-wrapper {
  position: relative;
  display: inline-block;
  margin-bottom: 1rem;
}

.travel-icon {
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  margin: 0 auto;
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
  transition: all 0.3s ease;
}

.travel-icon.success-icon {
  background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
  box-shadow: 0 8px 24px rgba(39, 174, 96, 0.3);
}

.travel-icon.error-icon {
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
  box-shadow: 0 8px 24px rgba(231, 76, 60, 0.3);
}

.icon-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 80px;
  height: 80px;
  background: radial-gradient(circle, rgba(39, 174, 96, 0.3) 0%, transparent 70%);
  border-radius: 50%;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 0.5;
    transform: translate(-50%, -50%) scale(1);
  }
  50% {
    opacity: 0.8;
    transform: translate(-50%, -50%) scale(1.1);
  }
}

.welcome-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0.5rem 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.welcome-subtitle {
  color: #7f8c8d;
  font-size: 1rem;
  margin-top: 0.5rem;
}

.loading-state,
.success-state,
.error-state {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #f0f0f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-text {
  color: #7f8c8d;
  font-size: 0.95rem;
}

.success-content,
.error-content {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.success-icon-large {
  font-size: 3rem;
  color: #27ae60;
  margin-bottom: 0.5rem;
}

.error-icon-large {
  font-size: 3rem;
  color: #e74c3c;
  margin-bottom: 0.5rem;
}

.success-message {
  font-size: 0.9rem;
  color: #4b5563;
  line-height: 1.5;
  text-align: center;
  padding: 0.875rem;
  background: #d4edda;
  border-radius: 10px;
  border: 1px solid #c3e6cb;
  width: 100%;
}

.error-message {
  font-size: 0.9rem;
  color: #721c24;
  line-height: 1.5;
  text-align: center;
  padding: 0.875rem;
  background: #f8d7da;
  border-radius: 10px;
  border: 1px solid #f5c6cb;
  width: 100%;
}

.redirect-text {
  font-size: 0.9rem;
  color: #7f8c8d;
  margin-top: 0.5rem;
}

.countdown {
  font-weight: 700;
  color: #667eea;
}

.btn-login {
  position: relative;
  display: inline-block;
  padding: 0.875rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-decoration: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  margin-top: 0.75rem;
  border: none;
  cursor: pointer;
  overflow: hidden;
}

.button-glow {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s ease;
}

.btn-login:hover .button-glow {
  left: 100%;
}

.btn-login:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
}

.error-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  width: 100%;
  margin-top: 1rem;
}

.btn-back-login {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-decoration: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  text-align: center;
}

.btn-back-login:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-resend {
  padding: 0.75rem 1.5rem;
  background: #f8f9fa;
  color: #667eea;
  border: 2px solid #667eea;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-resend:hover:not(:disabled) {
  background: #667eea;
  color: white;
  transform: translateY(-2px);
}

.btn-resend:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Slideshow Section */
.verify-slideshow {
  flex: 1;
  max-width: 500px;
  height: 100vh;
  position: relative;
  overflow: hidden;
  border-radius: 0;
}

.slideshow-container {
  position: relative;
  width: 100%;
  height: 100%;
}

.slide {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  transition: opacity 1s ease-in-out;
}

.slide.active {
  opacity: 1;
}

.slide img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.slideshow-dots {
  position: absolute;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 0.75rem;
  z-index: 20;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  transition: all 0.3s ease;
}

.dot.active {
  background: white;
  width: 30px;
  border-radius: 5px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .verify-email-page {
    flex-direction: column;
    padding: 1rem;
  }

  .verify-card {
    padding: 1.5rem;
    max-width: 100%;
  }

  .verify-slideshow {
    display: none;
  }

  .home-button {
    top: 1rem;
    left: 1rem;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
  }

  .welcome-header h1 {
    font-size: 1.5rem;
  }

  .travel-icon {
    width: 60px;
    height: 60px;
    font-size: 2rem;
  }
}
</style>
