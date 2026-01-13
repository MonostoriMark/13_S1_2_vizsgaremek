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
        role: data.role,
        isVerified: data.isVerified !== undefined ? data.isVerified : true
      }
      state.isAuthenticated = true
      
      localStorage.setItem('auth_token', data.token)
      localStorage.setItem('auth_user', JSON.stringify(state.user))
      
      return { success: true }
    } catch (error) {
      const responseData = error.response?.data || {}
      return {
        success: false,
        message: responseData.message || 'Login failed',
        email_verified: responseData.email_verified !== false
      }
    }
  }

  const registerUser = async (name, email, password) => {
    try {
      const data = await authService.registerUser(name, email, password)
      
      // DO NOT authenticate user - they must verify email first
      // Do not set token, user, or isAuthenticated
      // Do not store in localStorage
      
      return { 
        success: true,
        message: data.message || 'Regisztráció sikeres! Kérjük, erősítsd meg az e-mail címedet.',
        requiresVerification: true // Always require verification
      }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Registration failed'
      }
    }
  }

  const registerHotel = async (name, email, password, hotelName, location, type, starRating) => {
    try {
      const data = await authService.registerHotel(name, email, password, hotelName, location, type, starRating)
      
      // DO NOT authenticate user - they must verify email first
      // Do not set token, user, or isAuthenticated
      // Do not store in localStorage
      
      return { 
        success: true,
        message: data.message || 'Regisztráció sikeres! Kérjük, erősítsd meg az e-mail címedet.',
        requiresVerification: true // Always require verification
      }
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


