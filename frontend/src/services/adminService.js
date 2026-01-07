import api from './api'

export const adminService = {
  // Hotels
  async getHotels() {
    const response = await api.get('/hotels')
    return response.data
  },

  async getHotelById(id) {
    const response = await api.get(`/hotels/${id}`)
    return response.data
  },

  async createHotel(data) {
    const response = await api.post('/hotels', data)
    return response.data
  },

  async updateHotel(id, data) {
    const response = await api.put(`/hotels/upgrade/${id}`, data)
    return response.data
  },

  async deleteHotel(id) {
    const response = await api.delete(`/hotels/delete/${id}`)
    return response.data
  },

  // Rooms
  async getRoomsByHotelId(hotelId) {
    const response = await api.get(`/rooms/hotel/${hotelId}`)
    return response.data
  },

  async getRoomById(id) {
    const response = await api.get(`/rooms/${id}`)
    return response.data
  },

  async createRoom(hotelId, data) {
    const response = await api.post(`/rooms/create/${hotelId}`, {
      ...data,
      hotels_id: hotelId
    })
    return response.data
  },

  async updateRoom(id, data) {
    const response = await api.put(`/rooms/update/${id}`, data)
    return response.data
  },

  async deleteRoom(id) {
    const response = await api.delete(`/rooms/delete/${id}`)
    return response.data
  },

  // Services
  async getServicesByHotelId(hotelId) {
    // Note: This endpoint may need to be created in the backend
    // For now, we'll use a mock or create the endpoint
    try {
      const response = await api.get(`/services/hotel/${hotelId}`)
      return response.data
    } catch (error) {
      if (error.response?.status === 404) {
        // Endpoint doesn't exist yet, return empty array
        return []
      }
      throw error
    }
  },

  async getServiceById(id) {
    try {
      const response = await api.get(`/services/${id}`)
      return response.data
    } catch (error) {
      if (error.response?.status === 404) {
        return null
      }
      throw error
    }
  },

  async createService(hotelId, data) {
    // Note: This endpoint may need to be created in the backend
    const response = await api.post(`/services`, {
      ...data,
      hotels_id: hotelId
    })
    return response.data
  },

  async updateService(id, data) {
    const response = await api.put(`/services/${id}`, data)
    return response.data
  },

  async deleteService(id) {
    const response = await api.delete(`/services/${id}`)
    return response.data
  },

  // Images
  async uploadImage(file, roomIds = []) {
    const formData = new FormData()
    formData.append('image', file)
    formData.append('rooms', JSON.stringify(roomIds))

    const response = await api.post('/images', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  },

  async linkImage(imageId, roomIds) {
    const response = await api.post('/images/link', {
      imageId,
      rooms: roomIds
    })
    return response.data
  },

  async unlinkImage(imageId, roomId) {
    const response = await api.delete('/images/unlink', {
      data: {
        imageId,
        roomId
      }
    })
    return response.data
  },

  async deleteImage(id) {
    const response = await api.delete(`/images/${id}`)
    return response.data
  },

  async getRoomImages(roomId) {
    const response = await api.get(`/rooms/${roomId}/images`)
    return response.data
  },

  async getImageById(id) {
    const response = await api.get(`/images/${id}`)
    return response.data
  }
}
