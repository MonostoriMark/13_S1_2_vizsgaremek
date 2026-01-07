<template>
  <div class="login-container">
    <div class="login-wrapper">
      <!-- Left Panel - Image with Overlay -->
      <div class="login-image-panel">
        <div class="image-overlay"></div>
        <div class="image-content">
          <h2 class="brand-title">HotelFlow.</h2>
          <h3 class="main-headline">WELCOME BACK</h3>
          <p class="tagline">Your Journey Starts Here</p>
          <p class="subtitle">Discover amazing hotels and create unforgettable memories</p>
        </div>
      </div>

      <!-- Right Panel - Form -->
      <div class="login-form-panel">
        <div class="login-card">
          <h1>Login</h1>
          <p class="form-subtitle">WELCOME BACK</p>
          <div v-if="error" class="error-message">{{ error }}</div>
          <form @submit.prevent="handleLogin">
            <div class="form-group">
              <label for="email">Email</label>
              <input
                id="email"
                v-model="email"
                type="email"
                required
                placeholder="Enter your email"
              />
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <input
                id="password"
                v-model="password"
                type="password"
                required
                placeholder="Enter your password"
              />
            </div>
            <div class="form-actions">
              <button type="button" class="btn-cancel" @click="$router.push('/search')">
                Cancel
              </button>
              <button type="submit" class="btn-primary" :disabled="loading">
                {{ loading ? 'Logging in...' : 'Login' }}
              </button>
            </div>
          </form>
          <p class="register-link">
            Don't have an account? <router-link to="/register">Register here</router-link>
          </p>
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

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
  error.value = ''
  loading.value = true

  const result = await authStore.login(email.value, password.value)

  if (result.success) {
    // Redirect based on role
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
.login-container {
  min-height: calc(100vh - 70px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-wrapper {
  display: flex;
  width: 100%;
  max-width: 1200px;
  min-height: 600px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}

/* Left Panel - Image */
.login-image-panel {
  flex: 1;
  position: relative;
  background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');
  background-size: cover;
  background-position: center;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 3rem;
  color: white;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.85) 0%, rgba(118, 75, 162, 0.85) 100%);
  z-index: 1;
}

.image-content {
  position: relative;
  z-index: 2;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.brand-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 2rem;
  letter-spacing: 1px;
}

.main-headline {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  line-height: 1.2;
  letter-spacing: 2px;
}

.tagline {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  opacity: 0.95;
}

.subtitle {
  font-size: 1rem;
  opacity: 0.9;
  line-height: 1.6;
  max-width: 400px;
}

/* Right Panel - Form */
.login-form-panel {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  background: #fafafa;
}

.login-card {
  width: 100%;
  max-width: 450px;
}

.login-card h1 {
  margin-bottom: 0.5rem;
  color: #667eea;
  font-size: 2rem;
  font-weight: 700;
}

.form-subtitle {
  color: #7f8c8d;
  font-size: 0.9rem;
  margin-bottom: 2rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #2c3e50;
  font-size: 0.95rem;
}

.form-group input {
  padding: 0.875rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  width: 100%;
  transition: all 0.3s ease;
  background: white;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-primary {
  flex: 1;
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-cancel {
  flex: 1;
  padding: 0.875rem 1.5rem;
  background: #764ba2;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel:hover {
  background: #5a3a7a;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(118, 75, 162, 0.3);
}

.register-link {
  text-align: center;
  margin-top: 1.5rem;
  color: #7f8c8d;
  font-size: 0.9rem;
}

.register-link a {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
}

.register-link a:hover {
  text-decoration: underline;
}

.error-message {
  color: #e74c3c;
  background-color: #fadbd8;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
}

/* Responsive Design */
@media (max-width: 968px) {
  .login-wrapper {
    flex-direction: column;
    min-height: auto;
  }

  .login-image-panel {
    min-height: 300px;
    padding: 2rem;
  }

  .main-headline {
    font-size: 2rem;
  }

  .login-form-panel {
    padding: 2rem;
  }
}

@media (max-width: 480px) {
  .login-container {
    padding: 1rem;
  }

  .login-wrapper {
    border-radius: 12px;
  }

  .login-image-panel {
    min-height: 250px;
    padding: 1.5rem;
  }

  .brand-title {
    font-size: 1.25rem;
  }

  .main-headline {
    font-size: 1.75rem;
  }

  .tagline {
    font-size: 1rem;
  }

  .login-form-panel {
    padding: 1.5rem;
  }

  .login-card h1 {
    font-size: 1.75rem;
  }

  .form-actions {
    flex-direction: column;
  }

  .btn-primary,
  .btn-cancel {
    width: 100%;
  }
}
</style>


