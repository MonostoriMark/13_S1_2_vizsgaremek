<template>
  <div class="admin-bookings-page">
    <div class="page-header">
      <div class="page-header-top">
        <router-link to="/admin" class="back-to-dashboard-btn">
          <span class="back-icon">←</span>
          <span>Back to Dashboard</span>
        </router-link>
      </div>
      <h1>Hotel Bookings</h1>
      <p class="page-subtitle">Manage bookings for your hotel</p>
    </div>

    <!-- Loading States -->
    <div v-if="loading || hotelLoading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>{{ hotelLoading ? 'Loading hotel information...' : 'Loading bookings...' }}</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="error-message">{{ error }}</div>
    
    <!-- Success Message -->
    <div v-if="successMessage" class="success-message">{{ successMessage }}</div>

    <!-- Hotel Info Card -->
    <div v-if="hotel && !hotelLoading" class="hotel-info-card">
      <div class="hotel-info-content">
        <div class="hotel-icon">🏨</div>
        <div class="hotel-details">
          <h2>{{ hotel.user?.name || 'My Hotel' }}</h2>
          <p class="hotel-location">📍 {{ hotel.location }}</p>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="bookings.length === 0 && !loading && !hotelLoading" class="empty-state">
      <div class="empty-icon">📋</div>
      <h2>No Bookings Found</h2>
      <p>No bookings have been made for your hotel yet.</p>
    </div>

    <!-- Bookings List -->
    <div v-if="bookings.length > 0" class="bookings-container">
      <div class="bookings-stats">
        <div class="stat-card">
          <div class="stat-value">{{ bookings.length }}</div>
          <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ confirmedCount }}</div>
          <div class="stat-label">Confirmed</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ pendingCount }}</div>
          <div class="stat-label">Pending</div>
        </div>
      </div>

      <div class="bookings-grid">
        <div
          v-for="booking in bookings"
          :key="booking.id"
          class="booking-card"
          :class="`status-${booking.status}`"
        >
          <!-- Booking Header -->
          <div class="booking-card-header">
            <div class="booking-id-section">
              <span class="booking-label">Booking</span>
              <h3 class="booking-id">#{{ booking.id }}</h3>
            </div>
            <span :class="['status-badge', `badge-${booking.status}`]">
              {{ formatStatus(booking.status) }}
            </span>
          </div>

          <!-- Guest Information -->
          <div v-if="booking.user" class="guest-section">
            <div class="guest-header">
              <div class="guest-icon">👤</div>
              <div class="guest-info">
                <span class="guest-name">{{ booking.user.name }}</span>
                <span class="guest-email">{{ booking.user.email }}</span>
              </div>
            </div>
          </div>

          <!-- Booking Dates -->
          <div class="booking-dates-section">
            <div class="date-card">
              <div class="date-icon">📅</div>
              <div class="date-info">
                <span class="date-label">Check-in</span>
                <span class="date-value">{{ formatDate(booking.startDate) }}</span>
              </div>
            </div>
            <div class="date-separator">→</div>
            <div class="date-card">
              <div class="date-icon">📅</div>
              <div class="date-info">
                <span class="date-label">Check-out</span>
                <span class="date-value">{{ formatDate(booking.endDate) }}</span>
              </div>
            </div>
          </div>

          <!-- Booking Details -->
          <div class="booking-details">
            <div class="detail-row">
              <span class="detail-label">Total Price</span>
              <span class="detail-value price">{{ booking.totalPrice || 'N/A' }} €</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Nights</span>
              <span class="detail-value">{{ calculateNights(booking.startDate, booking.endDate) }} nights</span>
            </div>
          </div>

          <!-- Rooms Section -->
          <div v-if="booking.rooms && booking.rooms.length > 0" class="booking-rooms-section">
            <h4 class="section-title">Rooms</h4>
            <div class="rooms-list">
              <div
                v-for="room in booking.rooms"
                :key="room.id"
                class="room-item"
              >
                <div class="room-icon">🛏️</div>
                <div class="room-info">
                  <span class="room-name">{{ room.name }}</span>
                  <span class="room-capacity">{{ room.capacity }} guests</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Guests Section -->
          <!-- Guests Section (Admin can manage) -->
          <div class="booking-guests-section">
            <div class="guests-header">
              <h4 class="section-title">Registered Guests</h4>
              <button
                @click="openGuestModal(booking)"
                class="btn-add-guest"
                :disabled="booking.status === 'cancelled' || booking.status === 'finished' || isAtCapacity(booking)"
                :title="isAtCapacity(booking) ? 'Maximum guest capacity reached' : 'Add guest'"
              >
                + Add Guest
              </button>
            </div>
            <div v-if="booking.guests && booking.guests.length > 0" class="guests-list">
              <div
                v-for="guest in booking.guests"
                :key="guest.id"
                class="guest-item-admin"
              >
                <div class="guest-icon-small">👤</div>
                <div class="guest-info-small">
                  <span class="guest-name-small">{{ guest.name }}</span>
                  <span class="guest-id">ID: {{ guest.idNumber }}</span>
                  <span class="guest-dob" v-if="guest.dateOfBirth">
                    DOB: {{ formatDate(guest.dateOfBirth) }}
                  </span>
                </div>
                <div class="guest-actions">
                  <button
                    @click="openEditGuestModal(booking, guest)"
                    class="btn-edit-guest"
                    :disabled="booking.status === 'cancelled' || booking.status === 'finished'"
                    title="Edit guest"
                  >
                    ✏️
                  </button>
                  <button
                    @click="deleteGuest(guest.id, booking.id)"
                    class="btn-delete-guest"
                    :disabled="booking.status === 'cancelled' || booking.status === 'finished'"
                    title="Delete guest"
                  >
                    🗑️
                  </button>
                </div>
              </div>
            </div>
            <div v-else class="no-guests">
              <p>No guests registered yet</p>
            </div>
          </div>

          <!-- Invoice Section (Admin) -->
          <div v-if="booking.status === 'confirmed'" class="invoice-section">
            <h4 class="section-title">Invoice</h4>
            <div v-if="booking.invoice" class="invoice-info">
              <div class="invoice-status">
                <span class="invoice-status-badge" :class="`invoice-${booking.invoice.status}`">
                  {{ formatInvoiceStatus(booking.invoice.status) }}
                </span>
                <span class="invoice-number">{{ booking.invoice.invoice_number }}</span>
              </div>
              <div class="invoice-actions">
                <button
                  v-if="booking.invoice.status === 'draft'"
                  @click="openEditInvoiceModal(booking)"
                  class="btn-invoice-edit"
                  :disabled="invoiceLoading === booking.id"
                >
                  ✏️ Edit
                </button>
                <button
                  v-if="booking.invoice.status === 'draft'"
                  @click="previewInvoice(booking.id)"
                  class="btn-invoice-preview"
                  :disabled="invoiceLoading === booking.id"
                >
                  📄 Preview
                </button>
                <button
                  v-if="booking.invoice.status === 'draft'"
                  @click="approveInvoice(booking.invoice.id, booking.id)"
                  class="btn-invoice-approve"
                  :disabled="invoiceLoading === booking.id"
                >
                  ✓ Approve
                </button>
                <button
                  v-if="booking.invoice.status === 'approved'"
                  @click="sendInvoice(booking.invoice.id, booking.id)"
                  class="btn-invoice-send"
                  :disabled="invoiceLoading === booking.id"
                >
                  📧 Send to Guest
                </button>
                <button
                  v-if="booking.invoice.status === 'sent'"
                  class="btn-invoice-sent"
                  disabled
                >
                  ✅ Sent
                </button>
              </div>
            </div>
            <div v-else class="no-invoice">
              <p>Invoice will be generated after booking confirmation</p>
            </div>
          </div>

          <!-- Booking Actions (Admin) -->
          <div class="booking-actions">
            <div v-if="booking.status === 'pending'" class="pending-actions">
              <button
                @click="updateBookingStatus(booking.id, 'confirmed')"
                class="btn-accept"
                :disabled="updating === booking.id"
              >
                {{ updating === booking.id ? 'Updating...' : '✓ Accept' }}
              </button>
              <button
                @click="updateBookingStatus(booking.id, 'cancelled')"
                class="btn-reject"
                :disabled="updating === booking.id"
              >
                {{ updating === booking.id ? 'Updating...' : '✗ Reject' }}
              </button>
            </div>
            <div v-else class="booking-status-actions">
              <button
                @click="openEditBookingModal(booking)"
                class="btn-edit-booking"
                :disabled="updating === booking.id"
              >
                ✏️ Edit Booking
              </button>
              <div v-if="booking.status === 'confirmed'" class="confirmed-badge">
                ✅ Confirmed - Guest can check in
              </div>
              <div v-else-if="booking.status === 'cancelled'" class="cancelled-badge">
                ❌ Rejected
              </div>
              <div v-else-if="booking.status === 'finished'" class="completed-badge">
                ✓ Completed
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Invoice Modal -->
    <Transition name="modal">
      <div v-if="showEditInvoiceModal" class="modal-overlay" @click.self="closeEditInvoiceModal">
        <div class="modal-content invoice-modal">
          <div class="modal-header">
            <h2>Edit Invoice</h2>
            <button @click="closeEditInvoiceModal" class="btn-close-modal">×</button>
          </div>
          <form @submit.prevent="saveInvoice" class="invoice-form">
            <div class="form-group">
              <label>Subtotal (EUR) *</label>
              <input
                v-model.number="invoiceForm.subtotal"
                type="number"
                step="0.01"
                min="0"
                required
              />
            </div>
            <div class="form-group">
              <label>Tax Rate (%) *</label>
              <input
                v-model.number="invoiceForm.tax_rate"
                type="number"
                step="0.01"
                min="0"
                max="100"
                required
              />
            </div>
            <div class="form-group">
              <label>Issue Date *</label>
              <input
                v-model="invoiceForm.issue_date"
                type="date"
                required
              />
            </div>
            <div class="form-group">
              <label>Due Date *</label>
              <input
                v-model="invoiceForm.due_date"
                type="date"
                :min="invoiceForm.issue_date"
                required
              />
            </div>
            <div class="invoice-summary">
              <div class="summary-row">
                <span>Tax Amount:</span>
                <strong>{{ calculateTaxAmount }} EUR</strong>
              </div>
              <div class="summary-row total-row">
                <span>Total Amount:</span>
                <strong>{{ calculateTotalAmount }} EUR</strong>
              </div>
            </div>
            <div class="modal-actions">
              <button type="button" @click="closeEditInvoiceModal" class="btn-cancel">
                Cancel
              </button>
              <button type="submit" class="btn-save" :disabled="savingInvoice">
                {{ savingInvoice ? 'Saving...' : 'Save Invoice' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Edit Booking Modal -->
    <Transition name="modal">
      <div v-if="showEditBookingModal" class="modal-overlay" @click.self="closeEditBookingModal">
        <div class="modal-content booking-modal">
          <div class="modal-header">
            <h2>Edit Booking</h2>
            <button @click="closeEditBookingModal" class="btn-close-modal">×</button>
          </div>
          <form @submit.prevent="saveBooking" class="booking-form">
            <div class="form-group">
              <label>Start Date *</label>
              <input
                v-model="bookingForm.startDate"
                type="date"
                required
              />
            </div>
            <div class="form-group">
              <label>End Date *</label>
              <input
                v-model="bookingForm.endDate"
                type="date"
                :min="bookingForm.startDate"
                required
              />
            </div>
            <div class="form-group">
              <label>Total Price (EUR) *</label>
              <input
                v-model.number="bookingForm.totalPrice"
                type="number"
                step="0.01"
                min="0"
                required
              />
            </div>
            <div class="modal-actions">
              <button type="button" @click="closeEditBookingModal" class="btn-cancel">
                Cancel
              </button>
              <button type="submit" class="btn-save" :disabled="savingBooking">
                {{ savingBooking ? 'Saving...' : 'Save Booking' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Guest Management Modal -->
    <Transition name="modal">
      <div v-if="showGuestModal" class="modal-overlay" @click.self="closeGuestModal">
        <div class="modal-content guest-modal">
          <div class="modal-header">
            <h2>{{ editingGuest ? 'Edit Guest' : 'Add Guest' }}</h2>
            <button @click="closeGuestModal" class="btn-close-modal">×</button>
          </div>
          <form @submit.prevent="saveGuest" class="guest-form">
            <div class="form-group">
              <label>Full Name *</label>
              <input
                v-model="guestForm.name"
                type="text"
                required
                placeholder="Enter guest's full name"
              />
            </div>
            <div class="form-group">
              <label>ID Number *</label>
              <input
                v-model="guestForm.idNumber"
                type="text"
                required
                placeholder="Enter ID/Passport number"
              />
            </div>
            <div class="form-group">
              <label>Date of Birth *</label>
              <input
                v-model="guestForm.dateOfBirth"
                type="date"
                required
                :max="maxDate"
              />
            </div>
            <div class="modal-actions">
              <button type="button" @click="closeGuestModal" class="btn-cancel">
                Cancel
              </button>
              <button type="submit" class="btn-save" :disabled="savingGuest">
                {{ savingGuest ? 'Saving...' : (editingGuest ? 'Update Guest' : 'Add Guest') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { hotelService } from '../../services/hotelService'
import { bookingService } from '../../services/bookingService'
import { invoiceService } from '../../services/invoiceService'
import { guestService } from '../../services/guestService'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const hotel = ref(null)
const bookings = ref([])
const loading = ref(true)
const hotelLoading = ref(true)
const error = ref('')
const updating = ref(null)
const invoiceLoading = ref(null)
const successMessage = ref('')

// Invoice editing
const showEditInvoiceModal = ref(false)
const editingInvoice = ref(null)
const savingInvoice = ref(false)
const invoiceForm = ref({
  subtotal: 0,
  tax_rate: 0,
  issue_date: '',
  due_date: ''
})

// Booking editing
const showEditBookingModal = ref(false)
const editingBooking = ref(null)
const savingBooking = ref(false)
const bookingForm = ref({
  startDate: '',
  endDate: '',
  totalPrice: 0
})

// Guest management
const showGuestModal = ref(false)
const currentBookingForGuest = ref(null)
const editingGuest = ref(null)
const savingGuest = ref(false)
const guestForm = ref({
  name: '',
  idNumber: '',
  dateOfBirth: ''
})
const maxDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

const confirmedCount = computed(() => {
  return bookings.value.filter(b => b.status === 'confirmed').length
})

const pendingCount = computed(() => {
  return bookings.value.filter(b => b.status === 'pending').length
})

onMounted(async () => {
  await loadHotel()
  if (hotel.value) {
    await loadBookings()
  }
})

const loadHotel = async () => {
  if (!authStore.state.user) {
    error.value = 'Not authenticated'
    hotelLoading.value = false
    return
  }

  try {
    const hotels = await hotelService.getHotels()
    hotel.value = hotels.find(h => h.user_id === authStore.state.user.id)
    
    if (!hotel.value) {
      error.value = 'Hotel not found for this user'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load hotel information'
  } finally {
    hotelLoading.value = false
  }
}

const loadBookings = async () => {
  if (!hotel.value) {
    return
  }

  loading.value = true
  error.value = ''
  try {
    const data = await bookingService.getBookingsByHotelId(hotel.value.id)
    
    if (data && data.bookings && Array.isArray(data.bookings)) {
      // The new endpoint returns bookings with all relationships already loaded
      // Load invoice data for confirmed bookings
      bookings.value = await Promise.all(data.bookings.map(async (booking) => {
        // Rooms are already loaded via the relationship
        const rooms = booking.rooms ? booking.rooms.map(room => ({
          id: room.id,
          name: room.name,
          capacity: room.capacity || 'N/A'
        })) : []
        
        // Load invoice data
        let invoice = null
        if (booking.status === 'confirmed') {
          try {
            const invoiceData = await invoiceService.getInvoiceByBooking(booking.id)
            invoice = invoiceData?.invoice || null
          } catch (err) {
            console.error('Failed to load invoice:', err)
          }
        }
        
        return {
          ...booking,
          rooms: rooms,
          user: booking.user || null,
          guests: booking.guests || [],
          invoice: invoice
        }
      }))
    } else {
      bookings.value = []
    }
  } catch (err) {
    console.error('Failed to load bookings:', err)
    if (err.response?.status === 404) {
      bookings.value = []
    } else {
      error.value = err.response?.data?.message || 'Failed to load bookings'
      bookings.value = []
    }
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatStatus = (status) => {
  const statusMap = {
    'pending': 'Pending',
    'confirmed': 'Confirmed',
    'cancelled': 'Cancelled',
    'finished': 'Completed'
  }
  return statusMap[status] || status
}

const calculateNights = (startDate, endDate) => {
  const start = new Date(startDate)
  const end = new Date(endDate)
  const diffTime = Math.abs(end - start)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
}

const updateBookingStatus = async (bookingId, status) => {
  updating.value = bookingId
  try {
    await bookingService.updateBookingStatus(bookingId, status)
    await loadBookings()
    if (status === 'confirmed') {
      successMessage.value = 'Booking confirmed! Invoice preview is now available.'
      setTimeout(() => { successMessage.value = '' }, 5000)
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update booking status'
  } finally {
    updating.value = null
  }
}

const formatInvoiceStatus = (status) => {
  const statusMap = {
    'draft': 'Draft',
    'approved': 'Approved',
    'sent': 'Sent'
  }
  return statusMap[status] || status
}

const previewInvoice = async (bookingId) => {
  invoiceLoading.value = bookingId
  try {
    const blob = await invoiceService.generatePreview(bookingId)
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.target = '_blank'
    link.click()
    window.URL.revokeObjectURL(url)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to generate invoice preview'
  } finally {
    invoiceLoading.value = null
  }
}

const approveInvoice = async (invoiceId, bookingId) => {
  if (!confirm('Are you sure you want to approve this invoice? It will be finalized and cannot be edited.')) {
    return
  }
  
  invoiceLoading.value = bookingId
  try {
    await invoiceService.approveInvoice(invoiceId)
    successMessage.value = 'Invoice approved successfully!'
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to approve invoice'
  } finally {
    invoiceLoading.value = null
  }
}

const sendInvoice = async (invoiceId, bookingId) => {
  if (!confirm('Send this invoice to the guest via email?')) {
    return
  }
  
  invoiceLoading.value = bookingId
  try {
    await invoiceService.sendInvoice(invoiceId)
    successMessage.value = 'Invoice sent to guest successfully!'
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to send invoice'
  } finally {
    invoiceLoading.value = null
  }
}

// Invoice editing functions
const openEditInvoiceModal = (booking) => {
  if (!booking.invoice || booking.invoice.status !== 'draft') {
    error.value = 'Only draft invoices can be edited'
    return
  }
  editingInvoice.value = booking.invoice
  invoiceForm.value = {
    subtotal: parseFloat(booking.invoice.subtotal) || 0,
    tax_rate: parseFloat(booking.invoice.tax_rate) || 0,
    issue_date: booking.invoice.issue_date || '',
    due_date: booking.invoice.due_date || ''
  }
  showEditInvoiceModal.value = true
}

const closeEditInvoiceModal = () => {
  showEditInvoiceModal.value = false
  editingInvoice.value = null
  invoiceForm.value = {
    subtotal: 0,
    tax_rate: 0,
    issue_date: '',
    due_date: ''
  }
}

const calculateTaxAmount = computed(() => {
  return (invoiceForm.value.subtotal * (invoiceForm.value.tax_rate / 100)).toFixed(2)
})

const calculateTotalAmount = computed(() => {
  return (parseFloat(invoiceForm.value.subtotal) + parseFloat(calculateTaxAmount.value)).toFixed(2)
})

const saveInvoice = async () => {
  if (!editingInvoice.value) return
  
  savingInvoice.value = true
  try {
    await invoiceService.updateInvoice(editingInvoice.value.id, {
      subtotal: invoiceForm.value.subtotal,
      tax_rate: invoiceForm.value.tax_rate,
      issue_date: invoiceForm.value.issue_date,
      due_date: invoiceForm.value.due_date
    })
    successMessage.value = 'Invoice updated successfully!'
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
    closeEditInvoiceModal()
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'Failed to update invoice'
  } finally {
    savingInvoice.value = false
  }
}

// Booking editing functions
const openEditBookingModal = (booking) => {
  editingBooking.value = booking
  bookingForm.value = {
    startDate: booking.startDate || '',
    endDate: booking.endDate || '',
    totalPrice: parseFloat(booking.totalPrice) || 0
  }
  showEditBookingModal.value = true
}

const closeEditBookingModal = () => {
  showEditBookingModal.value = false
  editingBooking.value = null
  bookingForm.value = {
    startDate: '',
    endDate: '',
    totalPrice: 0
  }
}

const saveBooking = async () => {
  if (!editingBooking.value) return
  
  savingBooking.value = true
  try {
    await bookingService.updateBooking(editingBooking.value.id, {
      startDate: bookingForm.value.startDate,
      endDate: bookingForm.value.endDate,
      totalPrice: bookingForm.value.totalPrice
    })
    successMessage.value = 'Booking updated successfully!'
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
    closeEditBookingModal()
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'Failed to update booking'
  } finally {
    savingBooking.value = false
  }
}

// Guest management functions
const getMaxCapacity = (booking) => {
  if (!booking.rooms || booking.rooms.length === 0) {
    return 0
  }
  return booking.rooms.reduce((total, room) => total + (room.capacity || 0), 0)
}

const getCurrentGuestCount = (booking) => {
  return booking.guests ? booking.guests.length : 0
}

const isAtCapacity = (booking) => {
  return getCurrentGuestCount(booking) >= getMaxCapacity(booking)
}

const openGuestModal = (booking) => {
  currentBookingForGuest.value = booking
  editingGuest.value = null
  guestForm.value = {
    name: '',
    idNumber: '',
    dateOfBirth: ''
  }
  showGuestModal.value = true
}

const openEditGuestModal = (booking, guest) => {
  currentBookingForGuest.value = booking
  editingGuest.value = guest
  guestForm.value = {
    name: guest.name || '',
    idNumber: guest.idNumber || '',
    dateOfBirth: guest.dateOfBirth || ''
  }
  showGuestModal.value = true
}

const closeGuestModal = () => {
  showGuestModal.value = false
  currentBookingForGuest.value = null
  editingGuest.value = null
  guestForm.value = {
    name: '',
    idNumber: '',
    dateOfBirth: ''
  }
}

const saveGuest = async () => {
  if (!currentBookingForGuest.value) return

  const booking = currentBookingForGuest.value

  // Check capacity before adding (not when editing)
  if (!editingGuest.value) {
    const currentCount = getCurrentGuestCount(booking)
    const maxCapacity = getMaxCapacity(booking)
    
    if (currentCount >= maxCapacity) {
      error.value = `Maximum guest capacity reached. This booking can accommodate ${maxCapacity} guest${maxCapacity !== 1 ? 's' : ''}.`
      closeGuestModal()
      return
    }
  }

  savingGuest.value = true
  try {
    if (editingGuest.value) {
      // Update existing guest
      await guestService.updateGuest(editingGuest.value.id, {
        name: guestForm.value.name,
        idNumber: guestForm.value.idNumber,
        dateOfBirth: guestForm.value.dateOfBirth
      })
      successMessage.value = 'Guest updated successfully!'
    } else {
      // Add new guest
      await guestService.addGuests(booking.id, [{
        name: guestForm.value.name,
        idNumber: guestForm.value.idNumber,
        dateOfBirth: guestForm.value.dateOfBirth
      }])
      successMessage.value = 'Guest added successfully!'
    }
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
    closeGuestModal()
    error.value = ''
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'Failed to save guest information'
  } finally {
    savingGuest.value = false
  }
}

const deleteGuest = async (guestId, bookingId) => {
  if (!confirm('Are you sure you want to delete this guest?')) {
    return
  }

  try {
    await guestService.deleteGuest(guestId)
    successMessage.value = 'Guest deleted successfully!'
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'Failed to delete guest'
  }
}
</script>

<style scoped>
.admin-bookings-page {
  min-height: 100vh;
  background: #f8f9fa;
  padding: 2rem;
}

.page-header {
  max-width: 1400px;
  margin: 0 auto 2rem;
}

.page-header-top {
  margin-bottom: 1rem;
}

.back-to-dashboard-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-decoration: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
  border: none;
  cursor: pointer;
}

.back-to-dashboard-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}

.back-icon {
  font-size: 1.2rem;
  font-weight: 700;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.page-subtitle {
  color: #7f8c8d;
  font-size: 1.1rem;
}

/* Loading State */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  color: #7f8c8d;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #f0f0f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Error Message */
.error-message {
  background: #fee;
  color: #c33;
  padding: 1rem;
  border-radius: 8px;
  margin: 1rem 0;
  border: 1px solid #fcc;
  max-width: 1400px;
  margin-left: auto;
  margin-right: auto;
}

/* Success Message */
.success-message {
  background: #d4edda;
  color: #155724;
  padding: 1rem;
  border-radius: 8px;
  margin: 1rem 0;
  border: 1px solid #c3e6cb;
  max-width: 1400px;
  margin-left: auto;
  margin-right: auto;
}

/* Invoice Section */
.invoice-section {
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.invoice-info {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.invoice-status {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.invoice-status-badge {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
}

.invoice-draft {
  background: #fff3cd;
  color: #856404;
}

.invoice-approved {
  background: #d1ecf1;
  color: #0c5460;
}

.invoice-sent {
  background: #d4edda;
  color: #155724;
}

.invoice-number {
  font-size: 0.9rem;
  color: #667eea;
  font-weight: 600;
}

.invoice-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.btn-invoice-edit,
.btn-invoice-preview,
.btn-invoice-approve,
.btn-invoice-send,
.btn-invoice-sent {
  padding: 0.625rem 1.25rem;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-invoice-preview {
  background: #667eea;
  color: white;
}

.btn-invoice-preview:hover:not(:disabled) {
  background: #5568d3;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-invoice-approve {
  background: #27ae60;
  color: white;
}

.btn-invoice-approve:hover:not(:disabled) {
  background: #229954;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.btn-invoice-send {
  background: #3498db;
  color: white;
}

.btn-invoice-send:hover:not(:disabled) {
  background: #2980b9;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.btn-invoice-sent {
  background: #95a5a6;
  color: white;
  cursor: not-allowed;
}

.btn-invoice-edit {
  background: #f39c12;
  color: white;
}

.btn-invoice-edit:hover:not(:disabled) {
  background: #e67e22;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
}

.btn-invoice-edit:disabled,
.btn-invoice-preview:disabled,
.btn-invoice-approve:disabled,
.btn-invoice-send:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-edit-booking {
  padding: 0.625rem 1.25rem;
  background: #f39c12;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-right: 1rem;
}

.btn-edit-booking:hover:not(:disabled) {
  background: #e67e22;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
}

.btn-edit-booking:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.booking-status-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(5px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
}

.modal-content {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e0e0e0;
}

.modal-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
}

.btn-close-modal {
  background: none;
  border: none;
  font-size: 2rem;
  color: #999;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.btn-close-modal:hover {
  background: #f0f0f0;
  color: #333;
}

.invoice-form,
.booking-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.95rem;
}

.form-group input {
  padding: 0.75rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.2s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.invoice-summary {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  margin: 1rem 0;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  font-size: 0.95rem;
}

.summary-row.total-row {
  border-top: 2px solid #e0e0e0;
  padding-top: 1rem;
  margin-top: 0.5rem;
  font-size: 1.1rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 2px solid #e0e0e0;
}

.btn-cancel {
  padding: 0.75rem 1.5rem;
  background: #e0e0e0;
  color: #333;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel:hover {
  background: #d0d0d0;
}

.btn-save {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-save:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active .modal-content,
.modal-leave-active .modal-content {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
  transform: scale(0.95);
  opacity: 0;
}

/* Guest Management Styles */
.guests-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.btn-add-guest {
  padding: 0.5rem 1rem;
  background: #27ae60;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-add-guest:hover:not(:disabled) {
  background: #229954;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.btn-add-guest:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.guest-item-admin {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem;
  background: white;
  border-radius: 8px;
  margin-bottom: 0.5rem;
  border: 1px solid #e0e0e0;
}

.guest-info-small {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.guest-dob {
  font-size: 0.8rem;
  color: #7f8c8d;
}

.guest-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-edit-guest,
.btn-delete-guest {
  padding: 0.5rem;
  background: none;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-edit-guest:hover:not(:disabled) {
  background: #f0f7ff;
  border-color: #667eea;
  color: #667eea;
}

.btn-delete-guest:hover:not(:disabled) {
  background: #fee2e2;
  border-color: #ef4444;
  color: #ef4444;
}

.btn-edit-guest:disabled,
.btn-delete-guest:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.no-guests {
  padding: 1rem;
  text-align: center;
  color: #7f8c8d;
  font-size: 0.9rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.no-invoice {
  padding: 1rem;
  text-align: center;
  color: #7f8c8d;
  font-size: 0.9rem;
}

/* Hotel Info Card */
.hotel-info-card {
  max-width: 1400px;
  margin: 0 auto 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.hotel-info-content {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.hotel-icon {
  font-size: 3rem;
}

.hotel-details h2 {
  color: white;
  font-size: 1.75rem;
  margin-bottom: 0.5rem;
}

.hotel-location {
  color: rgba(255, 255, 255, 0.9);
  font-size: 1.1rem;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  max-width: 600px;
  margin: 0 auto;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h2 {
  font-size: 1.75rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #7f8c8d;
  margin-bottom: 2rem;
  font-size: 1.1rem;
}

/* Bookings Container */
.bookings-container {
  max-width: 1400px;
  margin: 0 auto;
}

/* Stats Cards */
.bookings-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.stat-value {
  font-size: 2.5rem;
  font-weight: 700;
  color: #667eea;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 0.9rem;
  color: #7f8c8d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Bookings Grid */
.bookings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 2rem;
}

/* Booking Card */
.booking-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border-top: 4px solid transparent;
}

.booking-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.booking-card.status-pending {
  border-top-color: #f39c12;
}

.booking-card.status-confirmed {
  border-top-color: #27ae60;
}

.booking-card.status-cancelled {
  border-top-color: #e74c3c;
}

.booking-card.status-finished {
  border-top-color: #3498db;
}

/* Booking Header */
.booking-card-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.booking-id-section {
  display: flex;
  flex-direction: column;
}

.booking-label {
  font-size: 0.85rem;
  color: #7f8c8d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.25rem;
}

.booking-id {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: capitalize;
}

.badge-pending {
  background: #fff3cd;
  color: #856404;
}

.badge-confirmed {
  background: #d4edda;
  color: #155724;
}

.badge-cancelled {
  background: #f8d7da;
  color: #721c24;
}

.badge-finished {
  background: #d1ecf1;
  color: #0c5460;
}

/* Guest Section */
.guest-section {
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.guest-header {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.guest-icon {
  font-size: 2rem;
}

.guest-info {
  display: flex;
  flex-direction: column;
}

.guest-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.25rem;
}

.guest-email {
  font-size: 0.9rem;
  color: #7f8c8d;
}

/* Dates Section */
.booking-dates-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
  border-radius: 12px;
}

.date-card {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.date-icon {
  font-size: 1.5rem;
}

.date-info {
  display: flex;
  flex-direction: column;
}

.date-label {
  font-size: 0.85rem;
  color: #7f8c8d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.25rem;
}

.date-value {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
}

.date-separator {
  font-size: 1.5rem;
  color: #667eea;
  font-weight: 600;
}

/* Booking Details */
.booking-details {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.detail-row {
  display: flex;
  flex-direction: column;
}

.detail-label {
  font-size: 0.85rem;
  color: #7f8c8d;
  margin-bottom: 0.25rem;
}

.detail-value {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
}

.detail-value.price {
  color: #27ae60;
  font-size: 1.25rem;
}

/* Rooms Section */
.booking-rooms-section,
.booking-guests-section {
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 1rem;
}

.rooms-list,
.guests-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.room-item,
.guest-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.room-icon,
.guest-icon-small {
  font-size: 1.5rem;
}

.room-info,
.guest-info-small {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.room-name,
.guest-name-small {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.25rem;
}

.room-capacity {
  font-size: 0.9rem;
  color: #7f8c8d;
}

.guest-id {
  font-size: 0.9rem;
  color: #7f8c8d;
}

/* Booking Actions */
.booking-actions {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 2px solid #f0f0f0;
}

.pending-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.btn-accept {
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-accept:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.btn-reject {
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-reject:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

.btn-accept:disabled,
.btn-reject:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.confirmed-badge,
.cancelled-badge,
.completed-badge {
  text-align: center;
  padding: 0.875rem;
  border-radius: 12px;
  font-weight: 600;
}

.confirmed-badge {
  background: #d4edda;
  color: #155724;
}

.cancelled-badge {
  background: #f8d7da;
  color: #721c24;
}

.completed-badge {
  background: #d1ecf1;
  color: #0c5460;
}

/* Responsive Design */
@media (max-width: 768px) {
  .admin-bookings-page {
    padding: 1rem;
  }

  .page-header h1 {
    font-size: 2rem;
  }

  .bookings-grid {
    grid-template-columns: 1fr;
  }

  .booking-dates-section {
    flex-direction: column;
  }

  .date-separator {
    transform: rotate(90deg);
  }

  .booking-details {
    grid-template-columns: 1fr;
  }

  .pending-actions {
    grid-template-columns: 1fr;
  }
}
</style>
