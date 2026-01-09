import { reactive } from 'vue'
import { authService } from '../services/authService'

const state = reactive({
  user: null,
  token: null,
  isAuthenticated: false
})

// Load from localStorage on init
const storedToken = localStorage.getItem('auth_token')
const storedUser = localStorage.getItem('auth_user')

if (storedToken && storedUser) {
  state.token = storedToken
  state.user = JSON.parse(storedUser)
  state.isAuthenticated = true
}

export const useAuthStore = () => {
  const login = async (email, password) => {
    try {
      const data = await authService.login(email, password)
      state.token = data.token
      state.user = {
        id: data.id,
        name: data.name,
        email: data.email,
        role: data.role
      }
      state.isAuthenticated = true
      
      localStorage.setItem('auth_token', data.token)
      localStorage.setItem('auth_user', JSON.stringify(state.user))
      
      return { success: true }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Login failed'
      }
    }
  }

  const registerUser = async (name, email, password) => {
    try {
      const data = await authService.registerUser(name, email, password)
      state.token = data.token
      state.user = {
        id: data.id,
        name: data.name,
        email: data.email,
        role: data.role
      }
      state.isAuthenticated = true
      
      localStorage.setItem('auth_token', data.token)
      localStorage.setItem('auth_user', JSON.stringify(state.user))
      
      return { success: true }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Registration failed'
      }
    }
  }

  const registerHotel = async (name, email, password, location, type, starRating) => {
    try {
      const data = await authService.registerHotel(name, email, password, location, type, starRating)
      state.token = data.token
      state.user = {
        id: data.id,
        name: data.name,
        email: data.email,
        role: data.role
      }
      state.isAuthenticated = true
      
      localStorage.setItem('auth_token', data.token)
      localStorage.setItem('auth_user', JSON.stringify(state.user))
      
      return { success: true }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Registration failed'
      }
    }
  }

  const logout = async () => {
    try {
      if (state.token) {
        await authService.logout()
      }
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      state.user = null
      state.token = null
      state.isAuthenticated = false
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }

  const updateUser = async (userId, userData) => {
    try {
      const data = await authService.updateUser(userId, userData)
      // Update local state
      if (state.user && state.user.id === userId) {
        state.user = {
          ...state.user,
          name: data.name || state.user.name,
          email: data.email || state.user.email
        }
        localStorage.setItem('auth_user', JSON.stringify(state.user))
      }
      return { success: true, data }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to update user'
      }
    }
  }

  return {
    state,
    login,
    registerUser,
    registerHotel,
    logout,
    updateUser
  }
}

// Export singleton instance
export const authStore = useAuthStore()


