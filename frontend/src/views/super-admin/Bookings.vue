<template>
  <SuperAdminLayout>
    <div class="bookings-page">
      <div class="page-header">
        <h1>Foglalások kezelése</h1>
        <button @click="openCreateModal" class="btn-primary">
          <span>➕</span> Foglalás létrehozása
        </button>
      </div>

      <DataTable
        :data="bookings"
        :columns="columns"
        :loading="loading"
        search-placeholder="Foglalások keresése..."
        empty-message="Nincs foglalás"
        :search-fields="['id', 'user.name', 'hotel.name']"
        :on-edit="handleEdit"
        :on-delete="handleDelete"
      >
        <template #cell-user="{ value }">
          {{ value?.name || 'N/A' }} ({{ value?.email || '' }})
        </template>
        <template #cell-hotel="{ value }">
          {{ value?.name || 'N/A' }}
        </template>
        <template #cell-status="{ value }">
          <span class="status-badge" :class="`status-${value}`">{{ value }}</span>
        </template>
        <template #cell-totalPrice="{ value }">
          €{{ parseFloat(value || 0).toFixed(2) }}
        </template>
        <template #cell-dates="{ row }">
          {{ formatDate(row.startDate) }} - {{ formatDate(row.endDate) }}
        </template>
        <template #actions="{ row }">
          <button @click="handleEdit(row)" class="btn-icon btn-edit" title="Szerkesztés">✏️</button>
          <button @click="handleDelete(row)" class="btn-icon btn-delete" title="Törlés">🗑️</button>
        </template>
      </DataTable>

      <!-- Edit Modal -->
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal-content large">
            <div class="modal-header">
              <h2>{{ editingBooking ? 'Foglalás szerkesztése' : 'Új foglalás létrehozása' }}</h2>
              <button class="modal-close" @click="closeModal">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-body">
              <div v-if="error" class="error-message">{{ error }}</div>

              <div class="form-group">
                <label>Vendég (Felhasználó) *</label>
                <select v-model="form.users_id" required>
                  <option value="">Válasszon felhasználót...</option>
                  <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                    {{ user.name }} ({{ user.email }})
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label>Szálloda *</label>
                <select v-model="form.hotels_id" required @change="loadHotelData">
                  <option value="">Válasszon szállodát...</option>
                  <option v-for="hotel in availableHotels" :key="hotel.id" :value="hotel.id">
                    {{ hotel.name }} - {{ hotel.location }}
                  </option>
                </select>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Érkezési dátum *</label>
                  <input v-model="form.startDate" type="date" required />
                </div>
                <div class="form-group">
                  <label>Távozási dátum *</label>
                  <input v-model="form.endDate" type="date" required />
                </div>
              </div>

              <!-- Rooms Selection (only for create) -->
              <div v-if="!editingBooking" class="form-section">
                <h3 class="section-title">Szobák *</h3>
                <div v-if="availableRooms.length === 0 && form.hotels_id" class="form-hint">
                  Válasszon először egy szállodát
                </div>
                <div v-else-if="availableRooms.length === 0" class="form-hint">
                  Nincs elérhető szoba ehhez a szállodához
                </div>
                <div v-else class="rooms-selection">
                  <div v-for="(room, index) in form.rooms" :key="index" class="room-item">
                    <select v-model="room.id" @change="updateRoomPrice(index)" required>
                      <option value="">Válasszon szobát...</option>
                      <option v-for="r in availableRooms" :key="r.id" :value="r.id" :disabled="isRoomSelected(r.id, index)">
                        {{ r.name }} - {{ r.pricePerNight }}€/éj (Kapacitás: {{ r.capacity }})
                      </option>
                    </select>
                    <input v-model.number="room.guests" type="number" min="1" :max="getRoomCapacity(room.id)" placeholder="Vendégek száma" required />
                    <button v-if="form.rooms.length > 1" @click="removeRoom(index)" type="button" class="btn-remove">🗑️</button>
                  </div>
                  <button @click="addRoom" type="button" class="btn-add-room">➕ Szoba hozzáadása</button>
                </div>
              </div>

              <!-- Services Selection (only for create) -->
              <div v-if="!editingBooking" class="form-section">
                <h3 class="section-title">Szolgáltatások (opcionális)</h3>
                <div v-if="availableServices.length === 0 && form.hotels_id" class="form-hint">
                  Nincs elérhető szolgáltatás ehhez a szállodához
                </div>
                <div v-else class="services-selection">
                  <label v-for="service in availableServices" :key="service.id" class="service-checkbox">
                    <input type="checkbox" :value="service.id" v-model="form.services" />
                    <span>{{ service.name }} - {{ service.price }}€</span>
                  </label>
                </div>
              </div>

              <!-- Total Price (calculated for create, editable for edit) -->
              <div class="form-group">
                <label>Összesen (€) *</label>
                <input v-if="editingBooking" v-model.number="form.totalPrice" type="number" step="0.01" min="0" required />
                <div v-else class="calculated-price">
                  {{ calculatedTotalPrice.toFixed(2) }}€
                  <small>(automatikusan számítva)</small>
                </div>
              </div>

              <div class="form-group">
                <label>Státusz *</label>
                <select v-model="form.status" required>
                  <option value="pending">Függőben</option>
                  <option value="confirmed">Megerősítve</option>
                  <option value="cancelled">Törölve</option>
                  <option value="finished">Befejezve</option>
                </select>
              </div>

              <!-- Payment and Invoice Details (only for create) -->
              <div v-if="!editingBooking" class="form-section">
                <h3 class="section-title">Fizetés és számlázás (opcionális)</h3>
                <div class="form-group">
                  <label>Fizetési mód</label>
                  <select v-model="form.payment_method">
                    <option value="">Nincs kiválasztva</option>
                    <option value="bank_transfer">Banki átutalás</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Ügyfél típusa</label>
                  <select v-model="form.invoice_details.customer_type">
                    <option value="">Nincs kiválasztva</option>
                    <option value="private">Magánszemély</option>
                    <option value="business">Cég</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Teljes név</label>
                  <input v-model="form.invoice_details.full_name" type="text" />
                </div>
                <div class="form-group">
                  <label>Email</label>
                  <input v-model="form.invoice_details.email" type="email" />
                </div>
                <div class="form-group">
                  <label>Cégnév</label>
                  <input v-model="form.invoice_details.company_name" type="text" />
                </div>
                <div class="form-group">
                  <label>Adószám</label>
                  <input v-model="form.invoice_details.tax_number" type="text" />
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Ország</label>
                    <input v-model="form.invoice_details.country" type="text" />
                  </div>
                  <div class="form-group">
                    <label>Város</label>
                    <input v-model="form.invoice_details.city" type="text" />
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Irányítószám</label>
                    <input v-model="form.invoice_details.postal_code" type="text" />
                  </div>
                  <div class="form-group">
                    <label>Cím</label>
                    <input v-model="form.invoice_details.address_line" type="text" />
                  </div>
                </div>
                <div class="form-group">
                  <label>Megjegyzés</label>
                  <textarea v-model="form.invoice_details.note" rows="3"></textarea>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" @click="closeModal" class="btn-secondary">Mégse</button>
                <button type="submit" class="btn-primary" :disabled="saving">
                  {{ saving ? 'Mentés...' : 'Mentés' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>

      <ConfirmDialog
        v-model:visible="showDeleteDialog"
        title="Foglalás törlése"
        :message="`Biztosan törölni szeretné a #${bookingToDelete?.id} foglalást? Ez a művelet nem vonható vissza.`"
        confirm-text="Törlés"
        cancel-text="Mégse"
        confirm-type="danger"
        @confirm="confirmDelete"
      />

      <Toast ref="toast" />
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import SuperAdminLayout from '../../layouts/SuperAdminLayout.vue'
import DataTable from '../../components/DataTable.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import Toast from '../../components/Toast.vue'
import { superAdminService } from '../../services/superAdminService'
import { hotelService } from '../../services/hotelService'
import { useBodyScrollLock } from '../../composables/useBodyScrollLock'

const bookings = ref([])
const availableUsers = ref([])
const availableHotels = ref([])
const availableRooms = ref([])
const availableServices = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteDialog = ref(false)
const editingBooking = ref(null)
const bookingToDelete = ref(null)
const saving = ref(false)
const error = ref('')
const toast = ref(null)

const form = ref({
  users_id: '',
  hotels_id: '',
  startDate: '',
  endDate: '',
  rooms: [{ id: '', guests: 1 }],
  services: [],
  totalPrice: 0,
  status: 'pending',
  payment_method: '',
  invoice_details: {
    customer_type: '',
    full_name: '',
    email: '',
    company_name: '',
    tax_number: '',
    country: '',
    city: '',
    postal_code: '',
    address_line: '',
    note: ''
  }
})

const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'user', label: 'Vendég' },
  { key: 'hotel', label: 'Szálloda' },
  { key: 'dates', label: 'Dátumok' },
  { key: 'status', label: 'Státusz', sortable: true },
  { key: 'totalPrice', label: 'Összesen', sortable: true }
]

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('hu-HU')
}

