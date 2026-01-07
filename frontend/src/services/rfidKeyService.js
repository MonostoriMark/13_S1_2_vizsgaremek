import api from './api'

export const rfidKeyService = {
  async getKeys(filters = {}) {
    const params = new URLSearchParams()
    if (filters.status) params.append('status', filters.status)
    if (filters.search) params.append('search', filters.search)

    const queryString = params.toString()
    const url = queryString ? `/rfid-keys?${queryString}` : '/rfid-keys'
    const response = await api.get(url)
    return response.data
  },

  async getKeyById(id) {
    const response = await api.get(`/rfid-keys/${id}`)
    return response.data
  },

  async createKey(data) {
    const response = await api.post('/rfid-keys', data)
    return response.data
  },

  async updateKey(id, data) {
    const response = await api.patch(`/rfid-keys/${id}`, data)
    return response.data
  },

  async deleteKey(id) {
    const response = await api.delete(`/rfid-keys/${id}`)
    return response.data
  },

  async assignKey(keyId, bookingId, roomId) {
    const response = await api.post(`/rfid-keys/${keyId}/assign`, {
      booking_id: bookingId,
      room_id: roomId
    })
    return response.data
  },

  async releaseKey(keyId) {
    const response = await api.post(`/rfid-keys/${keyId}/release`)
    return response.data
  },

  async getAvailableBookings() {
    const response = await api.get('/rfid-keys/bookings')
    return response.data
  }
}
