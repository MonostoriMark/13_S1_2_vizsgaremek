<template>
  <div class="forgot-password-page">
    <router-link to="/login" class="home-button">
      <span class="home-icon">🔑</span>
      <span class="home-text">Bejelentkezés</span>
    </router-link>
    
    <div class="forgot-password-card">
      <div class="card-content">
        <div class="welcome-header">
          <div class="travel-icon-wrapper">
            <div class="travel-icon">🔐</div>
          </div>
          <h1>2FA helyreállítás</h1>
          <p class="welcome-subtitle">Ha elvesztetted a telefonod, kérj helyreállítási linket e-mailben.</p>
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
            </div>
          </div>

          <button type="submit" class="btn-submit" :disabled="loading">
            <span v-if="loading" class="loading-spinner"></span>
            <span v-else>{{ loading ? 'Küldés...' : 'HELYREÁLLÍTÁSI LINK KÉRÉSE' }}</span>
          </button>

          <p class="back-link">
            Inkább bejelentkeznél? <router-link to="/login">Vissza a bejelentkezéshez</router-link>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { authService } from '../services/authService'

const email = ref('')
const error = ref('')
const successMessage = ref('')
const loading = ref(false)

const handleSubmit = async () => {
  error.value = ''
  successMessage.value = ''
  loading.value = true
  try {
    const res = await authService.requestTwoFactorRecovery(email.value)
    successMessage.value = res?.message || 'Ha a fiókhoz tartozik 2FA, a helyreállítási linket elküldtük e-mailben.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Hiba történt. Kérjük, próbáld újra később.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.forgot-password-page {
  min-height: 100vh;
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
}

.forgot-password-card {
  flex: 1;
  max-width: 460px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  padding: 2.5rem 2rem;
}

.welcome-header {
  text-align: center;
  margin-bottom: 2rem;
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
}

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
  margin-top: 1.25rem;
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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

.back-link {
  text-align: center;
  color: #6b7280;
  font-size: 0.875rem;
  margin: 1rem 0 0;
}

.back-link a {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
}

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
</style>