const loadBookings = async () => {
  loading.value = true
  try {
    const data = await superAdminService.getAllBookings()
    bookings.value = data
  } catch (err) {
    showToast('A foglalások betöltése sikertelen', 'error')
  } finally {
    loading.value = false
  }
}

const loadUsers = async () => {
  try {
    const data = await superAdminService.getAllUsers()
    availableUsers.value = data.filter(u => u.role === 'user')
  } catch (err) {
    console.error('Failed to load users:', err)
  }
}

const loadHotels = async () => {
  try {
    const data = await superAdminService.getAllHotels()
    availableHotels.value = data
  } catch (err) {
    console.error('Failed to load hotels:', err)
  }
}

const openCreateModal = async () => {
  editingBooking.value = null
  resetForm()
  await Promise.all([loadUsers(), loadHotels()])
  showModal.value = true
}

const loadHotelData = async () => {
  if (!form.value.hotels_id) {
    availableRooms.value = []
    availableServices.value = []
    return
  }
  
  try {
    const [rooms, services] = await Promise.all([
      hotelService.getRoomsByHotelId(form.value.hotels_id),
      hotelService.getServicesByHotelId(form.value.hotels_id)
    ])
    availableRooms.value = rooms
    availableServices.value = services
  } catch (err) {
    console.error('Failed to load hotel data:', err)
    availableRooms.value = []
    availableServices.value = []
  }
}

