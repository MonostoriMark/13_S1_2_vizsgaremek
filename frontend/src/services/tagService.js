import api from './api'

export const tagService = {
  async getAllTags() {
    const response = await api.get('/tags')
    return response.data
  },

  async getTagUsage() {
    const response = await api.get('/tags/usage')
    return response.data
  },

  async createTag(name) {
    const response = await api.post('/tags', { name })
    return response.data
  },

  async updateTag(id, name) {
    const response = await api.put(`/tags/${id}`, { name })
    return response.data
  },

  async deleteTag(id) {
    const response = await api.delete(`/tags/${id}`)
    return response.data
  },

  async addTagsToHotel(hotelId, tagIds) {
    const response = await api.post(`/hotels/${hotelId}/tags`, { tag_ids: tagIds })
    return response.data
  },

  async removeTagFromHotel(hotelId, tagId) {
    const response = await api.delete(`/hotels/${hotelId}/tags/${tagId}`)
    return response.data
  },

  async addTagsToRoom(roomId, tagIds) {
    const response = await api.post(`/rooms/${roomId}/tags`, { tag_ids: tagIds })
    return response.data
  },

  async removeTagFromRoom(roomId, tagId) {
    const response = await api.delete(`/rooms/${roomId}/tags/${tagId}`)
    return response.data
  }
}
