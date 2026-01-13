import api from './api'
import { API_BASE_URL } from '../config/api'

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
  },

  async getServicesByHotelId(hotelId) {
    try {
      const response = await api.get(`/services/hotel/${hotelId}`)
      return response.data
    } catch (error) {
      if (error.response?.status === 404) {
        return []
      }
      throw error
    }
  },

  async getRecommendedHotels() {
    // Get all hotels (now includes cover_image and rooms with images)
    const hotels = await this.getHotels()
    const hotelsWithDetails = await Promise.all(
      hotels.slice(0, 6).map(async (hotel) => {
        try {
          const rooms = await this.getRoomsByHotelId(hotel.id)
          
          // Priority: hotel cover_image > first room's first image
          let coverImage = null
          
          // First check hotel cover_image
          if (hotel.cover_image) {
            const baseUrl = API_BASE_URL.replace('/api', '')
            coverImage = hotel.cover_image.startsWith('/storage/')
              ? `${baseUrl}${hotel.cover_image}`
              : hotel.cover_image
          } else if (rooms && rooms.length > 0) {
            // Fallback to first room's first image
            const firstRoom = rooms[0]
            if (firstRoom.images && firstRoom.images.length > 0) {
              let imageUrl = firstRoom.images[0].url
              if (imageUrl && imageUrl.startsWith('/storage/')) {
                const baseUrl = API_BASE_URL.replace('/api', '')
                imageUrl = `${baseUrl}${imageUrl}`
              }
              coverImage = imageUrl
            }
          }
          
          // Calculate starting price from rooms
          const startingPrice = rooms && rooms.length > 0
            ? Math.min(...rooms.map(r => r.pricePerNight || r.basePrice || 0))
            : null

          return {
            ...hotel,
            coverImage,
            startingPrice,
            roomCount: rooms?.length || 0
          }
        } catch (error) {
          console.warn('Failed to load hotel details:', hotel.id, error)
          return {
            ...hotel,
            coverImage: null,
            startingPrice: null,
            roomCount: 0
          }
        }
      })
    )
    return hotelsWithDetails
  }
}


