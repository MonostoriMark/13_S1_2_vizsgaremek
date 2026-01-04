import api from './api'

export const bookingService = {
  async createBooking(bookingData) {
    const response = await api.post('/bookings', bookingData)
    return response.data
  },

  async getBookingsByUserId(userId) {
    const response = await api.get(`/bookings/user/${userId}`)
    return response.data
  },

  async deleteBooking(id) {
    const response = await api.delete(`/bookings/delete/${id}`)
    return response.data
  },

  async updateBookingStatus(id, status) {
    const response = await api.put(`/bookings/update-status/${id}`, { status })
    return response.data
  },

  // For hotel admins - get bookings for their hotel
  // Note: This endpoint only returns confirmed bookings
  // For pending bookings, a backend extension would be needed
  async getBookingsByHotelId(hotelId) {
    try {
      const response = await api.get(`/devices/bookings/${hotelId}`)
      return response.data
    } catch (error) {
      // Handle 404 (no bookings) gracefully
      if (error.response?.status === 404) {
        return { bookings: [], rooms: [], relations: [] }
      }
      throw error
    }
  }
}

