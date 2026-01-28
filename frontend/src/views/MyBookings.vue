<template>
  <div class="bookings-page">
    <div class="page-header">
      <h1>Foglalásaim</h1>
      <p class="page-subtitle">Szállodai foglalásaim kezelése</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Foglalásaim betöltése...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="error-message">{{ error }}</div>

    <!-- Empty State -->
    <div v-if="bookings.length === 0 && !loading" class="empty-state">
      <div class="empty-icon">📅</div>
      <h2>Még nincs foglalás</h2>
      <p>Fedezzen fel fantasztikus szállodákat és hozza létre az első foglalását!</p>
      <router-link to="/search" class="btn-primary">Szállodák keresése</router-link>
    </div>

    <!-- Bookings List -->
    <div v-if="bookings.length > 0" class="bookings-container">
      <!-- Filter Buttons -->
      <div class="bookings-filters">
        <button
          @click="activeFilter = 'all'"
          :class="['filter-btn', { active: activeFilter === 'all' }]"
        >
          <span class="filter-icon">📋</span>
          <span>Összes</span>
          <span class="filter-count">({{ bookings.length }})</span>
        </button>
        <button
          @click="activeFilter = 'pending'"
          :class="['filter-btn', { active: activeFilter === 'pending' }]"
        >
          <span class="filter-icon">⏳</span>
          <span>Függőben</span>
          <span class="filter-count">({{ pendingCount }})</span>
        </button>
        <button
          @click="activeFilter = 'confirmed'"
          :class="['filter-btn', { active: activeFilter === 'confirmed' }]"
        >
          <span class="filter-icon">✅</span>
          <span>Megerősítve</span>
          <span class="filter-count">({{ confirmedCount }})</span>
        </button>
        <button
          @click="activeFilter = 'cancelled'"
          :class="['filter-btn', { active: activeFilter === 'cancelled' }]"
        >
          <span class="filter-icon">❌</span>
          <span>Törölve</span>
          <span class="filter-count">({{ cancelledCount }})</span>
        </button>
        <button
          @click="activeFilter = 'finished'"
          :class="['filter-btn', { active: activeFilter === 'finished' }]"
        >
          <span class="filter-icon">✓</span>
          <span>Befejezve</span>
          <span class="filter-count">({{ finishedCount }})</span>
        </button>
      </div>

      <div class="bookings-stats">
        <div class="stat-card">
          <div class="stat-value">{{ filteredBookings.length }}</div>
          <div class="stat-label">Kiválasztott foglalások</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ confirmedCount }}</div>
          <div class="stat-label">Megerősítve</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ pendingCount }}</div>
          <div class="stat-label">Függőben</div>
        </div>
      </div>

      <!-- View Switcher -->
      <div class="view-switcher">
        <div class="view-switcher-label">
          <span>Nézet:</span>
        </div>
        <div class="view-switcher-buttons">
          <button
            @click="viewMode = 'card'"
            :class="['view-btn', { active: viewMode === 'card' }]"
            title="Kártya nézet"
          >
            <span class="view-icon">📋</span>
            <span>Kártya</span>
          </button>
          <button
            @click="viewMode = 'table'"
            :class="['view-btn', { active: viewMode === 'table' }]"
            title="Táblázat nézet"
          >
            <span class="view-icon">📊</span>
            <span>Táblázat</span>
          </button>
        </div>
      </div>

      <!-- Empty State for Filtered Results -->
      <div v-if="filteredBookings.length === 0 && !loading" class="empty-state">
        <div class="empty-icon">🔍</div>
        <h2>Nincs foglalás ezzel a szűrővel</h2>
        <p>Nincs találat a kiválasztott szűrőre.</p>
      </div>

      <!-- Table View -->
      <div v-if="filteredBookings.length > 0 && viewMode === 'table'" class="bookings-table-container">
        <table class="bookings-table minimal-table">
          <thead>
            <tr>
              <th>Foglalás ID</th>
              <th>Szálloda</th>
              <th>Bejelentkezés</th>
              <th>Kijelentkezés</th>
              <th>Éjszakák</th>
              <th>Összeg</th>
              <th>Státusz</th>
              <th>Műveletek</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="booking in filteredBookings"
              :key="booking.id"
              :class="booking.checkInstatus === 'checkedOut' ? 'table-row-finished' : `table-row-${booking.status}`"
            >
              <td class="booking-id-cell">#{{ booking.id }}</td>
              <td class="hotel-cell">
                <div v-if="booking.hotel" class="hotel-info-inline">
                  <div class="hotel-name-inline">{{ booking.hotel.name }}</div>
                  <div v-if="booking.hotel.address" class="hotel-address-inline">{{ booking.hotel.address }}</div>
                </div>
                <span v-else>-</span>
              </td>
              <td>{{ formatDate(booking.startDate) }}</td>
              <td>{{ formatDate(booking.endDate) }}</td>
              <td>{{ calculateNights(booking.startDate, booking.endDate) }} éjszaka</td>
              <td class="price-cell">{{ booking.totalPrice }} €</td>
              <td>
                <span :class="['status-badge', booking.checkInstatus === 'checkedOut' ? 'badge-finished' : `badge-${booking.status}`]">
                  {{ booking.checkInstatus === 'checkedOut' ? 'Befejezve' : formatStatus(booking.status) }}
                </span>
              </td>
              <td class="actions-cell">
                <button
                  @click="openBookingActionsModal(booking)"
                  class="btn-actions"
                  title="Műveletek"
                >
                  ⚙️ Műveletek
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Card View -->
      <div v-if="filteredBookings.length > 0 && viewMode === 'card'" class="bookings-grid">
        <div
          v-for="booking in filteredBookings"
          :key="booking.id"
          class="booking-card"
          :class="booking.checkInstatus === 'checkedOut' ? 'status-finished' : `status-${booking.status}`"
        >
          <!-- Booking Header -->
          <div class="booking-card-header">
            <div class="booking-id-section">
              <span class="booking-label">Foglalás</span>
              <h3 class="booking-id">#{{ booking.id }}</h3>
            </div>
            <span :class="['status-badge', booking.checkInstatus === 'checkedOut' ? 'badge-finished' : `badge-${booking.status}`]">
              {{ booking.checkInstatus === 'checkedOut' ? 'Befejezve' : formatStatus(booking.status) }}
            </span>
          </div>

          <!-- Booking Dates -->
          <div class="booking-dates-section">
            <div class="date-card">
              <div class="date-icon">📅</div>
              <div class="date-info">
                <span class="date-label">Bejelentkezés</span>
                <span class="date-value">{{ formatDate(booking.startDate) }}</span>
              </div>
            </div>
            <div class="date-separator">↓</div>
            <div class="date-card">
              <div class="date-icon">📅</div>
              <div class="date-info">
                <span class="date-label">Kijelentkezés</span>
                <span class="date-value">{{ formatDate(booking.endDate) }}</span>
              </div>
            </div>
          </div>

          <!-- Booking Details -->
          <div class="booking-details">
            <div class="detail-row">
              <span class="detail-label">Összesen</span>
              <span class="detail-value price">{{ booking.totalPrice }} €</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Éjszakák</span>
              <span class="detail-value">{{ calculateNights(booking.startDate, booking.endDate) }} éjszaka</span>
            </div>
          </div>

          <!-- Rooms Section -->
          <div v-if="booking.rooms && booking.rooms.length > 0" class="booking-rooms-section">
            <h4 class="section-title">Szobák</h4>
            <div class="rooms-list">
              <div
                v-for="room in booking.rooms"
                :key="room.id"
                class="room-item"
              >
                <div class="room-icon">🛏️</div>
                <div class="room-info">
                  <span class="room-name">{{ room.name }}</span>
                  <span class="room-capacity">{{ room.capacity }} vendég</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Services Section -->
          <div v-if="booking.services && booking.services.length > 0" class="booking-services-section">
            <h4 class="section-title">További szolgáltatások</h4>
            <div class="services-list">
              <div
                v-for="service in booking.services"
                :key="service.id"
                class="service-item"
              >
                <span class="service-name">{{ service.name }}</span>
                <span class="service-price">{{ service.price }} €</span>
              </div>
            </div>
          </div>

          <!-- Guests Section -->
          <div class="booking-guests-section">
            <div class="guests-header">
              <div class="guests-title-section">
                <h4 class="section-title">Vendég információk</h4>
                <span class="guests-capacity-info">
                  {{ getCurrentGuestCount(booking) }}/{{ getMaxCapacity(booking) }} vendég
                </span>
              </div>
              <button
                @click="openGuestModal(booking.id)"
                class="btn-add-guest"
                :disabled="booking.status === 'cancelled' || isAtCapacity(booking)"
                :title="isAtCapacity(booking) ? 'Elérte a maximális vendégkapacitást' : 'Vendég hozzáadása'"
              >
                + Vendég hozzáadása
              </button>
            </div>
            <div v-if="booking.guests && booking.guests.length > 0" class="guests-list">
              <div
                v-for="guest in booking.guests"
                :key="guest.id"
                class="guest-item"
              >
                <div class="guest-icon">👤</div>
                <div class="guest-info">
                  <span class="guest-name">{{ guest.name }}</span>
                  <div class="guest-details">
                    <span class="guest-id">ID: {{ guest.idNumber }}</span>
                    <span class="guest-dob">Szül. dátum: {{ formatDate(guest.dateOfBirth) }}</span>
                  </div>
                </div>
                <div class="guest-actions">
                  <button
                    @click="editGuest(guest, booking.id)"
                    class="btn-edit-guest"
                    :disabled="booking.status === 'cancelled'"
                    title="Vendég szerkesztése"
                  >
                    ✏️
                  </button>
                  <button
                    @click="deleteGuest(guest.id, booking.id)"
                    class="btn-delete-guest"
                    :disabled="booking.status === 'cancelled'"
                    title="Vendég törlése"
                  >
                    🗑️
                  </button>
                </div>
              </div>
            </div>
            <div v-else class="no-guests">
              <p>Még nincs hozzáadott vendég. Kattintson a "Vendég hozzáadása" gombra a vendég információk regisztrálásához.</p>
            </div>
            <div v-if="isAtCapacity(booking)" class="capacity-warning">
              ⚠️ Elérte a maximális vendégkapacitást ({{ getMaxCapacity(booking) }} vendég)
            </div>
          </div>

          <!-- Invoice Section (Guest) -->
          <div v-if="booking.status === 'confirmed' && booking.checkInstatus !== 'checkedOut' && booking.invoice && booking.invoice.status !== 'draft'" class="invoice-section">
            <h4 class="section-title">Számla</h4>
            <div class="invoice-info">
              <div class="invoice-status">
                <span class="invoice-number">{{ booking.invoice.invoice_number }}</span>
                <span class="invoice-amount">{{ booking.invoice.total_amount }} Ft</span>
              </div>
              <button
                @click="downloadInvoice(booking.invoice.id, booking.id)"
                class="btn-download-invoice"
                :disabled="invoiceLoading === booking.id"
              >
                {{ invoiceLoading === booking.id ? 'Letöltés...' : '📥 Számla letöltése' }}
              </button>
            </div>
          </div>

          <!-- Booking Actions -->
          <div class="booking-actions">
            <button
              v-if="booking.status === 'pending'"
              @click="cancelBooking(booking.id)"
              class="btn-cancel"
              :disabled="deleting === booking.id"
            >
              {{ deleting === booking.id ? 'Törlés...' : 'Foglalás törlése' }}
            </button>
            <div v-else-if="booking.status === 'confirmed' && booking.checkInstatus !== 'checkedOut'" class="confirmed-badge">
              ✅ Megerősítve - Készen áll a bejelentkezésre
            </div>
            <div v-else-if="booking.checkInstatus === 'checkedOut'" class="completed-badge">
              ✓ Befejezve
            </div>
            <div v-else-if="booking.status === 'cancelled'" class="cancelled-badge">
              ❌ Törölve
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Guest Modal -->
  <div v-if="showGuestModal" class="modal-overlay guest-modal-overlay" @click.self="closeGuestModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>{{ editingGuest ? 'Vendég szerkesztése' : 'Vendég hozzáadása' }}</h2>
        <button @click="closeGuestModal" class="btn-close-modal">×</button>
      </div>
      <form @submit.prevent="saveGuest" class="guest-form">
        <div class="form-group">
          <label for="guestName">Teljes név *</label>
          <input
            id="guestName"
            v-model="guestForm.name"
            type="text"
            required
            placeholder="Adja meg a vendég teljes nevét"
          />
        </div>
        <div class="form-group">
          <label for="guestIdNumber">Személyigazolvány szám *</label>
          <input
            id="guestIdNumber"
            v-model="guestForm.idNumber"
            type="text"
            required
            placeholder="Adja meg a személyigazolvány/útlevél számát"
          />
        </div>
        <div class="form-group">
          <label for="guestDateOfBirth">Születési dátum *</label>
          <input
            id="guestDateOfBirth"
            v-model="guestForm.dateOfBirth"
            type="date"
            required
            :max="maxDate"
          />
        </div>
        <div class="modal-actions">
          <button type="button" @click="closeGuestModal" class="btn-cancel-modal">
            Mégse
          </button>
          <button type="submit" class="btn-save-guest" :disabled="savingGuest">
            {{ savingGuest ? 'Mentés...' : (editingGuest ? 'Vendég frissítése' : 'Vendég hozzáadása') }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Booking Actions Modal -->
  <div v-if="showBookingActionsModal && selectedBookingForActions" class="modal-overlay" @click.self="closeBookingActionsModal">
    <div class="modal-content booking-actions-modal">
      <div class="modal-header">
        <h2>Foglalás műveletek - #{{ selectedBookingForActions.id }}</h2>
        <button @click="closeBookingActionsModal" class="btn-close-modal">×</button>
      </div>
      
      <div class="booking-actions-content">
        <!-- Booking Summary -->
        <div class="booking-summary-section">
          <h3 class="section-title">Foglalás összefoglaló</h3>
          <div class="summary-grid">
            <div class="summary-item">
              <span class="summary-label">Szálloda:</span>
              <span class="summary-value">{{ selectedBookingForActions.hotel?.name || 'Nincs adat' }}</span>
            </div>
            <div class="summary-item">
              <span class="summary-label">Bejelentkezés:</span>
              <span class="summary-value">{{ formatDate(selectedBookingForActions.startDate) }}</span>
            </div>
            <div class="summary-item">
              <span class="summary-label">Kijelentkezés:</span>
              <span class="summary-value">{{ formatDate(selectedBookingForActions.endDate) }}</span>
            </div>
            <div class="summary-item">
              <span class="summary-label">Éjszakák:</span>
              <span class="summary-value">{{ calculateNights(selectedBookingForActions.startDate, selectedBookingForActions.endDate) }} éjszaka</span>
            </div>
            <div class="summary-item">
              <span class="summary-label">Összeg:</span>
              <span class="summary-value price">{{ selectedBookingForActions.totalPrice }} €</span>
            </div>
            <div class="summary-item">
              <span class="summary-label">Státusz:</span>
              <span :class="['status-badge', selectedBookingForActions.checkInstatus === 'checkedOut' ? 'badge-finished' : `badge-${selectedBookingForActions.status}`]">
                {{ selectedBookingForActions.checkInstatus === 'checkedOut' ? 'Befejezve' : formatStatus(selectedBookingForActions.status) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="actions-section">
          <!-- Cancel Booking (for pending bookings) -->
          <div v-if="selectedBookingForActions.status === 'pending'" class="action-group">
            <h3 class="action-group-title">Foglalás kezelése</h3>
            <button
              @click="handleCancelBooking"
              class="action-btn btn-danger"
              :disabled="deleting === selectedBookingForActions.id"
            >
              {{ deleting === selectedBookingForActions.id ? 'Törlés...' : '❌ Foglalás törlése' }}
            </button>
          </div>

          <!-- Guest Management -->
          <div v-if="selectedBookingForActions.status !== 'cancelled' && selectedBookingForActions.checkInstatus !== 'checkedOut'" class="action-group">
            <h3 class="action-group-title">Vendégkezelés</h3>
            <button
              @click="handleOpenGuestModal"
              class="action-btn btn-primary"
              :disabled="isAtCapacity(selectedBookingForActions)"
              :title="isAtCapacity(selectedBookingForActions) ? 'Elérte a maximális vendégkapacitást' : 'Vendég hozzáadása'"
            >
              + Vendég hozzáadása
            </button>
            <div v-if="selectedBookingForActions.guests && selectedBookingForActions.guests.length > 0" class="guests-list-modal">
              <div
                v-for="guest in selectedBookingForActions.guests"
                :key="guest.id"
                class="guest-item-modal"
              >
                <div class="guest-info-modal">
                  <span class="guest-name-modal">{{ guest.name }}</span>
                  <span class="guest-id-modal">ID: {{ guest.idNumber }}</span>
                </div>
                <div class="guest-actions-modal">
                  <button
                    @click="handleEditGuest(guest)"
                    class="btn-edit-guest-modal"
                    :disabled="selectedBookingForActions.status === 'cancelled'"
                    title="Vendég szerkesztése"
                  >
                    ✏️
                  </button>
                  <button
                    @click="handleDeleteGuest(guest.id)"
                    class="btn-delete-guest-modal"
                    :disabled="selectedBookingForActions.status === 'cancelled'"
                    title="Vendég törlése"
                  >
                    🗑️
                  </button>
                </div>
              </div>
            </div>
            <p v-else class="no-guests-modal">Nincs hozzáadott vendég</p>
          </div>

          <!-- Invoice Actions -->
          <div v-if="selectedBookingForActions.status === 'confirmed' && selectedBookingForActions.checkInstatus !== 'checkedOut' && selectedBookingForActions.invoice && selectedBookingForActions.invoice.status !== 'draft'" class="action-group">
            <h3 class="action-group-title">Számla műveletek</h3>
            <div class="invoice-info-modal">
              <div class="invoice-details-modal">
                <span class="invoice-number-modal">{{ selectedBookingForActions.invoice.invoice_number }}</span>
                <span class="invoice-amount-modal">{{ selectedBookingForActions.invoice.total_amount }} Ft</span>
              </div>
              <button
                @click="handleDownloadInvoice"
                class="action-btn btn-primary"
                :disabled="invoiceLoading === selectedBookingForActions.id"
              >
                {{ invoiceLoading === selectedBookingForActions.id ? 'Letöltés...' : '📥 Számla letöltése' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { bookingService } from '../services/bookingService'
import { guestService } from '../services/guestService'
import { invoiceService } from '../services/invoiceService'
import { useAuthStore } from '../stores/auth'
import { useBodyScrollLock } from '../composables/useBodyScrollLock'

const authStore = useAuthStore()
const bookings = ref([])
const loading = ref(true)
const error = ref('')
const deleting = ref(null)
const invoiceLoading = ref(null)
const showGuestModal = ref(false)
const currentBookingId = ref(null)
const editingGuest = ref(null)
const savingGuest = ref(false)
const activeFilter = ref('all')
const viewMode = ref('card') // 'card' or 'table'
const showBookingActionsModal = ref(false)
const selectedBookingForActions = ref(null)
const guestForm = ref({
  name: '',
  idNumber: '',
  dateOfBirth: ''
})

const confirmedCount = computed(() => {
  return bookings.value.filter(b => b.status === 'confirmed' && b.checkInstatus !== 'checkedOut').length
})

const pendingCount = computed(() => {
  return bookings.value.filter(b => b.status === 'pending').length
})

const cancelledCount = computed(() => {
  return bookings.value.filter(b => b.status === 'cancelled').length
})

const finishedCount = computed(() => {
  return bookings.value.filter(b => b.checkInstatus === 'checkedOut').length
})

const filteredBookings = computed(() => {
  if (activeFilter.value === 'all') {
    return bookings.value
  } else if (activeFilter.value === 'pending') {
    return bookings.value.filter(b => b.status === 'pending')
  } else if (activeFilter.value === 'confirmed') {
    return bookings.value.filter(b => b.status === 'confirmed' && b.checkInstatus !== 'checkedOut')
  } else if (activeFilter.value === 'cancelled') {
    return bookings.value.filter(b => b.status === 'cancelled')
  } else if (activeFilter.value === 'finished') {
    return bookings.value.filter(b => b.checkInstatus === 'checkedOut')
  }
  return bookings.value
})

// Lock body scroll when any modal is open
useBodyScrollLock([showGuestModal, showBookingActionsModal])

onMounted(async () => {
  await loadBookings()
})

const loadBookings = async () => {
  if (!authStore.state.user) {
    error.value = 'Nincs bejelentkezve'
    loading.value = false
    return
  }

  try {
    const data = await bookingService.getBookingsByUserId(authStore.state.user.id)
    const bookingsList = data.bookings || []
    
    // Load invoice data for confirmed bookings
    bookings.value = await Promise.all(bookingsList.map(async (booking) => {
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
        guests: booking.guests || [],
        invoice: invoice
      }
    }))
  } catch (err) {
    error.value = err.response?.data?.message || 'A foglalások betöltése sikertelen'
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
    'pending': 'Függőben',
    'confirmed': 'Megerősítve',
    'cancelled': 'Törölve',
    'finished': 'Befejezve'
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

const cancelBooking = async (bookingId) => {
  if (!confirm('Biztosan törölni szeretné ezt a foglalást?')) {
    return
  }

  deleting.value = bookingId
  try {
    await bookingService.deleteBooking(bookingId)
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || 'A foglalás törlése sikertelen'
  } finally {
    deleting.value = null
  }
}

const maxDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

const openGuestModal = (bookingId) => {
  currentBookingId.value = bookingId
  editingGuest.value = null
  guestForm.value = {
    name: '',
    idNumber: '',
    dateOfBirth: ''
  }
  showGuestModal.value = true
}

const editGuest = (guest, bookingId) => {
  currentBookingId.value = bookingId
  editingGuest.value = guest
  guestForm.value = {
    name: guest.name,
    idNumber: guest.idNumber,
    dateOfBirth: guest.dateOfBirth
  }
  showGuestModal.value = true
}

const closeGuestModal = () => {
  showGuestModal.value = false
  editingGuest.value = null
  currentBookingId.value = null
  guestForm.value = {
    name: '',
    idNumber: '',
    dateOfBirth: ''
  }
}

const saveGuest = async () => {
  if (!currentBookingId.value) return

  // Find the booking to check capacity
  const booking = bookings.value.find(b => b.id === currentBookingId.value)
  if (!booking) {
    error.value = 'Foglalás nem található'
    return
  }

  // Check capacity before adding (not when editing)
  if (!editingGuest.value) {
    const currentCount = getCurrentGuestCount(booking)
    const maxCapacity = getMaxCapacity(booking)
    
    if (currentCount >= maxCapacity) {
      error.value = `Elérte a maximális vendégkapacitást. Ez a foglalás ${maxCapacity} vendéget tud elhelyezni.`
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
    } else {
      // Add new guest
      await guestService.addGuests(currentBookingId.value, [{
        name: guestForm.value.name,
        idNumber: guestForm.value.idNumber,
        dateOfBirth: guestForm.value.dateOfBirth
      }])
    }
    await loadBookings()
    closeGuestModal()
    error.value = '' // Clear any previous errors
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'A vendég információk mentése sikertelen'
  } finally {
    savingGuest.value = false
  }
}

const deleteGuest = async (guestId, bookingId) => {
  if (!confirm('Biztosan törölni szeretné ezt a vendéget?')) {
    return
  }

  try {
    await guestService.deleteGuest(guestId)
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || 'A vendég törlése sikertelen'
  }
}

const getMaxCapacity = (booking) => {
  if (!booking.rooms || booking.rooms.length === 0) {
    return 0
  }
  return booking.rooms.reduce((total, room) => {
    // Handle different capacity formats
    let capacity = 0
    if (typeof room.capacity === 'number') {
      capacity = room.capacity
    } else if (typeof room.capacity === 'string') {
      // Try to parse the string, handle cases like "2 guests" or just "2"
      const parsed = parseInt(room.capacity)
      capacity = isNaN(parsed) ? 0 : parsed
    }
    return total + capacity
  }, 0)
}

const getCurrentGuestCount = (booking) => {
  return booking.guests ? booking.guests.length : 0
}

const isAtCapacity = (booking) => {
  return getCurrentGuestCount(booking) >= getMaxCapacity(booking)
}

const downloadInvoice = async (invoiceId, bookingId) => {
  invoiceLoading.value = bookingId
  try {
    const blob = await invoiceService.downloadInvoice(invoiceId)
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `invoice_${invoiceId}.pdf`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (err) {
    error.value = err.response?.data?.message || 'A számla letöltése sikertelen'
  } finally {
    invoiceLoading.value = null
  }
}

// Booking Actions Modal
const openBookingActionsModal = (booking) => {
  selectedBookingForActions.value = booking
  showBookingActionsModal.value = true
}

const closeBookingActionsModal = () => {
  showBookingActionsModal.value = false
  selectedBookingForActions.value = null
}

const handleCancelBooking = async () => {
  if (!selectedBookingForActions.value) return
  closeBookingActionsModal()
  await cancelBooking(selectedBookingForActions.value.id)
}

const handleOpenGuestModal = () => {
  if (!selectedBookingForActions.value) return
  // Don't close the booking actions modal, just open guest modal on top
  openGuestModal(selectedBookingForActions.value.id)
}

const handleEditGuest = (guest) => {
  if (!selectedBookingForActions.value) return
  // Don't close the booking actions modal, just open guest modal on top
  editGuest(guest, selectedBookingForActions.value.id)
}

const handleDeleteGuest = async (guestId) => {
  if (!selectedBookingForActions.value) return
  if (!confirm('Biztosan törölni szeretné ezt a vendéget?')) {
    return
  }
  await deleteGuest(guestId, selectedBookingForActions.value.id)
  // Reload the booking data
  await loadBookings()
  // Update the selected booking with fresh data
  const updatedBooking = bookings.value.find(b => b.id === selectedBookingForActions.value.id)
  if (updatedBooking) {
    selectedBookingForActions.value = updatedBooking
  }
}

const handleDownloadInvoice = async () => {
  if (!selectedBookingForActions.value || !selectedBookingForActions.value.invoice) return
  await downloadInvoice(selectedBookingForActions.value.invoice.id, selectedBookingForActions.value.id)
}
</script>

<style scoped>
.bookings-page {
  min-height: 100vh;
  background: #f8f9fa;
  padding: 2rem;
}

.page-header {
  max-width: 1400px;
  margin: 0 auto 2rem;
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

.btn-primary {
  display: inline-block;
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
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

/* Dates Section */
.booking-dates-section {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 0.75rem;
  margin: 0 auto 1.5rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
  border-radius: 12px;
  width: 100%;
  max-width: 420px;
  box-sizing: border-box;
}

.date-card {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-sizing: border-box;
  text-align: center;
}

.date-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.date-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
  flex: 1;
  overflow: hidden;
}

.date-label {
  font-size: 0.85rem;
  color: #7f8c8d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.25rem;
  white-space: nowrap;
}

.date-value {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
  overflow-wrap: break-word;
  word-break: break-word;
  line-height: 1.4;
}

.date-separator {
  font-size: 1.5rem;
  color: #667eea;
  font-weight: 600;
  text-align: center;
  padding: 0.25rem 0;
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
.booking-services-section {
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 1rem;
}

.rooms-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.room-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.room-icon {
  font-size: 1.5rem;
}

.room-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.room-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.25rem;
}

.room-capacity {
  font-size: 0.9rem;
  color: #7f8c8d;
}

/* Services Section */
.services-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.service-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.service-name {
  font-weight: 500;
  color: #2c3e50;
}

.service-price {
  font-weight: 600;
  color: #667eea;
}

/* Booking Actions */
.booking-actions {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 2px solid #f0f0f0;
}

.btn-cancel {
  width: 100%;
  padding: 0.875rem 1.5rem;
  background: #e74c3c;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel:hover:not(:disabled) {
  background: #c0392b;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

.btn-cancel:disabled {
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

/* Guests Section */
.booking-guests-section {
  margin-bottom: 1.5rem;
}

.guests-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.guests-title-section {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.guests-capacity-info {
  font-size: 0.85rem;
  color: #667eea;
  font-weight: 600;
}

.btn-add-guest {
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-add-guest:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-add-guest:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.guests-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.guest-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.guest-item:hover {
  background: #f0f0f0;
}

.guest-icon {
  font-size: 1.5rem;
}

.guest-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.guest-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.25rem;
}

.guest-details {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.guest-id,
.guest-dob {
  font-size: 0.85rem;
  color: #7f8c8d;
}

.guest-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-edit-guest,
.btn-delete-guest {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 8px;
  transition: all 0.3s ease;
  opacity: 0.7;
}

.btn-edit-guest:hover:not(:disabled) {
  background: #e8f4f8;
  opacity: 1;
  transform: scale(1.1);
}

.btn-delete-guest:hover:not(:disabled) {
  background: #fee;
  opacity: 1;
  transform: scale(1.1);
}

.btn-edit-guest:disabled,
.btn-delete-guest:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.no-guests {
  padding: 1.5rem;
  text-align: center;
  background: #f8f9fa;
  border-radius: 12px;
  color: #7f8c8d;
  font-size: 0.9rem;
}

.capacity-warning {
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  background: #fff3cd;
  border: 1px solid #ffc107;
  border-radius: 8px;
  color: #856404;
  font-size: 0.9rem;
  text-align: center;
  font-weight: 500;
}

/* Guest Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.guest-modal-overlay {
  z-index: 1001;
}

.modal-content {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  width: 90%;
  max-width: 500px;
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
  border-bottom: 2px solid #f0f0f0;
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
  color: #7f8c8d;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.btn-close-modal:hover {
  background: #f0f0f0;
  color: #2c3e50;
}

.guest-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.guest-form .form-group {
  display: flex;
  flex-direction: column;
}

.guest-form label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.guest-form input {
  padding: 0.875rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.guest-form input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.modal-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.btn-cancel-modal {
  flex: 1;
  padding: 0.875rem 1.5rem;
  background: #f0f0f0;
  color: #2c3e50;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel-modal:hover {
  background: #e0e0e0;
}

.btn-save-guest {
  flex: 1;
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-save-guest:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-save-guest:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 768px) {
  .bookings-page {
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
    padding: 1rem;
  }

  .date-card {
    max-width: 100%;
    width: 100%;
  }

  .date-separator {
    transform: rotate(90deg);
  }

  .booking-details {
    grid-template-columns: 1fr;
  }

  .guests-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }  .btn-add-guest {
    width: 100%;
  }  .modal-content {
    width: 95%;
    padding: 1.5rem;
  }

  .bookings-filters {
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .filter-btn {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
  }

  .bookings-table-container {
    overflow-x: scroll;
  }

  .bookings-table {
    font-size: 0.85rem;
  }

  .bookings-table th,
  .bookings-table td {
    padding: 0.75rem 0.5rem;
  }
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
  justify-content: space-between;
  gap: 1rem;
}

.invoice-number {
  font-size: 0.9rem;
  color: #667eea;
  font-weight: 600;
}

.invoice-amount {
  font-size: 1rem;
  color: #27ae60;
  font-weight: 700;
}

.btn-download-invoice {
  width: 100%;
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-download-invoice:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-download-invoice:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Bookings Filters */
.bookings-filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.filter-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: #f8f9fa;
  border: 2px solid transparent;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 600;
  color: #2c3e50;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.filter-btn:hover {
  background: #e9ecef;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.filter-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.filter-icon {
  font-size: 1.1rem;
}

.filter-count {
  font-size: 0.85rem;
  opacity: 0.8;
}

/* Bookings Table (for Archived Bookings) */
.bookings-table-container {
  margin-top: 2rem;
  overflow-x: auto;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.bookings-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.95rem;
}

.bookings-table thead {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.bookings-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.bookings-table tbody tr {
  border-bottom: 1px solid #f0f0f0;
  transition: background-color 0.2s;
}

.bookings-table tbody tr:hover {
  background-color: #f8f9fa;
}

.bookings-table tbody tr:last-child {
  border-bottom: none;
}

.bookings-table td {
  padding: 1rem;
  color: #2c3e50;
}

.booking-id-cell {
  font-weight: 600;
  color: #667eea;
}

.hotel-cell {
  min-width: 200px;
}

.hotel-info-inline {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.hotel-name-inline {
  font-weight: 600;
  color: #2c3e50;
}

.hotel-address-inline {
  font-size: 0.85rem;
  color: #7f8c8d;
}

.price-cell {
  font-weight: 600;
  color: #27ae60;
}

.table-row-cancelled {
  opacity: 0.7;
}

.table-row-finished {
  opacity: 0.8;
}

/* View Switcher */
.view-switcher {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.view-switcher-label {
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.95rem;
}

.view-switcher-buttons {
  display: flex;
  gap: 0.5rem;
}

.view-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: #f8f9fa;
  border: 2px solid transparent;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  color: #2c3e50;
  cursor: pointer;
  transition: all 0.3s ease;
}

.view-btn:hover {
  background: #e9ecef;
  transform: translateY(-2px);
}

.view-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.view-icon {
  font-size: 1.1rem;
}

/* Table View Actions */
.actions-cell {
  text-align: center;
}

.btn-actions {
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.btn-actions:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* Booking Actions Modal */
.booking-actions-modal {
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  z-index: 1000;
}

.booking-actions-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.booking-summary-section {
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-top: 1rem;
}

.summary-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.summary-label {
  font-size: 0.85rem;
  color: #7f8c8d;
  font-weight: 500;
}

.summary-value {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
}

.summary-value.price {
  color: #27ae60;
  font-size: 1.1rem;
}

.actions-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.action-group {
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.action-group-title {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 1rem;
}

.action-btn {
  width: 100%;
  padding: 0.875rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-bottom: 0.5rem;
}

.action-btn.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.action-btn.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.action-btn.btn-danger {
  background: #e74c3c;
  color: white;
}

.action-btn.btn-danger:hover:not(:disabled) {
  background: #c0392b;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

.action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.guests-list-modal {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1rem;
}

.guest-item-modal {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.guest-info-modal {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.guest-name-modal {
  font-weight: 600;
  color: #2c3e50;
}

.guest-id-modal {
  font-size: 0.85rem;
  color: #7f8c8d;
}

.guest-actions-modal {
  display: flex;
  gap: 0.5rem;
}

.btn-edit-guest-modal,
.btn-delete-guest-modal {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 8px;
  transition: all 0.3s ease;
  opacity: 0.7;
}

.btn-edit-guest-modal:hover:not(:disabled) {
  background: #e8f4f8;
  opacity: 1;
  transform: scale(1.1);
}

.btn-delete-guest-modal:hover:not(:disabled) {
  background: #fee;
  opacity: 1;
  transform: scale(1.1);
}

.btn-edit-guest-modal:disabled,
.btn-delete-guest-modal:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.no-guests-modal {
  padding: 1rem;
  text-align: center;
  color: #7f8c8d;
  font-size: 0.9rem;
  background: white;
  border-radius: 8px;
  margin-top: 1rem;
}

.invoice-info-modal {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.invoice-details-modal {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.invoice-number-modal {
  font-size: 0.9rem;
  color: #667eea;
  font-weight: 600;
}

.invoice-amount-modal {
  font-size: 1rem;
  color: #27ae60;
  font-weight: 700;
}

/* Responsive */
@media (max-width: 768px) {
  .view-switcher {
    flex-direction: column;
    align-items: stretch;
  }

  .view-switcher-buttons {
    width: 100%;
  }

  .view-btn {
    flex: 1;
    justify-content: center;
  }

  .summary-grid {
    grid-template-columns: 1fr;
  }

  .booking-actions-modal {
    max-width: 95%;
  }
}
</style>