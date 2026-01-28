import api from './api'

export const superAdminService = {
  // Dashboard
  async getDashboardStats() {
    const response = await api.get('/super-admin/dashboard/stats')
    return response.data
  },

  // Users
  async getAllUsers() {
    const response = await api.get('/super-admin/users')
    return response.data
  },

  async getUser(id) {
    const response = await api.get(`/super-admin/users/${id}`)
    return response.data
  },

  async createUser(data) {
    const response = await api.post('/super-admin/users', data)
    return response.data
  },

  async updateUser(id, data) {
    const response = await api.put(`/super-admin/users/${id}`, data)
    return response.data
  },

  async deleteUser(id) {
    const response = await api.delete(`/super-admin/users/${id}`)
    return response.data
  },

  // Hotels
  async getAllHotels() {
    const response = await api.get('/super-admin/hotels')
    return response.data
  },

  async getHotel(id) {
    const response = await api.get(`/super-admin/hotels/${id}`)
    return response.data
  },

  async createHotel(data) {
    const response = await api.post('/super-admin/hotels', data)
    return response.data
  },

  async updateHotel(id, data) {
    const response = await api.put(`/super-admin/hotels/${id}`, data)
    return response.data
  },

  async deleteHotel(id) {
    const response = await api.delete(`/super-admin/hotels/${id}`)
    return response.data
  },

  // Rooms
  async getAllRooms() {
    const response = await api.get('/super-admin/rooms')
    return response.data
  },

  async getRoom(id) {
    const response = await api.get(`/super-admin/rooms/${id}`)
    return response.data
  },

  async createRoom(data) {
    const response = await api.post('/super-admin/rooms', data)
    return response.data
  },

  async updateRoom(id, data) {
    const response = await api.put(`/super-admin/rooms/${id}`, data)
    return response.data
  },

  async deleteRoom(id) {
    const response = await api.delete(`/super-admin/rooms/${id}`)
    return response.data
  },

  // Services
  async getAllServices() {
    const response = await api.get('/super-admin/services')
    return response.data
  },

  async getService(id) {
    const response = await api.get(`/super-admin/services/${id}`)
    return response.data
  },

  async createService(data) {
    const response = await api.post('/super-admin/services', data)
    return response.data
  },

  async updateService(id, data) {
    const response = await api.put(`/super-admin/services/${id}`, data)
    return response.data
  },

  async deleteService(id) {
    const response = await api.delete(`/super-admin/services/${id}`)
    return response.data
  },

  // Bookings
  async getAllBookings() {
    const response = await api.get('/super-admin/bookings')
    return response.data
  },

  async getBooking(id) {
    const response = await api.get(`/super-admin/bookings/${id}`)
    return response.data
  },

  async createBooking(data) {
    const response = await api.post('/super-admin/bookings', data)
    return response.data
  },

  async updateBooking(id, data) {
    const response = await api.put(`/super-admin/bookings/${id}`, data)
    return response.data
  },

  async deleteBooking(id) {
    const response = await api.delete(`/super-admin/bookings/${id}`)
    return response.data
  },

  // Invoices
  async getAllInvoices() {
    const response = await api.get('/super-admin/invoices')
    return response.data
  },

  async getInvoice(id) {
    const response = await api.get(`/super-admin/invoices/${id}`)
    return response.data
  },

  async updateInvoice(id, data) {
    const response = await api.put(`/super-admin/invoices/${id}`, data)
    return response.data
  },

  async deleteInvoice(id) {
    const response = await api.delete(`/super-admin/invoices/${id}`)
    return response.data
  },

  // RFID Keys
  async getAllRFIDKeys() {
    const response = await api.get('/super-admin/rfid-keys')
    return response.data
  },

  async getRFIDKey(id) {
    const response = await api.get(`/super-admin/rfid-keys/${id}`)
    return response.data
  },

  async createRFIDKey(data) {
    const response = await api.post('/super-admin/rfid-keys', data)
    return response.data
  },

  async updateRFIDKey(id, data) {
    const response = await api.put(`/super-admin/rfid-keys/${id}`, data)
    return response.data
  },

  async deleteRFIDKey(id) {
    const response = await api.delete(`/super-admin/rfid-keys/${id}`)
    return response.data
  },

  // Devices
  async getAllDevices() {
    const response = await api.get('/super-admin/devices')
    return response.data
  },

  async getDevice(id) {
    const response = await api.get(`/super-admin/devices/${id}`)
    return response.data
  },

  async createDevice(data) {
    const response = await api.post('/super-admin/devices', data)
    return response.data
  },

  async updateDevice(id, data) {
    const response = await api.put(`/super-admin/devices/${id}`, data)
    return response.data
  },

  async regenerateDeviceToken(id) {
    const response = await api.post(`/super-admin/devices/${id}/regenerate-token`)
    return response.data
  },

  async deleteDevice(id) {
    const response = await api.delete(`/super-admin/devices/${id}`)
    return response.data
  }
}
