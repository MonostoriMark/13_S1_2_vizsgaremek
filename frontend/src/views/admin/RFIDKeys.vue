<template>
  <AdminLayout>
    <div class="rfid-keys-page">
      <div class="page-header">
        <h1>RFID kulcsok</h1>
        <button @click="openCreateModal" class="btn-primary">
          <span>➕</span> RFID kulcs hozzáadása
        </button>
      </div>

      <!-- Compact hotel selector (like bookings supervision) -->
      <div v-if="selectedHotel" class="hotel-selector-compact header-compact">
        <div class="hotel-compact-info">
          <div class="hotel-compact-icon">🏨</div>
          <div class="hotel-compact-details">
            <div class="hotel-compact-name">
              {{ selectedHotel.name || `Szálloda #${selectedHotel.id}` }}
            </div>
            <div class="hotel-compact-location">
              📍 {{ selectedHotel.location || 'Helyszín nincs megadva' }}
            </div>
          </div>
        </div>
        <button
          v-if="hotels.length > 1"
          @click="openHotelCarousel"
          class="hotel-change-btn"
          title="Szálloda váltása"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 4V10H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M23 20V14H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14L18.36 18.36A9 9 0 0 1 3.51 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Váltás</span>
        </button>
      </div>

      <!-- If no hotel is yet selected (initial load), we simply hide the selector;
           the first hotel will be auto-selected in script once data arrives. -->

      <!-- Minimal hotel carousel overlay (shared pattern with bookings) -->
      <Transition name="fade">
        <div
          v-if="hotels.length > 1 && showHotelCarousel"
          class="hotel-carousel-overlay"
          @click.self="closeHotelCarousel"
        >
          <div class="hotel-carousel-container-minimal">
            <div class="hotel-carousel-header-minimal">
              <h3 class="carousel-title-minimal">🏨 Szálloda kiválasztása</h3>
              <button @click="closeHotelCarousel" class="carousel-close-btn-minimal">×</button>
            </div>

            <div class="hotel-carousel-wrapper-minimal">
              <button
                @click="previousHotel"
                class="carousel-nav-btn-modern carousel-prev-modern"
                title="Előző szálloda"
              >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M15 18L9 12L15 6" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>

              <div class="hotel-carousel-minimal">
                <div
                  class="hotel-card-carousel-minimal"
                  :style="{ transform: `translateX(-${currentHotelIndex * 100}%)` }"
                >
                  <div
                    v-for="hotel in hotels"
                    :key="hotel.id"
                    class="hotel-card-item-minimal"
                    @click="selectHotelFromCarousel(hotel.id)"
                    :class="{ selected: selectedHotelId === hotel.id }"
                  >
                    <div class="hotel-card-image-minimal">
                      <img
                        v-if="hotel.cover_image"
                        :src="getImageUrl(hotel.cover_image)"
                        :alt="hotel.name || 'Hotel'"
                        class="hotel-cover-image-minimal"
                        @error="handleImageError"
                      />
                      <div v-else class="hotel-image-placeholder-minimal">
                        <span class="hotel-icon-minimal">🏨</span>
                      </div>
                    </div>
                    <div class="hotel-card-content-minimal">
                      <h4 class="hotel-card-name-minimal">
                        {{ hotel.name || `Szálloda #${hotel.id}` }}
                      </h4>
                      <p class="hotel-card-location-minimal">
                        📍 {{ hotel.location || 'Helyszín nincs megadva' }}
                      </p>
                      <button class="hotel-select-btn-minimal">
                        {{ selectedHotelId === hotel.id ? '✓ Kiválasztva' : 'Kiválasztás →' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <button
                @click="nextHotel"
                class="carousel-nav-btn-modern carousel-next-modern"
                title="Következő szálloda"
              >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M9 18L15 12L9 6" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </div>

            <div class="carousel-indicators-minimal">
              <button
                v-for="(hotel, index) in hotels"
                :key="hotel.id"
                @click="goToHotel(index)"
                class="carousel-indicator-minimal"
                :class="{ active: currentHotelIndex === index }"
              ></button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Calendar view - primary content -->
      <div v-if="selectedHotel" class="calendar-section card">
        <div class="calendar-header">
          <div>
            <h2>RFID kulcs naptár</h2>
            <p class="calendar-subtitle">
              Színes sávok jelzik, hogy melyik időszakban melyik RFID kulcs melyik foglaláshoz tartozik.
            </p>
          </div>
          <div class="calendar-controls">
            <label>
              <span>Hónap:</span>
              <input type="month" v-model="currentMonth" @change="loadCalendar" />
            </label>
          </div>
        </div>

        <div v-if="calendarLoading" class="calendar-loading">
          <div class="loading-spinner"></div>
          <p>RFID kulcs foglalások betöltése...</p>
        </div>

        <div v-else-if="calendarEvents.length === 0" class="calendar-empty">
          Nincs RFID kulcs foglalás az adott időszakban.
        </div>

        <div v-else class="calendar-scroll-wrapper">
          <div class="calendar-grid">
            <!-- Header row with weekday names -->
            <div class="calendar-row calendar-row-header">
              <div
                v-for="(label, index) in weekdayLabels"
                :key="index"
                class="cell day-header"
              >
                <div class="day-label">{{ label }}</div>
              </div>
            </div>

            <!-- Month days in week rows -->
            <div
              v-for="(week, wIndex) in calendarWeeks"
              :key="wIndex"
              class="calendar-row"
            >
              <div
                v-for="day in week"
                :key="day.date"
                :class="['cell', 'month-day', { 'outside-month': !day.inCurrentMonth }]"
              >
                <div class="day-top">
                  <span class="day-number">{{ day.day }}</span>
                </div>
                <div class="day-events">
                  <div
                    v-for="segment in getSegmentsForDay(day.date)"
                    :key="segment.event.id"
                    class="event-bar"
                    :class="{
                      'event-bar-single': segment.isStart && segment.isEnd,
                      'event-bar-start': segment.isStart && !segment.isEnd,
                      'event-bar-end': segment.isEnd && !segment.isStart,
                      'event-bar-middle': !segment.isStart && !segment.isEnd
                    }"
                    :style="{ backgroundColor: getColorForKey(segment.event.rfid_uid) }"
                    @mouseenter="showEventTooltip(segment.event, $event)"
                    @mouseleave="hideEventTooltip"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <div class="calendar-legend">
            <span class="legend-title">Jelmagyarázat:</span>
            <span
              v-for="item in legendItems"
              :key="item.uid"
              class="legend-item"
            >
              <span
                class="legend-dot"
                :style="{ backgroundColor: item.color }"
              ></span>
              <span class="legend-label">{{ item.uid }}</span>
            </span>
            <span class="legend-item legend-help">
              Vigye az egeret egy pontra a foglalás részleteinek megtekintéséhez, majd kattintson a gombra a foglalás megnyitásához.
            </span>
          </div>

          <!-- Hover tooltip for event details -->
          <div
            v-if="tooltipVisible && hoveredEvent"
            class="event-tooltip"
            :style="{ top: tooltipPosition.y + 'px', left: tooltipPosition.x + 'px' }"
          >
            <div class="tooltip-key">UID: {{ hoveredEvent.rfid_uid }}</div>
            <div class="tooltip-row">
              <span class="tooltip-label">Vendég:</span>
              <span>{{ hoveredEvent.guest_name || 'Ismeretlen' }}</span>
            </div>
            <div class="tooltip-row">
              <span class="tooltip-label">Szoba:</span>
              <span>{{ hoveredEvent.room_name || 'Ismeretlen' }}</span>
            </div>
            <div class="tooltip-row">
              <span class="tooltip-label">Időszak:</span>
              <span>{{ hoveredEvent.reserved_from }} → {{ hoveredEvent.reserved_to }}</span>
            </div>
            <div class="tooltip-actions" v-if="hoveredEvent.booking_id">
              <button
                type="button"
                class="btn-link"
                @click="goToBooking(hoveredEvent.booking_id)"
              >
                Foglalás megnyitása
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Table with keys under the calendar -->
      <div v-if="selectedHotel" class="keys-table-section">
        <div class="keys-filter-bar">
          <label>
            <span class="filter-label">Kártyatípus:</span>
            <select v-model="typeFilter" class="filter-select">
              <option value="all">Összes</option>
              <option value="guest">Vendégkártyák</option>
              <option value="crew">Személyzeti kártyák</option>
            </select>
          </label>
        </div>

        <DataTable
          :data="filteredKeys"
          :columns="columns"
          :loading="loading"
          search-placeholder="Keresés UID alapján..."
          empty-message="Nincsenek RFID kulcsok"
          :search-fields="['uid']"
          :on-edit="handleEdit"
          :on-delete="handleDelete"
        >
          <template #actions="{ row }">
            <button
              v-if="row.type === 'crew'"
              @click="openAssignModal(row)"
              class="btn-icon btn-assign"
              title="RFID kulcs hozzárendelése szobához"
            >
              🔑
            </button>
            <button
              @click="handleRelease(row)"
              class="btn-icon btn-release"
              title="Aktív hozzárendelés feloldása"
            >
              🔓
            </button>
            <button @click="handleEdit(row)" class="btn-icon btn-edit" title="Szerkesztés">✏️</button>
            <button
              @click="handleDelete(row)"
              class="btn-icon btn-delete"
              title="Törlés"
            >
              🗑️
            </button>
          </template>
        </DataTable>
      </div>

      <!-- Create/Edit Modal -->
      <Transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
          <div class="modal-content">
            <div class="modal-header">
              <h2>{{ editingKey ? 'RFID kulcs szerkesztése' : 'RFID kulcs létrehozása' }}</h2>
              <button class="modal-close" @click="closeModal">×</button>
            </div>
            <form @submit.prevent="handleSubmit" class="modal-body">
              <div v-if="error" class="error-message">{{ error }}</div>

              <div v-if="!editingKey" class="form-group">
                <label>Szálloda kiválasztása *</label>
                <select v-model="form.hotelId" required class="form-select">
                  <option value="">Choose a hotel...</option>
                  <option v-for="hotel in hotels" :key="hotel.id" :value="hotel.id">
                    {{ hotel.name || hotel.location || `Hotel #${hotel.id}` }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label>Kártya típusa *</label>
                <select v-model="form.type" required>
                  <option value="guest">Vendégkártya</option>
                  <option value="crew">Személyzeti kártya</option>
                </select>
                <small class="form-hint">
                  A vendégkártyákhoz nem tartozik név vagy manuális szobahozzárendelés. A személyzeti kártyákhoz adhat nevet és kézzel rendelhet szobákat.
                </small>
              </div>

              <div class="form-group">
                <label>UID (RFID azonosító) *</label>
                <input
                  v-model="form.uid"
                  type="text"
                  required
                  placeholder="pl.: F4E4C928"
                  :disabled="editingKey"
                />
                <small class="form-hint">Egyedi RFID azonosító</small>
              </div>

              <div class="form-group" v-if="form.type === 'crew'">
                <label>Név / megnevezés</label>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="pl.: Portai mesterkártya, Marika kártyája"
                />
                <small class="form-hint">Segít azonosítani, hogy kihez vagy milyen célra tartozik a kártya.</small>
              </div>

              <div v-if="editingKey" class="form-group">
                <label>Státusz *</label>
                <select v-model="form.status" required>
                  <option value="available">Elérhető</option>
                  <option value="assigned">Hozzárendelve</option>
                </select>
                <small class="form-hint">Megjegyzés: jelenleg csak az „Elérhető” és „Hozzárendelve” státusz támogatott</small>
              </div>
              <div v-else class="info-box">
                <p>Az új RFID kulcsok alapértelmezés szerint <strong>Elérhető</strong> státusszal jönnek létre.</p>
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

      <!-- Assign Modal -->
      <Transition name="modal">
        <div v-if="showAssignModal" class="modal-overlay" @click.self="closeAssignModal">
          <div class="modal-content">
            <div class="modal-header">
              <h2>RFID kulcs hozzárendelése szobához</h2>
              <button class="modal-close" @click="closeAssignModal">×</button>
            </div>
            <div class="modal-body">
              <div v-if="assignError" class="error-message">{{ assignError }}</div>

              <div class="key-info">
                <p><strong>UID:</strong> {{ keyToAssign?.uid }}</p>
                <p v-if="keyToAssign?.name"><strong>Név:</strong> {{ keyToAssign.name }}</p>
              </div>

              <!-- Existing manual assignments for this crew card -->
              <div class="existing-assignments" v-if="keyToAssign && keyToAssign.type === 'crew'">
                <h3 class="section-title">Aktív hozzárendelések</h3>
                <div v-if="crewAssignmentsLoading" class="assignments-loading">
                  Hozzárendelések betöltése...
                </div>
                <div v-else-if="!crewAssignments.length" class="assignments-empty">
                  Jelenleg nincs aktív manuális hozzárendelés ehhez a kártyához.
                </div>
                <ul v-else class="assignments-list">
                  <li
                    v-for="assignment in crewAssignments"
                    :key="assignment.id"
                    class="assignment-item"
                  >
                    <div class="assignment-main">
                      <span class="assignment-room">{{ assignment.room_name || 'Ismeretlen szoba' }}</span>
                      <span class="assignment-dates">
                        {{ assignment.reserved_from }} → {{ assignment.reserved_to }}
                      </span>
                    </div>
                  </li>
                </ul>
              </div>

              <div class="form-group">
                <label>Szobák *</label>
                <div class="room-multiselect">
                  <label
                    v-for="room in hotelRooms"
                    :key="room.id"
                    class="room-chip"
                  >
                    <input
                      type="checkbox"
                      :value="room.id"
                      v-model="assignForm.room_ids"
                    />
                    <span>{{ room.name }}</span>
                  </label>
                </div>
              </div>

              <div class="form-row-booking">
                <div class="form-group">
                  <label>Kezdő dátum *</label>
                  <input
                    v-model="assignForm.start_date"
                    type="date"
                    required
                    class="form-input"
                  />
                </div>
                <div class="form-group">
                  <label>Záró dátum</label>
                  <input
                    v-model="assignForm.end_date"
                    type="date"
                    :min="assignForm.start_date || undefined"
                    :disabled="assignForm.lifetime"
                    class="form-input"
                  />
                </div>
              </div>

              <div class="form-group">
                <label class="checkbox-label">
                  <input
                    type="checkbox"
                    v-model="assignForm.lifetime"
                  />
                  <span>Élettartamú (master / személyzeti) kártya</span>
                </label>
                <p class="checkbox-help">
                  Ha be van jelölve, a kártya határozatlan ideig lesz hozzárendelve ehhez a szobához.
                </p>
              </div>

              <div class="modal-footer">
                <button type="button" @click="closeAssignModal" class="btn-secondary">Mégse</button>
                <button
                  type="button"
                  @click="confirmAssign"
                  class="btn-primary"
                  :disabled="assigning || !assignForm.room_ids.length || !assignForm.start_date"
                >
                  {{ assigning ? 'Hozzárendelés...' : 'Kulcs hozzárendelése' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <ConfirmDialog
        v-model:visible="showDeleteDialog"
        title="RFID kulcs törlése"
        :message="`Biztosan törölni szeretné ezt az RFID kulcsot? Ez a művelet nem vonható vissza.`"
        confirm-text="Törlés"
        cancel-text="Mégse"
        confirm-type="danger"
        @confirm="confirmDelete"
      />

      <ConfirmDialog
        v-model:visible="showReleaseDialog"
        title="RFID kulcs feloldása"
        :message="`Biztosan fel szeretné oldani ezt az RFID kulcsot a jelenlegi foglalásról?`"
        confirm-text="Feloldás"
        cancel-text="Mégse"
        confirm-type="primary"
        @confirm="confirmRelease"
      />

      <Toast ref="toast" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import AdminLayout from '../../layouts/AdminLayout.vue'
import DataTable from '../../components/DataTable.vue'
import ConfirmDialog from '../../components/ConfirmDialog.vue'
import Toast from '../../components/Toast.vue'
import { rfidKeyService } from '../../services/rfidKeyService'
import { adminService } from '../../services/adminService'
import { useAuthStore } from '../../stores/auth'
import { useBodyScrollLock } from '../../composables/useBodyScrollLock'

const router = useRouter()
const authStore = useAuthStore()
const hotels = ref([])
const keys = ref([])
const typeFilter = ref('all')
const loading = ref(false)
const showHotelCarousel = ref(false)
const currentHotelIndex = ref(0)
const showModal = ref(false)
const showAssignModal = ref(false)
const showDeleteDialog = ref(false)
const showReleaseDialog = ref(false)
const editingKey = ref(null)
const keyToAssign = ref(null)
const keyToDelete = ref(null)
const keyToRelease = ref(null)
const crewAssignments = ref([])
const crewAssignmentsLoading = ref(false)
const saving = ref(false)
const assigning = ref(false)
const error = ref('')
const assignError = ref('')
const toast = ref(null)
const selectedHotelId = ref(null)
const availableBookings = ref([]) // legacy, no longer used for manual assignment
const selectedBookingRooms = ref([]) // legacy, can be removed later
const hotelRooms = ref([])
const calendarEvents = ref([])
const calendarLoading = ref(false)
const currentMonth = ref(new Date().toISOString().slice(0, 7)) // YYYY-MM

// Weekday labels (Sunday start)
const weekdayLabels = ['V', 'H', 'K', 'Sze', 'Cs', 'P', 'Szo']

// Generate list of days for the whole calendar grid (6 weeks)
const visibleDays = computed(() => {
  const [year, month] = currentMonth.value.split('-').map(Number)
  if (!year || !month) return []

  const monthStart = new Date(year, month - 1, 1)
  const monthEnd = new Date(year, month, 0)

  // Start from the Sunday of the week containing the 1st
  const gridStart = new Date(monthStart)
  const dayOfWeek = gridStart.getDay() // 0 (Sun) - 6 (Sat)
  gridStart.setDate(gridStart.getDate() - dayOfWeek)

  const days = []
  for (let i = 0; i < 42; i++) {
    const d = new Date(gridStart)
    d.setDate(gridStart.getDate() + i)
    const dateStr = d.toISOString().slice(0, 10)
    days.push({
      date: dateStr,
      day: d.getDate(),
      inCurrentMonth: d >= monthStart && d <= monthEnd
    })
  }
  return days
})

// Days grouped into weeks (rows)
const calendarWeeks = computed(() => {
  const days = visibleDays.value
  const weeks = []
  for (let i = 0; i < days.length; i += 7) {
    weeks.push(days.slice(i, i + 7))
  }
  return weeks
})

// Color palette for keys
const colorPalette = ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#3b82f6', '#8b5cf6', '#14b8a6', '#ef4444']
const keyColorMap = {}

const getColorForKey = (uid) => {
  if (!uid) return '#9ca3af'
  if (!keyColorMap[uid]) {
    const idx = Object.keys(keyColorMap).length % colorPalette.length
    keyColorMap[uid] = colorPalette[idx]
  }
  return keyColorMap[uid]
}

// For a given day, return segments for events that cover that date,
// including flags to style the bar caps (start / middle / end / single-day).
const getSegmentsForDay = (dateStr) => {
  return calendarEvents.value
    .filter(ev => {
      if (!ev.reserved_from || !ev.reserved_to) return false
      return ev.reserved_from <= dateStr && ev.reserved_to >= dateStr
    })
    .map(ev => ({
      event: ev,
      isStart: ev.reserved_from === dateStr,
      isEnd: ev.reserved_to === dateStr
    }))
}

// Tooltip state
const hoveredEvent = ref(null)
const tooltipVisible = ref(false)
const tooltipPosition = ref({ x: 0, y: 0 })

const showEventTooltip = (event, domEvent) => {
  hoveredEvent.value = event
  tooltipVisible.value = true
  tooltipPosition.value = {
    x: domEvent.clientX + 12,
    y: domEvent.clientY + 12
  }
}

const hideEventTooltip = () => {
  tooltipVisible.value = false
  hoveredEvent.value = null
}

const goToBooking = (bookingId) => {
  if (!bookingId) return
  // Navigate to admin bookings list, passing the booking ID as a query param
  // The bookings page can use this to scroll/filter to the specific booking.
  router.push({ name: 'AdminBookings', query: { highlight: bookingId } })
}

const formatEventTooltip = (event) => {
  const from = event.reserved_from || ''
  const to = event.reserved_to || ''
  const guest = event.guest_name || 'Ismeretlen vendég'
  const room = event.room_name || 'Ismeretlen szoba'
  return `${guest} – ${room}\n${from} → ${to}`
}

// Legend items (one color per RFID UID)
const legendItems = computed(() => {
  const uids = Array.from(
    new Set(
      calendarEvents.value
        .map(ev => ev.rfid_uid)
        .filter(uid => !!uid)
    )
  )
  return uids.map(uid => ({
    uid,
    color: getColorForKey(uid)
  }))
})

const selectedHotel = computed(() => {
  return hotels.value.find(h => h.id === selectedHotelId.value)
})

const form = ref({
  hotelId: null,
  uid: '',
  type: 'guest',
  name: '',
  status: 'available'
})

const assignForm = ref({
  room_ids: [],
  start_date: '',
  end_date: '',
  lifetime: false
})

const columns = [
  { key: 'uid', label: 'UID', sortable: true },
  { key: 'name', label: 'Név / leírás', sortable: true },
  { key: 'type', label: 'Típus', sortable: true },
  { key: 'status', label: 'Státusz', sortable: true },
  { key: 'created_at', label: 'Létrehozva', type: 'date' }
]

const filteredKeys = computed(() => {
  if (typeFilter.value === 'all') return keys.value
  return keys.value.filter(key => (key.type || 'guest') === typeFilter.value)
})

const showToast = (message, type) => {
  if (toast.value) {
    toast.value.showToast(message, type)
  } else if (window.showToast) {
    window.showToast(message, type)
  }
}

const loadHotels = async () => {
  try {
    const data = await adminService.getHotels()
    hotels.value = data.filter(h => h.user_id === authStore.state.user?.id)
    
    // Auto-select first hotel if none selected yet
    if (hotels.value.length > 0 && !selectedHotelId.value) {
      selectedHotelId.value = hotels.value[0].id
      currentHotelIndex.value = 0
      await loadKeys()
      await loadCalendar()
      await loadHotelRooms()
    } else if (selectedHotelId.value) {
      const index = hotels.value.findIndex(h => h.id === selectedHotelId.value)
      if (index >= 0) currentHotelIndex.value = index
      await loadKeys()
      await loadCalendar()
      await loadHotelRooms()
    } else {
      loading.value = false
    }
  } catch (err) {
    showToast('Failed to load hotels', 'error')
    loading.value = false
  }
}

const handleHotelChange = async () => {
  if (selectedHotelId.value) {
    const index = hotels.value.findIndex(h => h.id === selectedHotelId.value)
    if (index >= 0) currentHotelIndex.value = index
    await loadKeys()
    await loadCalendar()
    await loadHotelRooms()
  } else {
    keys.value = []
    calendarEvents.value = []
    loading.value = false
  }
}

// Hotel carousel helpers (shared UX with bookings supervision)
const openHotelCarousel = () => {
  if (!hotels.value.length) return
  const index = hotels.value.findIndex(h => h.id === selectedHotelId.value)
  currentHotelIndex.value = index >= 0 ? index : 0
  showHotelCarousel.value = true
}

const closeHotelCarousel = () => {
  showHotelCarousel.value = false
}

const selectHotelFromCarousel = async (hotelId) => {
  selectedHotelId.value = hotelId
  const index = hotels.value.findIndex(h => h.id === hotelId)
  if (index >= 0) currentHotelIndex.value = index
  await loadKeys()
  await loadCalendar()
  await loadHotelRooms()
  showHotelCarousel.value = false
}

const previousHotel = () => {
  if (!hotels.value.length) return
  currentHotelIndex.value =
    (currentHotelIndex.value - 1 + hotels.value.length) % hotels.value.length
}

const nextHotel = () => {
  if (!hotels.value.length) return
  currentHotelIndex.value =
    (currentHotelIndex.value + 1) % hotels.value.length
}

const goToHotel = (index) => {
  if (index < 0 || index >= hotels.value.length) return
  currentHotelIndex.value = index
}

const getImageUrl = (relativePath) => {
  if (!relativePath) return ''
  const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || ''
  if (relativePath.startsWith('http')) return relativePath
  return `${baseUrl}${relativePath}`
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
}

const loadHotelRooms = async () => {
  if (!selectedHotelId.value) {
    hotelRooms.value = []
    return
  }
  try {
    const roomsData = await adminService.getRoomsByHotelId(selectedHotelId.value)
    hotelRooms.value = roomsData || []
  } catch (err) {
    console.error('Failed to load rooms for RFID assignment:', err)
    hotelRooms.value = []
  }
}

const loadKeys = async () => {
  if (!selectedHotelId.value) {
    loading.value = false
    keys.value = []
    return
  }

  loading.value = true
  try {
    const response = await rfidKeyService.getKeys({ hotel_id: selectedHotelId.value })
    // Handle response structure
    if (Array.isArray(response)) {
      keys.value = response
    } else if (response && Array.isArray(response.keys)) {
      keys.value = response.keys
    } else if (response && response.keys) {
      keys.value = response.keys
    } else {
      keys.value = []
    }
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to load RFID keys', 'error')
    keys.value = []
  } finally {
    loading.value = false
  }
}

const loadCalendar = async () => {
  if (!selectedHotelId.value) {
    calendarEvents.value = []
    return
  }
  calendarLoading.value = true
  try {
    const [year, month] = currentMonth.value.split('-')
    const startDate = `${year}-${month}-01`
    const endDateDate = new Date(Number(year), Number(month), 0)
    const endDate = endDateDate.toISOString().slice(0, 10)
    const response = await rfidKeyService.getCalendarEvents({
      start_date: startDate,
      end_date: endDate
    })
    calendarEvents.value = response.events || []
  } catch (err) {
    showToast(err.response?.data?.message || 'Nem sikerült betölteni az RFID kulcs naptárat', 'error')
    calendarEvents.value = []
  } finally {
    calendarLoading.value = false
  }
}

const openCreateModal = () => {
  editingKey.value = null
  resetForm()
  showModal.value = true
}

const handleEdit = (key) => {
  editingKey.value = key
  form.value = {
    uid: key.uid,
    type: key.type || 'guest',
    name: key.name || '',
    status: key.isUsed ? 'assigned' : 'available'
  }
  showModal.value = true
}

const handleDelete = (key) => {
  keyToDelete.value = key
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  if (!keyToDelete.value) return

  try {
    await rfidKeyService.deleteKey(keyToDelete.value.id)
    showToast('RFID key deleted successfully', 'success')
    await loadKeys()
  } catch (err) {
    showToast(err.response?.data?.error || 'Failed to delete RFID key', 'error')
  } finally {
    keyToDelete.value = null
  }
}

const openAssignModal = async (key) => {
  keyToAssign.value = key
  assignError.value = ''
  resetAssignForm()

  if ((key.type || 'guest') !== 'crew') {
    showToast('Csak személyzeti kártyák rendelhetők kézzel szobákhoz', 'warning')
    return
  }

  if (!selectedHotelId.value) {
    showToast('Kérjük, először válasszon szállodát', 'warning')
    return
  }

  if (!hotelRooms.value.length) {
    await loadHotelRooms()
  }

  // Load existing manual assignments for this crew key
  try {
    crewAssignmentsLoading.value = true
    const data = await rfidKeyService.getManualAssignments(key.id)
    crewAssignments.value = data.assignments || []
  } catch (err) {
    console.error('Failed to load manual assignments for key', err)
    crewAssignments.value = []
  } finally {
    crewAssignmentsLoading.value = false
  }

  showAssignModal.value = true
}

const confirmAssign = async () => {
  if (!keyToAssign.value || !assignForm.value.room_ids.length || !assignForm.value.start_date) return

  assigning.value = true
  assignError.value = ''

  try {
    // Ha a kártya már használatban van, először feloldjuk a meglévő hozzárendeléseket,
    // majd az új beállításokkal újra hozzárendeljük.
    if (keyToAssign.value.isUsed) {
      await rfidKeyService.releaseKey(keyToAssign.value.id)
    }

    // Név módosítása, ha crew kártya és van megadott név
    if (keyToAssign.value.type === 'crew' && typeof keyToAssign.value.name !== 'undefined') {
      await rfidKeyService.updateKey(keyToAssign.value.id, {
        name: keyToAssign.value.name
      })
    }

    // Új hozzárendelések létrehozása (szobák + dátumok)
    await rfidKeyService.assignKeyToRoom(keyToAssign.value.id, {
      room_ids: assignForm.value.room_ids,
      start_date: assignForm.value.start_date,
      end_date: assignForm.value.lifetime ? null : assignForm.value.end_date,
      lifetime: assignForm.value.lifetime
    })

    showToast('Hozzárendelés sikeresen frissítve', 'success')

    // Frissítjük a táblázatot és a naptárat
    await loadKeys()
    await loadCalendar()

    closeAssignModal()
  } catch (err) {
    assignError.value =
      err.response?.data?.error || err.response?.data?.message || 'A hozzárendelés frissítése sikertelen'
  } finally {
    assigning.value = false
  }
}

const handleRelease = (key) => {
  keyToRelease.value = key
  showReleaseDialog.value = true
}

const confirmRelease = async () => {
  if (!keyToRelease.value) return

  try {
    await rfidKeyService.releaseKey(keyToRelease.value.id)
    showToast('RFID key released successfully', 'success')
    await loadKeys()
  } catch (err) {
    showToast(err.response?.data?.error || 'Failed to release RFID key', 'error')
  } finally {
    keyToRelease.value = null
  }
}

const handleSubmit = async () => {
  if (!editingKey.value && !form.value.hotelId) {
    showToast('Please select a hotel', 'warning')
    return
  }

  saving.value = true
  error.value = ''

  try {
    if (editingKey.value) {
      const updateData = {
        uid: form.value.uid,
        type: form.value.type
      }
      if (form.value.name !== undefined) {
        updateData.name = form.value.name
      }
      const newStatus = form.value.status
      const currentStatus = editingKey.value.isUsed ? 'assigned' : 'available'
      if (newStatus !== currentStatus) {
        updateData.status = newStatus
      }
      await rfidKeyService.updateKey(editingKey.value.id, updateData)
      showToast('RFID key updated successfully', 'success')
    } else {
      await rfidKeyService.createKey({
        uid: form.value.uid,
        type: form.value.type,
        name: form.value.name || null,
        hotel_id: form.value.hotelId
      })
      showToast('RFID key created successfully', 'success')
    }
    closeModal()
    await loadKeys()
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'Failed to save RFID key'
    showToast(error.value, 'error')
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingKey.value = null
  resetForm()
  error.value = ''
}

// Lock body scroll when modal is open
useBodyScrollLock([showModal, showAssignModal])

const closeAssignModal = () => {
  showAssignModal.value = false
  keyToAssign.value = null
  resetAssignForm()
  assignError.value = ''
  crewAssignments.value = []
}

const resetForm = () => {
  form.value = {
    hotelId: null,
    uid: '',
    status: 'available'
  }
}

const resetAssignForm = () => {
  assignForm.value = {
    room_ids: [],
    start_date: '',
    end_date: '',
    lifetime: false
  }
  selectedBookingRooms.value = []
  assignError.value = ''
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString()
}

const handleHotelsUpdated = async () => {
  await loadHotels()
  if (selectedHotelId.value) {
    await loadCalendar()
  }
}

onMounted(async () => {
  try {
    await loadHotels()
  } catch (err) {
    console.error('Error loading RFID keys page:', err)
  }
  window.addEventListener('hotels-updated', handleHotelsUpdated)
})

onUnmounted(() => {
  window.removeEventListener('hotels-updated', handleHotelsUpdated)
})
</script>

<style scoped>
.rfid-keys-page {
  max-width: 1400px;
  padding: 1.5rem 1.25rem 2rem;
}

/* Shared compact hotel selector + carousel styles (aligned with bookings list) */
.hotel-selector-compact {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  background: white;
  border-radius: 10px;
  border: 1px solid #e9ecef;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
  max-width: 1400px;
  margin-left: auto;
  margin-right: auto;
  gap: 1rem;
}

.hotel-selector-compact.header-compact {
  max-width: 420px;
  margin: 0 0 2rem 0;
  flex-shrink: 0;
  padding: 0.625rem 0.875rem;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.hotel-compact-info {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  flex: 1;
  min-width: 0;
}

.hotel-compact-icon {
  font-size: 2rem;
  flex-shrink: 0;
  line-height: 1;
}

.hotel-compact-details {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  min-width: 0;
  flex: 1;
}

.hotel-compact-name {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
  line-height: 1.3;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.hotel-compact-location {
  font-size: 0.85rem;
  color: #6c757d;
  margin: 0;
  line-height: 1.3;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.hotel-change-btn {
  padding: 0.625rem 1.125rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  white-space: nowrap;
  flex-shrink: 0;
}

.hotel-change-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 10px rgba(102, 126, 234, 0.35);
}

.hotel-change-btn svg {
  width: 16px;
  height: 16px;
}

.hotel-carousel-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.hotel-carousel-container-minimal {
  width: 100%;
  max-width: 500px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.hotel-carousel-header-minimal {
  padding: 1rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.carousel-title-minimal {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
}

.carousel-close-btn-minimal {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  font-size: 1.5rem;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  font-weight: 600;
}

.carousel-close-btn-minimal:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

.hotel-carousel-wrapper-minimal {
  position: relative;
  display: flex;
  align-items: center;
  padding: 1.5rem 0;
  background: #f8f9fa;
  min-height: 280px;
}

.hotel-carousel-minimal {
  flex: 1;
  overflow: hidden;
  position: relative;
}

.hotel-card-carousel-minimal {
  display: flex;
  transition: transform 0.4s ease;
}

.hotel-card-item-minimal {
  min-width: 100%;
  padding: 0 1.5rem;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.hotel-card-item-minimal.selected .hotel-select-btn-minimal {
  background: #10b981;
}

.hotel-card-image-minimal {
  width: 100%;
  max-width: 360px;
  height: 180px;
  border-radius: 12px;
  overflow: hidden;
  background: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
}

.hotel-cover-image-minimal {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hotel-image-placeholder-minimal {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  color: #adb5bd;
  font-size: 2.5rem;
}

.hotel-card-content-minimal {
  text-align: center;
}

.hotel-card-name-minimal {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 0.25rem;
  color: #2c3e50;
}

.hotel-card-location-minimal {
  font-size: 0.9rem;
  color: #6c757d;
  margin: 0 0 0.75rem;
}

.hotel-select-btn-minimal {
  padding: 0.6rem 1.4rem;
  border-radius: 999px;
  border: none;
  background: #667eea;
  color: #fff;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.hotel-select-btn-minimal:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.35);
}

.carousel-nav-btn-modern {
  width: 44px;
  height: 44px;
  border-radius: 999px;
  border: none;
  background: rgba(15, 23, 42, 0.85);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.4);
}

.carousel-nav-btn-modern:hover {
  transform: translateY(-1px);
  background: rgba(15, 23, 42, 0.95);
}

.carousel-prev-modern {
  margin-left: 1rem;
}

.carousel-next-modern {
  margin-right: 1rem;
}

.carousel-indicators-minimal {
  display: flex;
  justify-content: center;
  gap: 0.4rem;
  padding: 0.75rem 1rem 1.25rem;
  background: #ffffff;
}

.carousel-indicator-minimal {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  border: none;
  background: #d1d5db;
  cursor: pointer;
  transition: all 0.2s ease;
}

.carousel-indicator-minimal.active {
  width: 18px;
  background: #4f46e5;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
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

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.hotel-selector {
  margin-bottom: 2rem;
  padding: 1.5rem;
}

.hotel-selector h3 {
  margin: 0 0 1rem 0;
  font-size: 1.1rem;
  color: #2c3e50;
}

.hotel-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.95rem;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status-available {
  background-color: #d4edda;
  color: #155724;
}

.status-assigned {
  background-color: #cce5ff;
  color: #004085;
}

/* Calendar layout */
.calendar-section {
  margin-bottom: 2rem;
  border-radius: 24px;
  box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
  border: none;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.calendar-header h2 {
  margin: 0;
  font-size: 1.6rem;
  font-weight: 700;
}

.calendar-subtitle {
  margin: 0.25rem 0 0;
  font-size: 0.9rem;
  color: #6b7280;
}

.calendar-controls label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}

.calendar-controls input[type="month"] {
  padding: 0.35rem 0.5rem;
  border-radius: 6px;
  border: 1px solid #d1d5db;
}

.calendar-loading,
.calendar-empty {
  padding: 1.5rem;
  text-align: center;
  color: #6b7280;
}

.calendar-scroll-wrapper {
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.calendar-grid {
  min-width: 800px;
  border-radius: 18px;
  overflow: hidden;
  background: #ffffff;
}

.calendar-row {
  display: grid;
  grid-auto-columns: minmax(60px, 1fr);
  grid-auto-flow: column;
}

.calendar-row-header {
  position: sticky;
  top: 0;
  z-index: 1;
  background: #f9fafb;
}

.calendar-row-header .cell {
  border-bottom: 1px solid #e5e7eb;
}

.cell {
  border-right: 1px solid #e5e7eb;
  border-bottom: 1px solid #e5e7eb;
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.key-header {
  min-width: 160px;
  font-weight: 600;
  background: linear-gradient(135deg, #eef2ff 0%, #fdf2ff 100%);
}

.key-cell {
  min-width: 160px;
  justify-content: flex-start;
  padding-left: 0.75rem;
  background: #faf5ff;
}

.key-uid {
  font-family: monospace;
  font-size: 0.9rem;
}

.day-header {
  flex-direction: column;
  padding: 0.4rem 0;
  font-size: 0.75rem;
  background: #f9fafb;
}

.day-number {
  font-weight: 600;
  color: #111827;
}

.day-label {
  color: #6b7280;
}

.day-cell {
  padding: 0;
}

.month-day {
  align-items: flex-start;
  justify-content: flex-start;
  padding: 4px 4px 8px;
  position: relative;
}

.outside-month {
  color: #d1d5db;
  background: #f9fafb;
}

.day-top {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  font-size: 0.75rem;
  margin-bottom: 4px;
}

.day-events {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 4px;
  align-items: stretch;
}

.event-bar {
  width: 100%;
  height: 12px;
  border-radius: 999px;
  opacity: 0.9;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
  cursor: pointer;
}

.event-bar-start {
  border-top-left-radius: 999px;
  border-bottom-left-radius: 999px;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

.event-bar-end {
  border-top-right-radius: 999px;
  border-bottom-right-radius: 999px;
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}

.event-bar-single {
  border-radius: 999px;
}

.event-bar-middle {
  border-radius: 0;
}

.calendar-legend {
  margin-top: 0.75rem;
  font-size: 0.85rem;
  color: #4b5563;
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem 1.25rem;
  align-items: center;
}

.legend-title {
  font-weight: 600;
}

.legend-item {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 999px;
  background: #6366f1;
}

.legend-label {
  font-family: monospace;
  font-size: 0.8rem;
}

.legend-help {
  color: #6b7280;
}

.booking-info {
  font-size: 0.9rem;
}

.booking-info div {
  margin-bottom: 0.25rem;
}

.assigned-date {
  color: #7f8c8d;
  font-size: 0.85rem;
}

.text-muted {
  color: #7f8c8d;
}

.btn-icon {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.btn-icon:hover {
  background-color: #f0f0f0;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow: auto;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #2c3e50;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: #7f8c8d;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.modal-close:hover {
  background-color: #ecf0f1;
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #2c3e50;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
}

.form-group input:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.form-hint {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.85rem;
  color: #7f8c8d;
}

.error-message {
  background-color: #f8d7da;
  color: #721c24;
  padding: 0.75rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.info-box {
  background-color: #e8f4f8;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.info-box p {
  margin: 0;
  color: #2c3e50;
  font-size: 0.9rem;
}

.key-info {
  background-color: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.key-info p {
  margin: 0.5rem 0;
}

.form-row-booking {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.checkbox-help {
  font-size: 0.8rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

.keys-filter-bar {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 0.75rem;
  gap: 0.5rem;
  align-items: center;
}

.filter-label {
  font-size: 0.85rem;
  color: #4b5563;
  margin-right: 0.4rem;
}

.filter-select {
  padding: 0.4rem 0.75rem;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  font-size: 0.85rem;
}

.room-multiselect {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.room-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.35rem 0.6rem;
  border-radius: 999px;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  font-size: 0.8rem;
  cursor: pointer;
}

.room-chip input {
  margin: 0;
}

.existing-assignments {
  margin-bottom: 1.5rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
}

.existing-assignments .section-title {
  margin: 0 0 0.5rem 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: #111827;
}

.assignments-loading,
.assignments-empty {
  font-size: 0.85rem;
  color: #6b7280;
}

.assignments-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.assignment-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
}

.assignment-main {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.assignment-room {
  font-size: 0.9rem;
  font-weight: 600;
  color: #111827;
}

.assignment-dates {
  font-size: 0.8rem;
  color: #6b7280;
}

.btn-small-danger {
  padding: 0.25rem 0.6rem;
  font-size: 0.75rem;
  border-radius: 999px;
  border: none;
  background: #ef4444;
  color: #fff;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.btn-small-danger:hover {
  background: #b91c1c;
}

.modal-footer {
  padding-top: 1.5rem;
  border-top: 1px solid #e0e0e0;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background-color: #ecf0f1;
  color: #2c3e50;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-secondary:hover {
  background-color: #d5dbdb;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active .modal-content,
.modal-leave-active .modal-content {
  transition: transform 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
  transform: scale(0.9);
}
</style>
