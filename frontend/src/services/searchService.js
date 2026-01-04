import api from './api'

export const searchService = {
  async search(city, startDate, endDate, guests) {
    const response = await api.get('/search', {
      params: { city, startDate, endDate, guests }
    })
    return response.data
  }
}


