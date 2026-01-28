import api from './api'

export const searchService = {
  async getLocations() {
    const response = await api.get('/search/locations')
    return response.data
  },

  async search(city, startDate, endDate, guests) {
    const response = await api.get('/search', {
      params: { city, startDate, endDate, guests }
    })
    return response.data
  }
}


