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
    // Get all hotels and fetch their rooms with images
    const hotels = await this.getHotels()
    const hotelsWithDetails = await Promise.all(
      hotels.slice(0, 6).map(async (hotel) => {
        try {
          const rooms = await this.getRoomsByHotelId(hotel.id)
          // Get first image from first room
          let coverImage = null
          if (rooms && rooms.length > 0) {
            const firstRoom = rooms[0]
            if (firstRoom.id) {
              try {
                const imagesResponse = await api.get(`/rooms/${firstRoom.id}/images`)
                if (imagesResponse.data && imagesResponse.data.length > 0) {
                  let imageUrl = imagesResponse.data[0].url
                  // Handle relative URLs
                  if (imageUrl && imageUrl.startsWith('/storage/')) {
                    // Extract base URL (remove /api)
                    const baseUrl = API_BASE_URL.replace('/api', '')
                    imageUrl = `${baseUrl}${imageUrl}`
                  }
                  coverImage = imageUrl
                }
              } catch (e) {
                // Image fetch failed, use fallback
                console.warn('Failed to fetch images for room:', firstRoom.id, e)
              }
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