const addRoom = () => {
  form.value.rooms.push({ id: '', guests: 1 })
}

const removeRoom = (index) => {
  form.value.rooms.splice(index, 1)
}

const isRoomSelected = (roomId, currentIndex) => {
  return form.value.rooms.some((room, index) => room.id === roomId && index !== currentIndex)
}

const getRoomCapacity = (roomId) => {
  const room = availableRooms.value.find(r => r.id === roomId)
  return room?.capacity || 1
}

const updateRoomPrice = (index) => {
  // Trigger recalculation
}

const calculatedTotalPrice = computed(() => {
  if (!form.value.startDate || !form.value.endDate || form.value.rooms.length === 0) {
    return 0
  }
  
  const start = new Date(form.value.startDate)
  const end = new Date(form.value.endDate)
  const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24))
  
  let total = 0
  
  // Calculate room prices
  form.value.rooms.forEach(roomData => {
    if (roomData.id) {
      const room = availableRooms.value.find(r => r.id === roomData.id)
      if (room) {
        total += (room.basePrice || 0) + (room.pricePerNight * nights)
      }
    }
  })
  
  // Add service prices
  form.value.services.forEach(serviceId => {
    const service = availableServices.value.find(s => s.id === serviceId)
    if (service) {
      total += service.price
    }
  })
  
  return total
})

const handleEdit = async (booking) => {
  editingBooking.value = booking
  form.value = {
    users_id: booking.users_id || '',
    hotels_id: booking.hotels_id || '',
    startDate: booking.startDate ? booking.startDate.split('T')[0] : '',
    endDate: booking.endDate ? booking.endDate.split('T')[0] : '',
    rooms: [{ id: '', guests: 1 }],
    services: [],
    totalPrice: booking.totalPrice || 0,
    status: booking.status || 'pending',
    payment_method: '',
    invoice_details: {
      customer_type: '',
      full_name: '',
      email: '',
      company_name: '',
      tax_number: '',
      country: '',
      city: '',
      postal_code: '',
      address_line: '',
      note: ''
    }
  }
  await Promise.all([loadUsers(), loadHotels()])
  showModal.value = true
}

const handleDelete = (booking) => {
  bookingToDelete.value = booking
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  if (!bookingToDelete.value) return

  try {
    await superAdminService.deleteBooking(bookingToDelete.value.id)
    showToast('Foglalás sikeresen törölve', 'success')
    await loadBookings()
  } catch (err) {
    showToast(err.response?.data?.message || 'A foglalás törlése sikertelen', 'error')
  } finally {
    bookingToDelete.value = null
  }
}

