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
    if (!file) {
      throw new Error('No file provided')
    }

    // Check if file is actually a File object
    if (!(file instanceof File) && !(file instanceof Blob)) {
      console.error('Invalid file object:', file, 'Type:', typeof file)
      throw new Error('Invalid file object provided. Expected File or Blob.')
    }

    if (!roomIds || roomIds.length === 0) {
      throw new Error('At least one room ID is required')
    }

    const formData = new FormData()
    
    // Append file with correct field name
    formData.append('image', file, file.name || 'image.jpg')
    
    // Send each room ID separately for Laravel to parse as array
    // Laravel expects rooms[0], rooms[1], etc. format
    roomIds.forEach((roomId, index) => {
      const roomIdInt = parseInt(roomId)
      if (isNaN(roomIdInt)) {
        console.error('Invalid room ID:', roomId)
        throw new Error(`Invalid room ID: ${roomId}`)
      }
      formData.append(`rooms[${index}]`, roomIdInt)
    })

    // Debug: Verify FormData contents
    console.log('Uploading image:', {
      fileName: file.name,
      fileSize: file.size,
      fileType: file.type,
      roomIds: roomIds,
      hasFile: file instanceof File
    })

    // Verify FormData has the image
    if (!formData.has('image')) {
      throw new Error('Failed to append image to FormData')
    }

    // Don't set Content-Type header - let browser set it with boundary
    try {
      const response = await api.post('/images', formData)
      return response.data
    } catch (error) {
      console.error('Upload API error:', error.response?.data || error.message)
      throw error
    }
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
  },

  // Admin profile update (including invoice fields)
  async updateAdminProfile(data) {
    const response = await api.put('/auth/admin/profile', data)
    return response.data
  }
}
