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
        <h1>Profilom</h1>
        <p class="profile-subtitle">Fiók információk kezelése</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Profil betöltése...</p>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="error-message">{{ error }}</div>

      <!-- Success Message -->
      <div v-if="successMessage" class="success-message">{{ successMessage }}</div>

      <!-- Profile Form -->
      <div v-if="!loading && authStore.state.user" class="profile-form-container">
        <form @submit.prevent="handleSubmit" class="profile-form">
          <div class="form-section">
            <h2 class="section-title">Személyes információk</h2>
            
            <div class="form-group">
              <label for="name">Teljes név</label>
              <input
                id="name"
                v-model="formData.name"
                type="text"
                class="form-input"
                placeholder="Adja meg a teljes nevét"
                required
              />
            </div>

            <div class="form-group">
              <label for="email">E-mail cím</label>
              <input
                id="email"
                v-model="formData.email"
                type="email"
                class="form-input"
                placeholder="Adja meg az e-mail címét"
                required
              />
            </div>
          </div>

          <div class="form-section">
            <h2 class="section-title">Kétfaktoros hitelesítés</h2>
            <p class="section-description">További biztonsági réteg hozzáadása a fiókjához</p>
            
            <div class="form-group">
              <div class="two-factor-toggle">
                <div class="toggle-info">
                  <label for="twoFactorEnabled">Kétfaktoros hitelesítés</label>
                  <span class="toggle-status" :class="{ enabled: formData.two_factor_enabled }">
                    {{ formData.two_factor_enabled ? 'Engedélyezve' : 'Letiltva' }}
                  </span>
                </div>
                <label class="switch-toggle">
                  <input
                    id="twoFactorEnabled"
                    v-model="formData.two_factor_enabled"
                    type="checkbox"
                    @change="handle2FAToggle"
                    :disabled="twoFALoading"
                  />
                  <span class="slider-toggle"></span>
                </label>
                <p v-if="authStore.state.user?.role === 'hotel' && !formData.two_factor_enabled" class="twofa-required-note">
                  ⚠️ A szálloda adminisztrátoroknak engedélyezniük kell a 2FA-t az admin felület eléréséhez. A beállítási ablak automatikusan megjelenik.
                </p>
                <p v-if="authStore.state.user?.role === 'hotel' && formData.two_factor_enabled" class="twofa-required-note" style="color: #22c55e; background: rgba(34, 197, 94, 0.1); border-left-color: #22c55e;">
                  ✓ A 2FA engedélyezve van. Teljes hozzáférése van az admin felülethez.
                </p>
              </div>
              
              <!-- 2FA Setup Modal -->
              <Transition name="modal">
                <div v-if="show2FASetup" class="modal-overlay" :class="{ 'non-dismissible': authStore.state.user?.role === 'hotel' && !(formData.two_factor_enabled || authStore.state.user?.two_factor_enabled) }" @click.self="handleModalClick">
                  <div class="modal-content-2fa">
                    <div class="modal-header-2fa">
                      <h3>Kétfaktoros hitelesítés beállítása</h3>
                      <button v-if="!(authStore.state.user?.role === 'hotel' && !(formData.two_factor_enabled || authStore.state.user?.two_factor_enabled))" class="modal-close" @click="close2FASetup">×</button>
                    </div>
                    <div class="modal-body-2fa">
                      <div v-if="twoFAError" class="error-message">{{ twoFAError }}</div>
                      <div v-if="twoFASuccess" class="success-message">{{ twoFASuccess }}</div>
                      
                      <div v-if="!twoFAQRCode" class="setup-instructions">
                        <p>Kattintson az alábbi gombra a QR kód generálásához az autentikátor alkalmazáshoz.</p>
                        <button @click="generate2FAQR" class="btn-generate-qr" :disabled="twoFALoading">
                          {{ twoFALoading ? 'Generálás...' : 'QR kód generálása' }}
                        </button>
                      </div>
                      
                      <div v-if="twoFAQRCode" class="qr-setup">
                        <div class="qr-container">
                          <img :src="twoFAQRCode" alt="QR kód" class="qr-code-image" />
                        </div>
                        <div class="secret-info">
                          <p class="info-text">Olvassa be ezt a QR kódot az autentikátor alkalmazásával (Google Authenticator, Authy stb.)</p>
                          <div class="secret-box">
                            <label>Titkos kulcs:</label>
                            <div class="secret-value">{{ twoFASecret }}</div>
                            <button @click="copySecret" class="btn-copy">📋 Másolás</button>
                          </div>
                        </div>
                        <div class="form-group">
                          <label>Adja meg a 6 számjegyű kódot az alkalmazásból</label>
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
                          {{ twoFALoading ? 'Ellenőrzés...' : 'Ellenőrzés és engedélyezés' }}
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
                      <h3>Kétfaktoros hitelesítés letiltása</h3>
                      <button class="modal-close" @click="close2FADisable">×</button>
                    </div>
                    <div class="modal-body-2fa">
                      <div v-if="twoFAError" class="error-message">{{ twoFAError }}</div>
                      <p class="warning-text">⚠️ A 2FA letiltása csökkenti a fiók biztonságát.</p>
                      <div class="form-group">
                        <label>Adja meg a jelszavát a megerősítéshez</label>
                        <input
                          v-model="disablePassword"
                          type="password"
                          placeholder="Adja meg a jelszavát"
                          class="form-input"
                        />
                      </div>
                      <div class="modal-footer-2fa">
                        <button @click="close2FADisable" class="btn-secondary">Mégse</button>
                        <button @click="confirmDisable2FA" class="btn-danger" :disabled="!disablePassword || twoFALoading">
                          {{ twoFALoading ? 'Letiltás...' : '2FA letiltása' }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </Transition>
            </div>
          </div>

          <div class="form-section">
            <h2 class="section-title">Jelszó megváltoztatása</h2>
            <p class="section-description">Hagyja üresen, ha nem szeretné megváltoztatni a jelszavát</p>
            
            <div class="form-group">
              <label for="password">Új jelszó</label>
              <input
                id="password"
                v-model="formData.password"
                type="password"
                class="form-input"
                placeholder="Adja meg az új jelszót (min. 8 karakter)"
                :minlength="8"
              />
            </div>

            <div class="form-group">
              <label for="confirmPassword">Új jelszó megerősítése</label>
              <input
                id="confirmPassword"
                v-model="formData.confirmPassword"
                type="password"
                class="form-input"
                placeholder="Erősítse meg az új jelszót"
                :minlength="8"
              />
            </div>
          </div>

          <div class="form-section danger-zone">
            <h2 class="section-title danger-title">Veszélyes zóna</h2>
            <p class="section-description">Visszafordíthatatlan és destruktív műveletek</p>
            
            <div class="form-group">
              <button @click="showDeleteAccountModal = true" class="btn-delete-account">
                <span class="delete-icon">🗑️</span>
                Fiókom törlése
              </button>
              <p class="delete-warning">A fiók törlése után nincs visszaút. Kérjük, legyen biztos benne.</p>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" @click="handleCancel" class="btn-cancel">
              Mégse
            </button>
            <button type="submit" class="btn-submit" :disabled="saving">
              <span v-if="saving">Mentés...</span>
              <span v-else>Változások mentése</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Account Modal -->
    <Transition name="modal">
      <div v-if="showDeleteAccountModal" class="modal-overlay" @click.self="closeDeleteAccountModal">
        <div class="modal-content-2fa">
          <div class="modal-header-2fa">
            <h3>Fiók törlése</h3>
            <button class="modal-close" @click="closeDeleteAccountModal">×</button>
          </div>
          <div class="modal-body-2fa">
            <div v-if="deleteAccountError" class="error-message">{{ deleteAccountError }}</div>
            <p class="warning-text">⚠️ Ez a művelet nem vonható vissza. Ez véglegesen törli a fiókját és eltávolítja az összes adatát a szervereinkről.</p>
            <div class="form-group">
              <label>Adja meg a jelszavát a megerősítéshez</label>
              <input
                v-model="deleteAccountPassword"
                type="password"
                placeholder="Adja meg a jelszavát"
                class="form-input"
              />
            </div>
            <div class="modal-footer-2fa">
              <button @click="closeDeleteAccountModal" class="btn-secondary">Mégse</button>
              <button @click="confirmDeleteAccount" class="btn-danger" :disabled="!deleteAccountPassword || deletingAccount">
                {{ deletingAccount ? 'Törlés...' : 'Fiók törlése' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
   
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { authService } from '../services/authService'

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

// 2FA state
const show2FASetup = ref(false)
const show2FADisable = ref(false)
const twoFALoading = ref(false)
const twoFAError = ref('')
const twoFASuccess = ref('')
const twoFAQRCode = ref('')
const twoFASecret = ref('')
const twoFACode = ref('')
const disablePassword = ref('')

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
  confirmPassword: '',
  two_factor_enabled: false
})

const loadUserData = async () => {
  try {
    const userData = await authService.getMe()
    formData.value.two_factor_enabled = userData.two_factor_enabled || false
  } catch (err) {
    console.error('Failed to load user data:', err)
  }
}

onMounted(async () => {
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
      confirmPassword: '',
      two_factor_enabled: authStore.state.user.two_factor_enabled || false
    }
  }

  await loadUserData()

  // Auto-open 2FA setup modal for hotel admins if 2FA is not enabled
  // Check both formData value and auth store value
  const has2FA = formData.value.two_factor_enabled || authStore.state.user?.two_factor_enabled
  if (authStore.state.user?.role === 'hotel' && !has2FA) {
    // Small delay to ensure page is fully loaded
    setTimeout(async () => {
      show2FASetup.value = true
      // Auto-generate QR code
      await generate2FAQR()
    }, 300)
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
      error.value = 'A jelszónak legalább 8 karakter hosszúnak kell lennie'
      return
    }
    if (formData.value.password !== formData.value.confirmPassword) {
      error.value = 'A jelszavak nem egyeznek'
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
      successMessage.value = 'Profil sikeresen frissítve!'
      showToast('Profil sikeresen frissítve!', 'success')
      
      // Clear password fields
      formData.value.password = ''
      formData.value.confirmPassword = ''

      // Redirect after a short delay
      setTimeout(() => {
        router.push('/search')
      }, 1500)
    } else {
      error.value = result.message || 'A profil frissítése sikertelen'
      showToast(result.message || 'A profil frissítése sikertelen', 'error')
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Hiba történt a profil frissítése során'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const handleCancel = () => {
  router.push('/search')
}

// 2FA Methods
const handle2FAToggle = async () => {
  // When toggle is turned ON (enabling 2FA)
  if (formData.value.two_factor_enabled) {
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
      showToast('A szálloda adminisztrátorok biztonsági okokból nem tilthatják le a 2FA-t', 'error')
      formData.value.two_factor_enabled = true
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
    twoFAError.value = err.response?.data?.message || 'A QR kód generálása sikertelen'
    formData.value.two_factor_enabled = false
  } finally {
    twoFALoading.value = false
  }
}

const format2FACode = (e) => {
  twoFACode.value = e.target.value.replace(/\D/g, '').slice(0, 6)
}

const copySecret = () => {
  navigator.clipboard.writeText(twoFASecret.value)
  twoFASuccess.value = 'Titkos kulcs a vágólapra másolva!'
  setTimeout(() => {
    twoFASuccess.value = ''
  }, 2000)
}

const verifyAndEnable2FA = async () => {
  if (twoFACode.value.length !== 6) {
    twoFAError.value = 'Kérjük, adjon meg egy 6 számjegyű kódot'
    return
  }

  twoFALoading.value = true
  twoFAError.value = ''
  twoFASuccess.value = ''

  try {
    await authService.verifyAndEnable2FA(twoFACode.value)
    twoFASuccess.value = 'A 2FA sikeresen engedélyezve!'
    showToast('A 2FA sikeresen engedélyezve!', 'success')
    
    // Update auth store
    if (authStore.state.user) {
      authStore.state.user.two_factor_enabled = true
      localStorage.setItem('auth_user', JSON.stringify(authStore.state.user))
    }
    
    // Reload user data to update 2FA status
    await loadUserData()
    
    // Update form and auth store to reflect enabled state
    formData.value.two_factor_enabled = true
    if (authStore.state.user) {
      authStore.state.user.two_factor_enabled = true
      localStorage.setItem('auth_user', JSON.stringify(authStore.state.user))
    }
    
    setTimeout(() => {
      close2FASetup()
      // If user is hotel admin, reload to remove blocker
      if (authStore.state.user?.role === 'hotel') {
        window.location.reload()
      }
    }, 1500)
  } catch (err) {
    twoFAError.value = err.response?.data?.message || 'Érvénytelen kód. Kérjük, próbálja újra.'
    formData.value.two_factor_enabled = false
  } finally {
    twoFALoading.value = false
  }
}

const confirmDisable2FA = async () => {
  if (!disablePassword.value) {
    twoFAError.value = 'Kérjük, adja meg a jelszavát'
    return
  }

  twoFALoading.value = true
  twoFAError.value = ''

  try {
    await authService.disable2FA(disablePassword.value)
    twoFASuccess.value = 'A 2FA sikeresen letiltva!'
    showToast('A 2FA sikeresen letiltva!', 'success')
    
    // Update auth store
    if (authStore.state.user) {
      authStore.state.user.two_factor_enabled = false
      localStorage.setItem('auth_user', JSON.stringify(authStore.state.user))
    }
    
    formData.value.two_factor_enabled = false
    
    setTimeout(async () => {
      close2FADisable()
      await loadUserData()
    }, 1500)
  } catch (err) {
    twoFAError.value = err.response?.data?.message || 'A 2FA letiltása sikertelen'
    formData.value.two_factor_enabled = true
  } finally {
    twoFALoading.value = false
  }
}

const handleModalClick = () => {
  // Prevent closing if hotel admin and 2FA not enabled
  const has2FA = formData.value.two_factor_enabled || authStore.state.user?.two_factor_enabled
  if (authStore.state.user?.role === 'hotel' && !has2FA) {
    return
  }
  close2FASetup()
}

const close2FASetup = () => {
  // Don't allow closing if hotel admin hasn't enabled 2FA
  const has2FA = formData.value.two_factor_enabled || authStore.state.user?.two_factor_enabled
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
  if (!formData.value.two_factor_enabled && !authStore.state.user?.two_factor_enabled) {
    formData.value.two_factor_enabled = false
  }
}

const close2FADisable = () => {
  show2FADisable.value = false
  disablePassword.value = ''
  twoFAError.value = ''
  // If user closed without disabling, reset toggle
  formData.value.two_factor_enabled = true
}

// Account Deletion
const showDeleteAccountModal = ref(false)
const deleteAccountPassword = ref('')
const deletingAccount = ref(false)
const deleteAccountError = ref('')

const confirmDeleteAccount = async () => {
  if (!deleteAccountPassword.value) {
    deleteAccountError.value = 'Kérjük, adja meg a jelszavát'
    return
  }

  deletingAccount.value = true
  deleteAccountError.value = ''

  try {
    await authService.deleteAccount(authStore.state.user.id, deleteAccountPassword.value)
    showToast('Fiók sikeresen törölve', 'success')
    
    // Logout and redirect
    await authStore.logout()
    setTimeout(() => {
      router.push('/login')
    }, 1500)
  } catch (err) {
    deleteAccountError.value = err.response?.data?.message || 'A fiók törlése sikertelen'
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
