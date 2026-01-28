import api from './api'

/**
 * Service for fetching all hotels with rooms in a single request
 * This provides a complete dataset for client-side filtering and ranking
 */
export const hotelDataService = {
  /**
   * Get all hotels with rooms in one request
   * @returns {Promise<Object>} Complete hotels dataset
   */
  async getAllHotelsWithRooms() {
    try {
      const response = await api.get('/hotels/all-with-rooms')
      return response.data
    } catch (error) {
      console.error('Failed to fetch hotels data:', error)
      throw error
    }
  }
}
