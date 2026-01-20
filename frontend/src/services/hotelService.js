import api from './api'

export const hotelService = {
  async getHotels() {
    const response = await api.get('/hotels')
    return response.data
  },

  async getHotelById(id) {
    const response = await api.get(`/hotels/${id}`)
    return response.data
  },

  async getRoomsByHotelId(hotelId) {
    const response = await api.get(`/rooms/hotel/${hotelId}`)
    return response.data
  }
}


