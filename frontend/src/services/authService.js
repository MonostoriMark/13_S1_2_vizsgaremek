import api from './api'

export const authService = {
  async login(email, password, twoFactorCode = null) {
    const payload = { email, password }
    if (twoFactorCode) {
      payload.two_factor_code = twoFactorCode
    }
    const response = await api.post('/auth/login', payload)
    return response.data
  },

  async registerUser(name, email, password) {
    const response = await api.post('/auth/register-user', { name, email, password })
    return response.data
  },

  async registerHotel(name, email, password, hotelName, location, type, starRating) {
    const response = await api.post('/auth/register-hotel', {
      name,
      email,
      password,
      hotelName,
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
  },

  async updateUser(userId, userData) {
    const response = await api.put(`/auth/updateuser/${userId}`, userData)
    return response.data
  },

  async resendVerificationEmail(email) {
    const response = await api.post('/auth/resend-verification', { email })
    return response.data
  },

  async requestPasswordReset(email) {
    const response = await api.post('/auth/forgot-password', { email })
    return response.data
  },

  async resetPassword(token, password) {
    const response = await api.post('/auth/reset-password', { token, password })
    return response.data
  },

  async verify2FA(code) {
    const response = await api.post('/auth/verify-2fa', { code })
    return response.data
  },

  async enable2FA() {
    const response = await api.post('/auth/2fa/enable')
    return response.data
  },

  async verifyAndEnable2FA(code) {
    const response = await api.post('/auth/2fa/verify-enable', { code })
    return response.data
  },

  async disable2FA(password) {
    const response = await api.post('/auth/2fa/disable', { password })
    return response.data
  },

  async deleteAccount(userId, password) {
    const response = await api.post(`/auth/deleteuser/${userId}`, { password })
    return response.data
  }
}


