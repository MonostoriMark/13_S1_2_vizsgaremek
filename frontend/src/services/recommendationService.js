import api from './api'

/**
 * Booking.com-style hotel recommendation service
 * Provides intelligent hotel recommendations based on search inputs
 */
export const recommendationService = {
  /**
   * Get hotel recommendations
   * 
   * @param {Object} params - Recommendation parameters
   * @param {string} params.city - City/location name (optional)
   * @param {string} params.check_in - Check-in date (optional, ISO format)
   * @param {string} params.check_out - Check-out date (optional, ISO format)
   * @param {number} params.guests - Number of guests (default: 1)
   * @param {number} params.limit - Maximum number of recommendations (default: 12, max: 12)
   * @returns {Promise<Object>} Recommendation response with hotels array
   */
  async getRecommendations({ city, check_in, check_out, guests = 1, limit = 9, offset = 0 }) {
    const params = {}
    
    if (city) params.city = city
    if (check_in) params.check_in = check_in
    if (check_out) params.check_out = check_out
    if (guests) params.guests = guests
    if (limit) params.limit = limit
    if (offset) params.offset = offset
    
    try {
      const response = await api.get('/recommendations', { params })
      return response.data
    } catch (error) {
      console.error('Failed to fetch recommendations:', error)
      // Return empty result on error (graceful degradation)
      return {
        hotels: [],
        count: 0,
        total: 0,
        has_more: false
      }
    }
  }
}
