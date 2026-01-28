import api from './api'

export const rfidKeyService = {
  async getKeys(filters = {}) {
    const params = new URLSearchParams()
    if (filters.hotel_id) params.append('hotel_id', filters.hotel_id)
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
  },

  // Manual assignment of RFID key to one or more rooms for custom period / lifetime
  async assignKeyToRoom(keyId, { room_ids, start_date, end_date, lifetime }) {
    const response = await api.post(`/rfid-keys/${keyId}/assign-room`, {
      room_ids,
      start_date,
      end_date,
      lifetime,
    })
    return response.data
  },

  // Get RFID key assignments for calendar view
  async getCalendarEvents(params = {}) {
    const response = await api.get('/rfid-keys/calendar', { params })
    return response.data
  }
}
