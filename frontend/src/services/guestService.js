import api from './api'

export const guestService = {
  async addGuests(bookingId, guests) {
    const response = await api.post(`/bookings/add-guest/${bookingId}`, { guests })
    return response.data
  },

  async getGuestsByBookingId(bookingId) {
    const response = await api.get(`/guests/booking/${bookingId}`)
    return response.data
  },

  async updateGuest(guestId, guestData) {
    const response = await api.put(`/guests/update/${guestId}`, guestData)
    return response.data
  },

  async deleteGuest(guestId) {
    const response = await api.delete(`/guests/delete/${guestId}`)
    return response.data
  }
}
