<template>
  <div class="register-container">
    <div class="register-card">
      <h1>Register</h1>
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

        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? 'Registering...' : 'Register' }}
        </button>
      </form>
      <p class="login-link">
        Already have an account? <router-link to="/login">Login here</router-link>
      </p>
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
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
  padding: 2rem 0;
}

.register-card {
  background: white;
  border-radius: 8px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  width: 100%;
  max-width: 500px;
}

.register-card h1 {
  margin-bottom: 1.5rem;
  text-align: center;
  color: #2c3e50;
}

.login-link {
  text-align: center;
  margin-top: 1rem;
  color: #7f8c8d;
}

.login-link a {
  color: #3498db;
  text-decoration: none;
}

.login-link a:hover {
  text-decoration: underline;
}
</style>


