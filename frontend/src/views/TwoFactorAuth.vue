<template>
  <div class="two-factor-page" :class="{ 'light-theme': isLightTheme }">
    <div class="two-factor-container" :class="{ 'light-theme': isLightTheme }">
      <div class="tech-header">
        <div class="tech-icon">🔐</div>
        <h1>Kétfaktoros hitelesítés</h1>
        <p class="tech-subtitle">{{ setupMode ? 'Autentikátor beállítása' : 'Ellenőrző kód megadása' }}</p>
      </div>

      <!-- Setup Mode (First Time) -->
      <div v-if="setupMode" class="setup-section">
        <div class="qr-container">
          <img v-if="qrCode" :src="qrCode" alt="QR Code" class="qr-code" />
        </div>
        <div class="secret-info">
          <p class="info-text">Olvasd be a QR kódot az autentikációs alkalmazásoddal (Google Authenticator, Authy, stb.).</p>
          <div class="secret-box">
            <label>Titkos kulcs:</label>
            <div class="secret-value">{{ secret }}</div>
            <button @click="copySecret" class="btn-copy">📋 Másolás</button>
          </div>
        </div>
        <div class="form-group">
          <label>Add meg az alkalmazásból a 6 jegyű kódot</label>
          <input
            ref="codeInputRef"
            v-model="code"
            type="text"
            maxlength="6"
            placeholder="000000"
            class="code-input"
            @input="formatCode"
            autofocus
          />
        </div>
        <button @click="verifySetup" class="btn-verify" :disabled="code.length !== 6 || verifying">
          {{ verifying ? 'Ellenőrzés...' : 'Ellenőrzés és befejezés' }}
        </button>
      </div>

      <!-- Verification Mode (Normal Login) -->
      <div v-else class="verification-section">
        <div class="form-group">
          <label>Add meg az autentikációs alkalmazás 6 jegyű kódját</label>
          <input
            ref="codeInputRef"
            v-model="code"
            type="text"
            maxlength="6"
            placeholder="000000"
            class="code-input"
            @input="formatCode"
            @keyup.enter="verifyCode"
            autofocus
          />
        </div>
        <button @click="verifyCode" class="btn-verify" :disabled="code.length !== 6 || verifying">
          {{ verifying ? 'Ellenőrzés...' : 'Ellenőrzés' }}
        </button>

        <div class="recovery-row">
          <router-link
            :to="{ path: '/two-factor-recovery', query: email ? { email } : {} }"
            class="recovery-link"
          >
            Elvesztetted a telefonod? 2FA helyreállítás e-mailben
          </router-link>
        </div>
      </div>

      <div v-if="error" class="error-message">{{ error }}</div>
      <div v-if="successMessage" class="success-message">{{ successMessage }}</div>

      <button @click="goBack" class="btn-back">← Vissza a bejelentkezéshez</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { authService } from '../services/authService'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const setupMode = computed(() => route.query.setup === 'true')
const qrCode = computed(() => route.query.qr_code || '')
const secret = computed(() => route.query.secret || '')
const email = computed(() => route.query.email || '')
const password = computed(() => route.query.password || '')

// Determine if we should use light theme (for hotel admins and regular users)
// Super admin uses dark theme
const isLightTheme = computed(() => {
  // If user is already logged in, check their role
  if (authStore.state.user) {
    return authStore.state.user.role !== 'super_admin'
  }
  // For login flow, check if we can determine role from route query or localStorage
  // Super admin typically has a different email pattern or we can check stored user
  const storedUser = localStorage.getItem('auth_user')
  if (storedUser) {
    try {
      const user = JSON.parse(storedUser)
      if (user.role === 'super_admin') {
        return false
      }
    } catch (e) {
      // Ignore parse errors
    }
  }
  // Default to light theme for hotel/user roles (most common case)
  return true
})

const code = ref('')
const error = ref('')
const successMessage = ref('')
const verifying = ref(false)
const codeInputRef = ref(null)

const formatCode = (e) => {
  code.value = e.target.value.replace(/\D/g, '').slice(0, 6)
}

const copySecret = () => {
  navigator.clipboard.writeText(secret.value)
  successMessage.value = 'Titkos kulcs a vágólapra másolva!'
  setTimeout(() => {
    successMessage.value = ''
  }, 2000)
}

