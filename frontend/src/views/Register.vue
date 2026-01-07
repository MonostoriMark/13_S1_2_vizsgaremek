<template>
  <div class="register-container">
    <div class="register-wrapper">
      <!-- Left Panel - Image with Overlay -->
      <div class="register-image-panel">
        <div class="image-overlay"></div>
        <div class="image-content">
          <h2 class="brand-title">HotelFlow.</h2>
          <h3 class="main-headline">START YOUR JOURNEY</h3>
          <p class="tagline">Explore Iconic Destinations</p>
          <p class="subtitle">Join thousands of travelers discovering amazing places around the world</p>
        </div>
      </div>

      <!-- Right Panel - Form -->
      <div class="register-form-panel">
        <div class="register-card">
          <h1>Registration</h1>
          <p class="form-subtitle">START YOUR JOURNEY</p>
          <div v-if="error" class="error-message">{{ error }}</div>
          <form @submit.prevent="handleRegister">
            <div class="form-group">
              <label for="name">Name</label>
              <input
                id="name"
                v-model="name"
                type="text"
                required
                placeholder="Enter your name"
              />
            </div>
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
                minlength="8"
                placeholder="Minimum 8 characters"
              />
            </div>
            <div class="form-group">
              <label for="userType">Register as</label>
              <select id="userType" v-model="userType" required>
                <option value="user">Guest</option>
                <option value="hotel">Hotel Admin</option>
              </select>
            </div>

            <!-- Hotel-specific fields -->
            <template v-if="userType === 'hotel'">
              <div class="form-group">
                <label for="location">Location</label>
                <input
                  id="location"
                  v-model="location"
                  type="text"
                  required
                  placeholder="City, Country"
                />
              </div>
              <div class="form-group">
                <label for="type">Hotel Type</label>
                <select id="type" v-model="type" required>
                  <option value="hotel">Hotel</option>
                  <option value="apartment">Apartment</option>
                  <option value="villa">Villa</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="form-group">
                <label for="starRating">Star Rating (optional)</label>
                <select id="starRating" v-model.number="starRating">
                  <option :value="null">None</option>
                  <option value="1">1 Star</option>
                  <option value="2">2 Stars</option>
                  <option value="3">3 Stars</option>
                  <option value="4">4 Stars</option>
                  <option value="5">5 Stars</option>
                </select>
              </div>
            </template>

            <div class="form-actions">
              <button type="button" class="btn-cancel" @click="$router.push('/search')">
                Cancel
              </button>
              <button type="submit" class="btn-primary" :disabled="loading">
                {{ loading ? 'Registering...' : 'Confirm' }}
              </button>
            </div>
          </form>
          <p class="login-link">
            Already have an account? <router-link to="/login">Login here</router-link>
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
    // Redirect based on role
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
.register-container {
  min-height: calc(100vh - 70px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.register-wrapper {
  display: flex;
  width: 100%;
  max-width: 1200px;
  min-height: 700px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}

/* Left Panel - Image */
.register-image-panel {
  flex: 1;
  position: relative;
  background-image: url('https://images.unsplash.com/photo-1488646953014-85cb44e25828?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');
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
.register-form-panel {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  background: #fafafa;
  overflow-y: auto;
}

.register-card {
  width: 100%;
  max-width: 500px;
}

.register-card h1 {
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

.form-group input,
.form-group select {
  padding: 0.875rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  width: 100%;
  transition: all 0.3s ease;
  background: white;
}

.form-group input:focus,
.form-group select:focus {
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

.login-link {
  text-align: center;
  margin-top: 1.5rem;
  color: #7f8c8d;
  font-size: 0.9rem;
}

.login-link a {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
}

.login-link a:hover {
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
  .register-wrapper {
    flex-direction: column;
    min-height: auto;
  }

  .register-image-panel {
    min-height: 300px;
    padding: 2rem;
  }

  .main-headline {
    font-size: 2rem;
  }

  .register-form-panel {
    padding: 2rem;
  }
}

@media (max-width: 480px) {
  .register-container {
    padding: 1rem;
  }

  .register-wrapper {
    border-radius: 12px;
  }

  .register-image-panel {
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

  .register-form-panel {
    padding: 1.5rem;
  }

  .register-card h1 {
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


