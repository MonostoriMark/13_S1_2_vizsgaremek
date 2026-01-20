import api from './api'

export const authService = {
  async login(email, password) {
    const response = await api.post('/auth/login', { email, password })
    return response.data
  },

  async registerUser(name, email, password) {
    const response = await api.post('/auth/register-user', { name, email, password })
    return response.data
  },

  async registerHotel(name, email, password, location, type, starRating) {
    const response = await api.post('/auth/register-hotel', {
      name,
      email,
      password,
      location,
      type,
      starRating
    })
    return response.data
  },

  async logout() {
    await api.post('/auth/logout')
  },

  async getMe() {
    const response = await api.get('/auth/me')
    return response.data
  }
}