const verifySetup = async () => {
  if (code.value.length !== 6) {
    error.value = 'Kérjük, adj meg egy 6 jegyű kódot'
    return
  }

  verifying.value = true
  error.value = ''
  successMessage.value = ''

  try {
    // Ha már be vagy jelentkezve (normál felhasználó / hotel admin), akkor a 2FA-t
    // a dedikált végponttal engedélyezzük, nem újra bejelentkezéssel
    if (authStore.state.isAuthenticated) {
      await authService.verifyAndEnable2FA(code.value)
      successMessage.value = 'A 2FA beállítása sikeres!'

      // Frissítsük a lokális user állapotot, hogy a rendszer \"emlékezzen\" rá
      if (authStore.state.user) {
        authStore.state.user.two_factor_enabled = true
        localStorage.setItem('auth_user', JSON.stringify(authStore.state.user))
      }

      setTimeout(() => {
        // Navigate based on role
        if (authStore.state.user?.role === 'super_admin') {
          router.push('/super-admin/dashboard')
        } else if (authStore.state.user?.role === 'hotel') {
          router.push('/admin/bookings')
        } else if (authStore.state.user?.role === 'user') {
          router.push('/bookings')
        } else {
          router.push('/search')
        }
      }, 1000)
    } else {
      // Super admin első 2FA beállítása: itt még nincs token, ezért
      // a kódot a login folyamaton keresztül ellenőrizzük
      const result = await authStore.login(email.value, password.value, code.value)

      if (result.success) {
        successMessage.value = 'A 2FA beállítása sikeres!'
        setTimeout(() => {
          if (authStore.state.user?.role === 'super_admin') {
            router.push('/super-admin/dashboard')
          } else if (authStore.state.user?.role === 'hotel') {
            router.push('/admin/bookings')
          } else if (authStore.state.user?.role === 'user') {
            router.push('/bookings')
          } else {
            router.push('/search')
          }
        }, 1000)
      } else {
        error.value = result.message || 'Érvénytelen kód. Kérjük, próbáld újra.'
      }
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Az ellenőrzés sikertelen'
  } finally {
    verifying.value = false
  }
}

const verifyCode = async () => {
  if (code.value.length !== 6) {
    error.value = 'Kérjük, adj meg egy 6 jegyű kódot'
    return
  }

  verifying.value = true
  error.value = ''
  successMessage.value = ''

  try {
    const result = await authStore.login(email.value, password.value, code.value)
    
    if (result.success) {
      successMessage.value = 'Sikeres bejelentkezés!'
      setTimeout(() => {
        // Navigate based on role
        if (authStore.state.user?.role === 'super_admin') {
          router.push('/super-admin/dashboard')
        } else if (authStore.state.user?.role === 'hotel') {
          router.push('/admin/bookings')
        } else if (authStore.state.user?.role === 'user') {
          router.push('/bookings')
        } else {
          router.push('/search')
        }
      }, 1000)
    } else {
      error.value = result.message || 'Érvénytelen kód. Kérjük, próbáld újra.'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Az ellenőrzés sikertelen'
  } finally {
    verifying.value = false
  }
}

const goBack = () => {
  router.push('/login')
}

// Focus the input field when component mounts or mode changes
const focusInput = async () => {
  await nextTick()
  if (codeInputRef.value) {
    codeInputRef.value.focus()
  }
}

// Focus on mount
onMounted(() => {
  focusInput()
})

// Focus when switching between setup and verification modes
watch(setupMode, () => {
  focusInput()
})
</script>

<style scoped>
.two-factor-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0f0f0f 100%);
  padding: 2rem;
  position: relative;
  overflow: hidden;
}

.two-factor-page.light-theme {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.two-factor-page::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.1) 0%, transparent 50%);
  pointer-events: none;
}

.two-factor-page.light-theme::before {
  background: 
    radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.05) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.05) 0%, transparent 50%);
}

.two-factor-container {
  background: rgba(20, 20, 20, 0.95);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 16px;
  padding: 3rem;
  max-width: 500px;
  width: 100%;
  box-shadow: 
    0 20px 60px rgba(0, 0, 0, 0.5),
    0 0 40px rgba(102, 126, 234, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  position: relative;
  z-index: 1;
}

.two-factor-container.light-theme {
  background: white;
  border: 1px solid rgba(102, 126, 234, 0.2);
  box-shadow: 
    0 20px 60px rgba(0, 0, 0, 0.1),
    0 0 40px rgba(102, 126, 234, 0.05);
}

.recovery-row {
  margin-top: 1rem;
  text-align: center;
}

.recovery-link {
  display: inline-block;
  color: #667eea;
  font-weight: 600;
  text-decoration: none;
  font-size: 0.9rem;
}

.recovery-link:hover {
  text-decoration: underline;
  color: #764ba2;
}

.tech-header {
  text-align: center;
  margin-bottom: 2rem;
}

.tech-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  filter: drop-shadow(0 0 20px rgba(102, 126, 234, 0.5));
}

.tech-header h1 {
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0.5rem 0;
  text-shadow: 0 0 30px rgba(102, 126, 234, 0.3);
}

.two-factor-container.light-theme .tech-header h1 {
  text-shadow: none;
}