const handleSubmit = async () => {
  saving.value = true
  error.value = ''

  try {
    if (editingBooking.value) {
      // Update existing booking
      await superAdminService.updateBooking(editingBooking.value.id, {
        users_id: form.value.users_id,
        hotels_id: form.value.hotels_id,
        startDate: form.value.startDate,
        endDate: form.value.endDate,
        totalPrice: form.value.totalPrice,
        status: form.value.status
      })
      showToast('Foglalás sikeresen frissítve', 'success')
    } else {
      // Create new booking
      const bookingData = {
        users_id: form.value.users_id,
        hotels_id: form.value.hotels_id,
        startDate: form.value.startDate,
        endDate: form.value.endDate,
        rooms: form.value.rooms.filter(r => r.id),
        services: form.value.services,
        status: form.value.status
      }
      
      if (form.value.payment_method) {
        bookingData.payment_method = form.value.payment_method
      }
      
      const invoiceDetails = { ...form.value.invoice_details }
      const hasInvoiceDetails = Object.values(invoiceDetails).some(v => v && v !== '')
      if (hasInvoiceDetails) {
        bookingData.invoice_details = invoiceDetails
      }
      
      await superAdminService.createBooking(bookingData)
      showToast('Foglalás sikeresen létrehozva', 'success')
    }
    closeModal()
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || 'A foglalás mentése sikertelen'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingBooking.value = null
  resetForm()
  error.value = ''
}

// Lock body scroll when modal is open
useBodyScrollLock(showModal)

const resetForm = () => {
  form.value = {
    users_id: '',
    hotels_id: '',
    startDate: '',
    endDate: '',
    rooms: [{ id: '', guests: 1 }],
    services: [],
    totalPrice: 0,
    status: 'pending',
    payment_method: '',
    invoice_details: {
      customer_type: '',
      full_name: '',
      email: '',
      company_name: '',
      tax_number: '',
      country: '',
      city: '',
      postal_code: '',
      address_line: '',
      note: ''
    }
  }
  availableRooms.value = []
  availableServices.value = []
}

const showToast = (message, type) => {
  if (toast.value) {
    toast.value.showToast(message, type)
  } else if (window.showToast) {
    window.showToast(message, type)
  }
}

onMounted(async () => {
  await loadBookings()
})
</script>

<style scoped>
.bookings-page {
  max-width: 1400px;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending {
  background: rgba(251, 191, 36, 0.2);
  color: #fbbf24;
  border: 1px solid rgba(251, 191, 36, 0.3);
}

.status-confirmed {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.status-cancelled {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-completed {
  background: rgba(59, 130, 246, 0.2);
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: rgba(20, 20, 20, 0.95);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow: auto;
}

.modal-content.large {
  max-width: 800px;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(102, 126, 234, 0.2);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #e5e7eb;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: #9ca3af;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: background 0.2s;
}

.modal-close:hover {
  background: rgba(102, 126, 234, 0.1);
  color: #d1d5db;
}

.modal-body {
  padding: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #d1d5db;
  font-size: 0.9rem;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 8px;
  color: #e5e7eb;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  background: rgba(0, 0, 0, 0.5);
}

.error-message {
  background: rgba(220, 38, 38, 0.2);
  border: 1px solid rgba(220, 38, 38, 0.4);
  color: #fca5a5;
  padding: 0.875rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
}

.modal-footer {
  padding-top: 1.5rem;
  border-top: 1px solid rgba(102, 126, 234, 0.2);
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: transparent;
  color: #9ca3af;
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  border-color: rgba(102, 126, 234, 0.6);
  color: #d1d5db;
  background: rgba(102, 126, 234, 0.1);
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-icon {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  transition: background 0.2s;
}

.btn-edit:hover {
  background: rgba(59, 130, 246, 0.2);
}

.btn-delete:hover {
  background: rgba(239, 68, 68, 0.2);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.form-section {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(102, 126, 234, 0.2);
}

.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #e5e7eb;
  margin-bottom: 1rem;
}

.rooms-selection {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.room-item {
  display: grid;
  grid-template-columns: 2fr 1fr auto;
  gap: 0.75rem;
  align-items: center;
}

.room-item select,
.room-item input {
  padding: 0.75rem;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 8px;
  color: #e5e7eb;
  font-size: 0.95rem;
}

.btn-remove {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #ef4444;
  padding: 0.5rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-remove:hover {
  background: rgba(239, 68, 68, 0.2);
}

.btn-add-room {
  padding: 0.75rem;
  background: rgba(102, 126, 234, 0.1);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 8px;
  color: #667eea;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
  align-self: flex-start;
}

.btn-add-room:hover {
  background: rgba(102, 126, 234, 0.2);
}

.services-selection {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.service-checkbox {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(102, 126, 234, 0.2);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.service-checkbox:hover {
  background: rgba(0, 0, 0, 0.3);
  border-color: rgba(102, 126, 234, 0.4);
}

.service-checkbox input[type="checkbox"] {
  width: auto;
  margin: 0;
}

.calculated-price {
  padding: 0.75rem;
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.3);
  border-radius: 8px;
  color: #22c55e;
  font-weight: 600;
  font-size: 1.1rem;
}

.calculated-price small {
  display: block;
  font-size: 0.8rem;
  color: #86efac;
  font-weight: 400;
  margin-top: 0.25rem;
}

.form-hint {
  color: #9ca3af;
  font-size: 0.85rem;
  font-style: italic;
  padding: 0.5rem;
}

.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(102, 126, 234, 0.3);
  border-radius: 8px;
  color: #e5e7eb;
  font-size: 0.95rem;
  font-family: inherit;
  resize: vertical;
}

.form-group textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  background: rgba(0, 0, 0, 0.5);
}
</style>
