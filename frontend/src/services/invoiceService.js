import api from './api'

export const invoiceService = {
  async getInvoiceByBooking(bookingId) {
    try {
      const response = await api.get(`/invoices/booking/${bookingId}`)
      return response.data
    } catch (error) {
      if (error.response?.status === 404) {
        return null
      }
      throw error
    }
  },

  async generatePreview(bookingId) {
    const response = await api.get(`/invoices/booking/${bookingId}/preview`, {
      responseType: 'blob'
    })
    return response.data
  },

  async approveInvoice(invoiceId) {
    const response = await api.post(`/invoices/${invoiceId}/approve`)
    return response.data
  },

  async sendInvoice(invoiceId) {
    const response = await api.post(`/invoices/${invoiceId}/send`)
    return response.data
  },

  async downloadInvoice(invoiceId) {
    const response = await api.get(`/invoices/${invoiceId}/download`, {
      responseType: 'blob'
    })
    return response.data
  }
}