.tech-subtitle {
  color: #9ca3af;
  font-size: 0.95rem;
  margin-top: 0.5rem;
}

.two-factor-container.light-theme .tech-subtitle {
  color: #6b7280;
}

.setup-section,
.verification-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.qr-container {
  display: flex;
  justify-content: center;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(102, 126, 234, 0.2);
}

.two-factor-container.light-theme .qr-container {
  background: #f9fafb;
  border: 1px solid rgba(102, 126, 234, 0.2);
}

.qr-code {
  width: 200px;
  height: 200px;
  border-radius: 8px;
}

.secret-info {
  background: rgba(102, 126, 234, 0.1);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 12px;
  padding: 1.5rem;
}

.two-factor-container.light-theme .secret-info {
  background: #f9fafb;
  border: 1px solid rgba(102, 126, 234, 0.2);
}

.info-text {
  color: #d1d5db;
  font-size: 0.9rem;
  margin-bottom: 1rem;
  text-align: center;
}

.two-factor-container.light-theme .info-text {
  color: #6b7280;
}

.secret-box {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.secret-box label {
  color: #9ca3af;
  font-size: 0.85rem;
  font-weight: 600;
}

.two-factor-container.light-theme .secret-box label {
  color: #374151;
}

.secret-value {
  font-family: 'Courier New', monospace;
  background: rgba(0, 0, 0, 0.3);
  padding: 0.75rem;
  border-radius: 8px;
  color: #667eea;
  font-size: 0.9rem;
  letter-spacing: 2px;
  border: 1px solid rgba(102, 126, 234, 0.2);
}

.two-factor-container.light-theme .secret-value {
  background: white;
  border: 1px solid #e5e7eb;
  color: #667eea;
}

.btn-copy {
  padding: 0.5rem 1rem;
  background: rgba(102, 126, 234, 0.2);
  border: 1px solid rgba(102, 126, 234, 0.4);
  border-radius: 8px;
  color: #667eea;
  cursor: pointer;
  font-size: 0.85rem;
  transition: all 0.2s;
}

.btn-copy:hover {
  background: rgba(102, 126, 234, 0.3);
  border-color: rgba(102, 126, 234, 0.6);
}

.two-factor-container.light-theme .btn-copy {
  background: #667eea;
  border: 1px solid #667eea;
  color: white;
}

.two-factor-container.light-theme .btn-copy:hover {
  background: #5568d3;
  border-color: #5568d3;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.form-group label {
  color: #d1d5db;
  font-size: 0.9rem;
  font-weight: 500;
}

.two-factor-container.light-theme .form-group label {
  color: #374151;
}

.code-input {
  width: 100%;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.3);
  border: 2px solid rgba(102, 126, 234, 0.3);
  border-radius: 12px;
  color: #fff;
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
  box-shadow: 
    0 0 0 4px rgba(102, 126, 234, 0.1),
    0 0 20px rgba(102, 126, 234, 0.3);
  background: rgba(0, 0, 0, 0.5);
}

.two-factor-container.light-theme .code-input {
  background: #f9fafb;
  border: 2px solid #e5e7eb;
  color: #1f2937;
}

.two-factor-container.light-theme .code-input:focus {
  background: white;
  border-color: #667eea;
  box-shadow: 
    0 0 0 4px rgba(102, 126, 234, 0.1);
}

.btn-verify {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 12px;
  color: white;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-verify:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

.btn-verify:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-message {
  background: rgba(220, 38, 38, 0.2);
  border: 1px solid rgba(220, 38, 38, 0.4);
  color: #fca5a5;
  padding: 0.875rem;
  border-radius: 8px;
  font-size: 0.9rem;
  text-align: center;
}

.two-factor-container.light-theme .error-message {
  background: #fee2e2;
  border: 1px solid #fecaca;
  color: #dc2626;
}

.success-message {
  background: rgba(34, 197, 94, 0.2);
  border: 1px solid rgba(34, 197, 94, 0.4);
  color: #86efac;
  padding: 0.875rem;
  border-radius: 8px;
  font-size: 0.9rem;
  text-align: center;
}

.two-factor-container.light-theme .success-message {
  background: #d1fae5;
  border: 1px solid #a7f3d0;
  color: #059669;
}

.btn-back {
  margin-top: 1.5rem;
  width: 100%;
  padding: 0.75rem;
  background: transparent;
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 8px;
  color: #9ca3af;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-back:hover {
  border-color: rgba(102, 126, 234, 0.6);
  color: #d1d5db;
  background: rgba(102, 126, 234, 0.1);
}

.two-factor-container.light-theme .btn-back {
  border: 1px solid #e5e7eb;
  color: #6b7280;
}

.two-factor-container.light-theme .btn-back:hover {
  border-color: #667eea;
  color: #667eea;
  background: rgba(102, 126, 234, 0.05);
}
</style>
