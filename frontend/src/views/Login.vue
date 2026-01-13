<template>
  <div class="login-page">
    <!-- Home Button -->
    <router-link to="/" class="home-button">
      <span class="home-icon">🏠</span>
      <span class="home-text">Home</span>
    </router-link>
    
    <!-- Minimal Card -->
    <div class="login-card">
      <div class="card-content">
            <div class="welcome-header">
              <div class="travel-icon-wrapper">
                <div class="travel-icon">✈️</div>
                <div class="icon-glow"></div>
              </div>
              <h1>Welcome</h1>
              <p class="welcome-subtitle">Login with Email</p>
            </div>

            <div v-if="error" class="error-message">
              <span class="error-icon">⚠️</span>
              {{ error }}
            </div>

            <form @submit.prevent="handleLogin" class="login-form">
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
                    placeholder="••••••••••"
                    class="glass-input"
                  />
                  <div class="input-glow"></div>
                </div>
              </div>

              <div class="forgot-password">
                <a href="#" class="forgot-link">Forgot your password?</a>
              </div>

              <button type="submit" class="btn-login" :disabled="loading">
                <span v-if="loading" class="loading-spinner"></span>
                <span v-else>{{ loading ? 'Logging in...' : 'LOGIN' }}</span>
                <div class="button-glow"></div>
              </button>

              <p class="register-link">
                Don't have account? <router-link to="/register">Register Now</router-link>
              </p>
            </form>
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

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
  error.value = ''
  loading.value = true

  const result = await authStore.login(email.value, password.value)

  if (result.success) {
    if (authStore.state.user.role === 'user') {
      router.push('/bookings')
    } else if (authStore.state.user.role === 'hotel') {
      router.push('/admin/bookings')
    } else {
      router.push('/search')
    }
  } else {
    error.value = result.message || 'Login failed'
  }

  loading.value = false
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
.login-card {
  width: 100%;
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
  align-items: center;
  gap: 0.5rem;
}

.error-icon {
  font-size: 1.1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .login-page {
    padding: 1rem;
  }

  .login-card {
    padding: 2rem 1.5rem;
    max-width: 100%;
  }

  .welcome-header h1 {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .login-page {
    padding: 1rem;
  }

  .login-card {
    padding: 1.5rem 1.25rem;
    max-width: 100%;
  }

  .welcome-header h1 {
    font-size: 1.375rem;
  }
}
</style>
