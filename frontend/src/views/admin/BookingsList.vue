<template>
  <div class="admin-bookings-page">
    <div class="page-header">
      <div class="page-header-top">
        <router-link to="/admin" class="back-to-dashboard-btn">
          <span class="back-icon">←</span>
          <span>Vissza az irányítópulthoz</span>
        </router-link>
      </div>
      <div class="page-header-main">
        <div class="page-header-text">
          <h1>Szálloda foglalások</h1>
          <p class="page-subtitle">Foglalások kezelése a szállodájához</p>
        </div>

        <!-- Compact Hotel Selector (Header) -->
        <div v-if="selectedHotel && !hotelLoading" class="hotel-selector-compact header-compact">
          <div class="hotel-compact-info">
            <div class="hotel-compact-icon">🏨</div>
            <div class="hotel-compact-details">
              <div class="hotel-compact-name">{{ selectedHotel.name || `Szálloda #${selectedHotel.id}` }}</div>
              <div class="hotel-compact-location">📍 {{ selectedHotel.location || 'Helyszín nincs megadva' }}</div>
            </div>
          </div>
          <button 
            v-if="hotels.length > 1"
            @click="openCarousel" 
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
      </div>
    </div>

    <!-- Loading States -->
    <div v-if="loading || hotelLoading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>{{ hotelLoading ? 'Szálloda információk betöltése...' : 'Foglalások betöltése...' }}</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="error-message">{{ error }}</div>
    
    <!-- Success Message -->
    <div v-if="successMessage" class="success-message">{{ successMessage }}</div>

    <!-- Minimal Hotel Selector Carousel -->
    <Transition name="fade">
      <div v-if="hotels.length > 1 && showHotelCarousel && !hotelLoading" class="hotel-carousel-overlay" @click.self="closeCarousel">
        <div class="hotel-carousel-container-minimal">
          <div class="hotel-carousel-header-minimal">
            <h3 class="carousel-title-minimal">🏨 Szálloda kiválasztása</h3>
            <button @click="closeCarousel" class="carousel-close-btn-minimal">×</button>
          </div>
          
          <div class="hotel-carousel-wrapper-minimal">
            <button 
              @click="previousHotel" 
              class="carousel-nav-btn-modern carousel-prev-modern"
              title="Előző szálloda"
            >
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 18L9 12L15 6" stroke="#667eea" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
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
                  @click="selectHotel(hotel.id)"
                  :class="{ 'selected': selectedHotelId === hotel.id }"
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
                    <h4 class="hotel-card-name-minimal">{{ hotel.name || `Szálloda #${hotel.id}` }}</h4>
                    <p class="hotel-card-location-minimal">📍 {{ hotel.location || 'Helyszín nincs megadva' }}</p>
                    <button class="hotel-select-btn-minimal">
                      {{ selectedHotelId === hotel.id ? '✓ Kiválasztva' : 'Kiválasztás' }}
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
                <path d="M9 18L15 12L9 6" stroke="#667eea" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
          
          <div class="carousel-indicators-minimal">
            <button
              v-for="(hotel, index) in hotels"
              :key="hotel.id"
              @click="goToHotel(index)"
              class="carousel-indicator-minimal"
              :class="{ 'active': currentHotelIndex === index }"
            ></button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Hotel Info Card -->
    <div v-if="selectedHotel && !hotelLoading" class="hotel-info-card">
      <div class="hotel-info-content">
        <div class="hotel-icon">🏨</div>
        <div class="hotel-details">
          <h2>{{ selectedHotel.name || selectedHotel.location || 'Szálloda' }}</h2>
          <p class="hotel-location">📍 {{ selectedHotel.location }}</p>
        </div>
      </div>
    </div>

    <!-- Empty State (No bookings at all) -->
    <div v-if="bookings.length === 0 && !loading && !hotelLoading && !(hotels.length > 1 && showHotelCarousel)" class="empty-state">
      <div class="empty-icon">📋</div>
      <h2>Nem található foglalás</h2>
      <p v-if="selectedHotelId">Még nem készült foglalás ehhez a szállodához.</p>
      <p v-else-if="hotels.length > 1 && !selectedHotelId">Kérjük, válasszon egy szállodát a foglalások megtekintéséhez.</p>
      <p v-else>Még nem készült foglalás a szállodá(i)hoz.</p>
    </div>

    <!-- Bookings List -->
    <div v-if="bookings.length > 0" class="bookings-container">
      <!-- Filter Tabs -->
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
        <button
          @click="activeFilter = 'archived'"
          :class="['filter-btn', { active: activeFilter === 'archived' }]"
        >
          <span class="filter-icon">📦</span>
          <span>Archivált</span>
          <span class="filter-count">({{ archivedCount }})</span>
        </button>
      </div>

      

      

      <!-- Empty State for Filtered Results -->
      <div v-if="filteredBookings.length === 0 && !loading && !hotelLoading" class="empty-state">
        <div class="empty-icon">🔍</div>
        <h2>Nincs foglalás ezzel a szűrővel</h2>
        <p>Nincs találat a kiválasztott szűrőre.</p>
      </div>

      <!-- Table View (Minimal) -->
      <div v-if="filteredBookings.length > 0 && viewMode === 'table'" class="bookings-table-container">
        <table class="bookings-table minimal-table">
          <thead>
            <tr>
              <th>Foglalás ID</th>
              <th>Vendég</th>
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
              <td class="guest-cell">
                <div v-if="booking.user" class="guest-info-inline">
                  <div class="guest-name-inline">{{ booking.user.name }}</div>
                  <div class="guest-email-inline">{{ booking.user.email }}</div>
                </div>
                <span v-else>-</span>
              </td>
              <td>{{ formatDate(booking.startDate) }}</td>
              <td>{{ formatDate(booking.endDate) }}</td>
              <td>{{ calculateNights(booking.startDate, booking.endDate) }} éjszaka</td>
              <td class="price-cell">{{ booking.totalPrice || 'Nincs adat' }} €</td>
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
          :class="`status-${booking.status}`"
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
              <span class="detail-value price">{{ booking.totalPrice || 'Nincs adat' }} €</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Éjszakák</span>
              <span class="detail-value">{{ calculateNights(booking.startDate, booking.endDate) }} éjszaka</span>
            </div>
          </div>

          <!-- Payment Section (Admin) -->
          <div v-if="booking.status === 'confirmed'" class="invoice-section">
            <h4 class="section-title">Fizetés</h4>
            <div class="invoice-info">
              <div class="invoice-status">
                <span
                  class="invoice-status-badge"
                  :class="`invoice-${booking.payment?.status || 'draft'}`"
                >
                  {{ booking.payment?.status === 'paid' ? 'Fizetve' : 'Függőben (banki átutalás)' }}
                </span>
              </div>
              <div class="invoice-actions">
                <button
                  v-if="booking.payment?.status !== 'paid'"
                  @click="confirmPayment(booking.id)"
                  class="btn-invoice-approve"
                  :disabled="paymentLoading === booking.id"
                >
                  {{ paymentLoading === booking.id ? 'Megerősítés...' : '✓ Fizetés megerősítése' }}
                </button>
                <button v-else class="btn-invoice-sent" disabled>✅ Fizetés megerősítve</button>
              </div>
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

          <!-- Guests Section -->
          <!-- Guests Section (Admin can manage) -->
          <div class="booking-guests-section">
            <div class="guests-header">
              <h4 class="section-title">Regisztrált vendégek</h4>
              <button
                @click="openGuestModal(booking)"
                class="btn-add-guest"
                :disabled="booking.status === 'cancelled' || booking.checkInstatus === 'checkedOut' || isAtCapacity(booking)"
                :title="isAtCapacity(booking) ? 'Elérte a maximális vendégkapacitást' : 'Vendég hozzáadása'"
              >
                + Vendég hozzáadása
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
                  <span class="guest-id">Személyigazolvány: {{ guest.idNumber }}</span>
                  <span class="guest-dob" v-if="guest.dateOfBirth">
                    Szül. dátum: {{ formatDate(guest.dateOfBirth) }}
                  </span>
                </div>
                <div class="guest-actions">
                  <button
                    @click="openEditGuestModal(booking, guest)"
                    class="btn-edit-guest"
                    :disabled="booking.status === 'cancelled' || booking.checkInstatus === 'checkedOut'"
                    title="Vendég szerkesztése"
                  >
                    ✏️
                  </button>
                  <button
                    @click="deleteGuest(guest.id, booking.id)"
                    class="btn-delete-guest"
                    :disabled="booking.status === 'cancelled' || booking.checkInstatus === 'checkedOut'"
                    title="Vendég törlése"
                  >
                    🗑️
                  </button>
                </div>
              </div>
            </div>
            <div v-else class="no-guests">
              <p>Még nincs regisztrált vendég</p>
            </div>
          </div>

          <!-- Invoice Section (Admin) -->
          <div v-if="booking.status === 'confirmed'" class="invoice-section">
            <h4 class="section-title">Számla</h4>
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
                  ✏️ Szerkesztés
                </button>
                <button
                  v-if="booking.invoice.status === 'draft'"
                  @click="previewInvoice(booking.id)"
                  class="btn-invoice-preview"
                  :disabled="invoiceLoading === booking.id"
                >
                  📄 Előnézet
                </button>
                <button
                  v-if="booking.invoice.status === 'draft'"
                  @click="approveInvoice(booking.invoice.id, booking.id)"
                  class="btn-invoice-approve"
                  :disabled="invoiceLoading === booking.id"
                >
                  ✓ Jóváhagyás
                </button>
                <button
                  v-if="booking.invoice.status === 'approved'"
                  @click="sendInvoice(booking.invoice.id, booking.id)"
                  class="btn-invoice-send"
                  :disabled="invoiceLoading === booking.id"
                >
                  📧 Küldés vendégnek
                </button>
                <button
                  v-if="booking.invoice.status === 'sent'"
                  class="btn-invoice-sent"
                  disabled
                >
                  ✅ Elküldve
                </button>
              </div>
            </div>
            <div v-else class="no-invoice">
              <p>A számla a foglalás megerősítése után jön létre</p>
            </div>
          </div>

          <!-- Booking Actions (Admin) - Only show for active bookings -->
          <div v-if="booking.status !== 'cancelled' && booking.checkInstatus !== 'checkedOut'" class="booking-actions">
            <div v-if="booking.status === 'pending'" class="pending-actions">
              <button
                @click="updateBookingStatus(booking.id, 'confirmed')"
                class="btn-accept"
                :disabled="updating === booking.id"
              >
                {{ updating === booking.id ? 'Frissítés...' : '✓ Elfogadás' }}
              </button>
              <button
                @click="updateBookingStatus(booking.id, 'cancelled')"
                class="btn-reject"
                :disabled="updating === booking.id"
              >
                {{ updating === booking.id ? 'Frissítés...' : '✗ Elutasítás' }}
              </button>
            </div>
            <div v-else class="booking-status-actions">
              <button
                @click="openEditBookingModal(booking)"
                class="btn-edit-booking"
                :disabled="updating === booking.id"
              >
                ✏️ Foglalás szerkesztése
              </button>
              <div v-if="booking.status === 'confirmed'" class="confirmed-badge">
                ✅ Megerősítve - {{ booking.payment?.status === 'paid' ? 'Vendég bejelentkezhet' : 'Fizetésre vár (QR kód a fizetés után kerül elküldésre)' }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Super Advanced Edit Invoice Modal -->
    <Transition name="modal">
      <div v-if="showEditInvoiceModal" class="modal-overlay" @click.self="closeEditInvoiceModal">
        <div class="modal-content super-invoice-modal">
          <div class="modal-header">
            <h2>🧾 Fejlett számla szerkesztő</h2>
            <button @click="closeEditInvoiceModal" class="btn-close-modal">×</button>
          </div>
          
          <form @submit.prevent="saveInvoice" class="super-invoice-form">
            <!-- Invoice Status -->
            <div class="form-section">
              <h3 class="section-title">📊 Számla státusz</h3>
              <div class="form-group">
                <label>Státusz *</label>
                <select v-model="invoiceForm.status" required class="form-select">
                  <option value="draft">Vázlat</option>
                  <option value="approved">Jóváhagyva</option>
                  <option value="sent">Elküldve</option>
                </select>
                <small class="form-hint">Csak a vázlat számlák szerkeszthetők teljesen</small>
              </div>
            </div>

            <!-- Invoice Number -->
            <div class="form-section">
              <h3 class="section-title">🔢 Számla részletek</h3>
              <div class="form-group">
                <label>Számlaszám *</label>
                <input
                  v-model="invoiceForm.invoice_number"
                  type="text"
                  required
                  class="form-input"
                  placeholder="pl. EU2024/00001"
                />
                <small class="form-hint">Egyedi számla azonosító</small>
              </div>
            </div>

            <!-- Financial Details -->
            <div class="form-section">
              <h3 class="section-title">💰 Pénzügyi részletek</h3>
              <div class="form-row">
                <div class="form-group">
                  <label>Részösszeg (EUR) *</label>
                  <input
                    v-model.number="invoiceForm.subtotal"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="form-input"
                    @input="recalculateInvoice"
                  />
                </div>
                <div class="form-group">
                  <label>ÁFA kulcs (%) *</label>
                  <input
                    v-model.number="invoiceForm.tax_rate"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    required
                    class="form-input"
                    @input="recalculateInvoice"
                  />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>ÁFA összeg (EUR)</label>
                  <input
                    v-model.number="invoiceForm.tax_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="form-input"
                    @input="manualTaxAmount = true"
                  />
                  <small class="form-hint">Hagyja üresen az automatikus számításhoz</small>
                </div>
                <div class="form-group">
                  <label>Végösszeg (EUR)</label>
                  <input
                    v-model.number="invoiceForm.total_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="form-input"
                    @input="manualTotalAmount = true"
                  />
                  <small class="form-hint">Hagyja üresen az automatikus számításhoz</small>
                </div>
              </div>
            </div>

            <!-- Dates -->
            <div class="form-section">
              <h3 class="section-title">📅 Fontos dátumok</h3>
              <div class="form-row">
                <div class="form-group">
                  <label>Kibocsátás dátuma *</label>
                  <input
                    v-model="invoiceForm.issue_date"
                    type="date"
                    required
                    class="form-input"
                  />
                </div>
                <div class="form-group">
                  <label>Fizetési határidő *</label>
                  <input
                    v-model="invoiceForm.due_date"
                    type="date"
                    :min="invoiceForm.issue_date"
                    required
                    class="form-input"
                  />
                </div>
              </div>
              <div class="info-box">
                <span>Fizetési határidő: <strong>{{ formatDate(invoiceForm.due_date) }}</strong></span>
                <span v-if="invoiceForm.due_date" class="days-until">
                  ({{ calculateDaysUntil(invoiceForm.due_date) }} nap)
                </span>
              </div>
            </div>

            <!-- Invoice Summary -->
            <div class="form-section">
              <h3 class="section-title">📋 Számla összesítő</h3>
              <div class="invoice-summary-advanced">
                <div class="summary-row">
                  <span>Részösszeg:</span>
                  <strong>€{{ parseFloat(invoiceForm.subtotal || 0).toFixed(2) }}</strong>
                </div>
                <div class="summary-row">
                  <span>ÁFA ({{ invoiceForm.tax_rate }}%):</span>
                  <strong>€{{ parseFloat(invoiceForm.tax_amount || calculateTaxAmount).toFixed(2) }}</strong>
                </div>
                <div class="summary-row total-row">
                  <span>Végösszeg:</span>
                  <strong>€{{ parseFloat(invoiceForm.total_amount || calculateTotalAmount).toFixed(2) }}</strong>
                </div>
              </div>
            </div>

            <!-- Booking Reference -->
            <div class="form-section" v-if="editingInvoice">
              <h3 class="section-title">🔗 Foglalás hivatkozás</h3>
              <div class="info-box">
                <div class="info-row">
                  <span class="info-label">Foglalás azonosító:</span>
                  <span class="info-value">#{{ editingInvoice.booking_id }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Vendég:</span>
                  <span class="info-value">{{ editingInvoice.booking?.user?.name || 'Nincs adat' }}</span>
                </div>
              </div>
            </div>

            <div class="modal-actions">
              <button type="button" @click="closeEditInvoiceModal" class="btn-cancel">
                Mégse
              </button>
              <button type="submit" class="btn-save" :disabled="savingInvoice">
                {{ savingInvoice ? 'Mentés...' : '💾 Számla mentése' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Advanced Edit Booking Modal -->
    <Transition name="modal">
      <div v-if="showEditBookingModal" class="modal-overlay" @click.self="closeEditBookingModal">
        <div class="modal-content advanced-booking-modal">
          <div class="modal-header">
            <h2>📋 Fejlett foglalás szerkesztő</h2>
            <button @click="closeEditBookingModal" class="btn-close-modal">×</button>
          </div>
          
          <form @submit.prevent="saveBooking" class="advanced-booking-form">
            <!-- Guest Information Section -->
            <div class="form-section">
              <h3 class="section-title">👤 Vendég információk</h3>
              <div v-if="editingBooking?.user" class="guest-info-display">
                <div class="info-row">
                  <span class="info-label">Név:</span>
                  <span class="info-value">{{ editingBooking.user.name }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">E-mail:</span>
                  <span class="info-value">{{ editingBooking.user.email }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Fizetési státusz:</span>
                  <span class="info-value payment-status" :class="`payment-${editingBooking.payment?.status || 'pending'}`">
                    {{ editingBooking.payment?.status === 'paid' ? '✅ Fizetve' : '⏳ Függőben' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Booking Status -->
            <div class="form-section">
              <h3 class="section-title">📊 Foglalás státusz</h3>
              <div class="form-group">
                <label>Státusz *</label>
                <select v-model="bookingForm.status" required class="form-select">
                  <option value="pending">Függőben</option>
                  <option value="confirmed">Megerősítve</option>
                  <option value="cancelled">Törölve</option>
                  <option value="completed">Befejezve</option>
                </select>
              </div>
            </div>

            <!-- Date Range -->
            <div class="form-section">
              <h3 class="section-title">📅 Dátumtartam</h3>
              <div class="form-row">
                <div class="form-group">
                  <label>Bejelentkezés dátuma *</label>
                  <input
                    v-model="bookingForm.startDate"
                    type="date"
                    required
                    class="form-input"
                  />
                </div>
                <div class="form-group">
                  <label>Kijelentkezés dátuma *</label>
                  <input
                    v-model="bookingForm.endDate"
                    type="date"
                    :min="bookingForm.startDate"
                    required
                    class="form-input"
                  />
                </div>
              </div>
              <div class="info-box">
                <span>Éjszakák: <strong>{{ calculateNights(bookingForm.startDate, bookingForm.endDate) }}</strong></span>
              </div>
            </div>

            <!-- Rooms Management -->
            <div class="form-section">
              <h3 class="section-title">🛏️ Szobák</h3>
              <div v-if="availableRooms.length === 0" class="loading-text">Szobák betöltése...</div>
              <div v-else class="multi-select-container">
                <div class="selected-items">
                  <div
                    v-for="roomId in bookingForm.rooms"
                    :key="roomId"
                    class="selected-item"
                  >
                    <span>{{ getRoomName(roomId) }}</span>
                    <button
                      type="button"
                      @click="removeRoom(roomId)"
                      class="remove-btn"
                    >
                      ×
                    </button>
                  </div>
                </div>
                <select
                  v-model="selectedRoomToAdd"
                  @change="addRoom"
                  class="form-select"
                >
                  <option value="">Válasszon szobát a hozzáadáshoz...</option>
                  <option
                    v-for="room in availableRooms.filter(r => !bookingForm.rooms.includes(r.id))"
                    :key="room.id"
                    :value="room.id"
                  >
                    {{ room.name }} ({{ room.capacity }} vendég) - €{{ room.pricePerNight }}/éjszaka
                  </option>
                </select>
              </div>
            </div>

            <!-- Services Management -->
            <div class="form-section">
              <h3 class="section-title">✨ Szolgáltatások</h3>
              <div v-if="availableServices.length === 0" class="loading-text">Szolgáltatások betöltése...</div>
              <div v-else class="multi-select-container">
                <div class="selected-items">
                  <div
                    v-for="serviceId in bookingForm.services"
                    :key="serviceId"
                    class="selected-item"
                  >
                    <span>{{ getServiceName(serviceId) }} (€{{ getServicePrice(serviceId) }})</span>
                    <button
                      type="button"
                      @click="removeService(serviceId)"
                      class="remove-btn"
                    >
                      ×
                    </button>
                  </div>
                </div>
                <select
                  v-model="selectedServiceToAdd"
                  @change="addService"
                  class="form-select"
                >
                  <option value="">Válasszon szolgáltatást a hozzáadáshoz...</option>
                  <option
                    v-for="service in availableServices.filter(s => !bookingForm.services.includes(s.id))"
                    :key="service.id"
                    :value="service.id"
                  >
                    {{ service.name }} - €{{ service.price }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Pricing -->
            <div class="form-section">
              <h3 class="section-title">💰 Árazás</h3>
              <div class="form-group">
                <label>Összesen (EUR) *</label>
                <input
                  v-model.number="bookingForm.totalPrice"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="form-input"
                />
              </div>
              <div class="info-box">
                <div class="price-breakdown">
                  <div>Szobák: <strong>€{{ calculateRoomsPrice }}</strong></div>
                  <div>Szolgáltatások: <strong>€{{ calculateServicesPrice }}</strong></div>
                  <div class="total-price">Összesen: <strong>€{{ bookingForm.totalPrice }}</strong></div>
                </div>
              </div>
            </div>

            <div class="modal-actions">
              <button type="button" @click="closeEditBookingModal" class="btn-cancel">
                Mégse
              </button>
              <button type="submit" class="btn-save" :disabled="savingBooking">
                {{ savingBooking ? 'Mentés...' : '💾 Változások mentése' }}
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
            <h2>{{ editingGuest ? 'Vendég szerkesztése' : 'Vendég hozzáadása' }}</h2>
            <button @click="closeGuestModal" class="btn-close-modal">×</button>
          </div>
          <form @submit.prevent="saveGuest" class="guest-form">
            <div class="form-group">
              <label>Teljes név *</label>
              <input
                v-model="guestForm.name"
                type="text"
                required
                placeholder="Adja meg a vendég teljes nevét"
              />
            </div>
            <div class="form-group">
              <label>Személyigazolvány szám *</label>
              <input
                v-model="guestForm.idNumber"
                type="text"
                required
                placeholder="Adja meg a személyigazolvány/útlevél számát"
              />
            </div>
            <div class="form-group">
              <label>Születési dátum *</label>
              <input
                v-model="guestForm.dateOfBirth"
                type="date"
                required
                :max="maxDate"
              />
            </div>
            <div class="modal-actions">
              <button type="button" @click="closeGuestModal" class="btn-cancel">
                Mégse
              </button>
              <button type="submit" class="btn-save" :disabled="savingGuest">
                {{ savingGuest ? 'Mentés...' : (editingGuest ? 'Vendég frissítése' : 'Vendég hozzáadása') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Cancellation Message Modal -->
    <Transition name="modal">
      <div v-if="showCancellationModal" class="modal-overlay" @click.self="closeCancellationModal">
        <div class="modal-content cancellation-modal">
          <div class="modal-header">
            <h2>⚠️ Foglalás törlése</h2>
            <button @click="closeCancellationModal" class="btn-close-modal">×</button>
          </div>
          <div class="cancellation-warning">
            <p><strong>Figyelem!</strong> Ez a művelet törli a foglalást és értesítést küld a vendégnek.</p>
          </div>
          <form @submit.prevent="confirmCancellation" class="cancellation-form">
            <div class="form-group">
              <label>Üzenet a vendégnek (opcionális)</label>
              <textarea
                v-model="cancellationMessage"
                rows="5"
                placeholder="Írjon egy üzenetet a vendégnek, amelyet az e-mailben kapni fog. Például: 'Sajnáljuk, de a foglalását törölnünk kellett a következő okok miatt...'"
                class="form-textarea"
                maxlength="1000"
              ></textarea>
              <small class="form-hint">{{ cancellationMessage.length }}/1000 karakter</small>
            </div>
            <div class="modal-actions">
              <button type="button" @click="closeCancellationModal" class="btn-cancel">
                Mégse
              </button>
              <button type="submit" class="btn-save btn-danger" :disabled="cancelling">
                {{ cancelling ? 'Törlés...' : '✓ Foglalás törlése' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Booking Actions Modal -->
    <Transition name="modal">
      <div v-if="showBookingActionsModal && selectedBookingForActions" class="modal-overlay" @click.self="closeBookingActionsModal">
        <div class="modal-content actions-modal">
          <div class="modal-header">
            <h2>⚙️ Foglalás műveletek - #{{ selectedBookingForActions.id }}</h2>
            <button @click="closeBookingActionsModal" class="btn-close-modal">×</button>
          </div>
          <div class="actions-content">
            <!-- Booking Info -->
            <div class="booking-info-summary">
              <div class="info-row">
                <span class="info-label">Vendég:</span>
                <span class="info-value">{{ selectedBookingForActions.user?.name || 'N/A' }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Státusz:</span>
                <span :class="['status-badge', selectedBookingForActions.checkInstatus === 'checkedOut' ? 'badge-finished' : `badge-${selectedBookingForActions.status}`]">
                  {{ selectedBookingForActions.checkInstatus === 'checkedOut' ? 'Befejezve' : formatStatus(selectedBookingForActions.status) }}
                </span>
              </div>
              <div class="info-row">
                <span class="info-label">Dátum:</span>
                <span class="info-value">{{ formatDate(selectedBookingForActions.startDate) }} - {{ formatDate(selectedBookingForActions.endDate) }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Összeg:</span>
                <span class="info-value price">{{ selectedBookingForActions.totalPrice || 'Nincs adat' }} €</span>
              </div>
            </div>

            <!-- Actions List -->
            <div class="actions-list">
              <!-- Status Actions -->
              <div v-if="selectedBookingForActions.status === 'pending'" class="action-group">
                <h3 class="action-group-title">Státusz műveletek</h3>
                <button
                  @click="handleAction('accept')"
                  class="action-btn btn-accept"
                  :disabled="updating === selectedBookingForActions.id"
                >
                  ✓ Foglalás elfogadása
                </button>
                <button
                  @click="handleAction('reject')"
                  class="action-btn btn-reject"
                  :disabled="updating === selectedBookingForActions.id"
                >
                  ✗ Foglalás elutasítása
                </button>
              </div>

              <!-- Booking Management -->
              <div v-if="selectedBookingForActions.status !== 'cancelled' && selectedBookingForActions.checkInstatus !== 'checkedOut'" class="action-group">
                <h3 class="action-group-title">Foglalás kezelése</h3>
                <button
                  @click="handleAction('edit')"
                  class="action-btn btn-edit"
                  :disabled="updating === selectedBookingForActions.id"
                >
                  ✏️ Foglalás szerkesztése
                </button>
                <button
                  @click="handleAction('cancel')"
                  class="action-btn btn-danger"
                  :disabled="updating === selectedBookingForActions.id"
                >
                  ❌ Foglalás törlése
                </button>
              </div>

              <!-- Guest Management -->
              <div v-if="selectedBookingForActions.status !== 'cancelled' && selectedBookingForActions.checkInstatus !== 'checkedOut'" class="action-group">
                <h3 class="action-group-title">Vendégkezelés</h3>
                <button
                  @click="handleAction('addGuest')"
                  class="action-btn btn-primary"
                  :disabled="isAtCapacity(selectedBookingForActions)"
                  :title="isAtCapacity(selectedBookingForActions) ? 'Elérte a maximális vendégkapacitást' : ''"
                >
                  + Vendég hozzáadása
                </button>
                <div v-if="selectedBookingForActions.guests && selectedBookingForActions.guests.length > 0" class="guests-list-actions">
                  <div
                    v-for="guest in selectedBookingForActions.guests"
                    :key="guest.id"
                    class="guest-action-item"
                  >
                    <span class="guest-name">{{ guest.name }}</span>
                    <div class="guest-item-actions">
                      <button
                        @click="handleAction('editGuest', guest)"
                        class="btn-icon btn-edit-icon"
                        title="Vendég szerkesztése"
                      >
                        ✏️
                      </button>
                      <button
                        @click="handleAction('deleteGuest', guest)"
                        class="btn-icon btn-delete-icon"
                        title="Vendég törlése"
                      >
                        🗑️
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Invoice Management -->
              <div v-if="selectedBookingForActions.status === 'confirmed' && selectedBookingForActions.invoice" class="action-group">
                <h3 class="action-group-title">Számla műveletek</h3>
                <button
                  v-if="selectedBookingForActions.invoice.status === 'draft'"
                  @click="handleAction('editInvoice')"
                  class="action-btn btn-edit"
                  :disabled="invoiceLoading === selectedBookingForActions.id"
                >
                  ✏️ Számla szerkesztése
                </button>
                <button
                  v-if="selectedBookingForActions.invoice.status === 'draft'"
                  @click="handleAction('previewInvoice')"
                  class="action-btn btn-primary"
                  :disabled="invoiceLoading === selectedBookingForActions.id"
                >
                  📄 Számla előnézet
                </button>
                <button
                  v-if="selectedBookingForActions.invoice.status === 'draft'"
                  @click="handleAction('approveInvoice')"
                  class="action-btn btn-accept"
                  :disabled="invoiceLoading === selectedBookingForActions.id"
                >
                  ✓ Számla jóváhagyása
                </button>
                <button
                  v-if="selectedBookingForActions.invoice.status === 'approved'"
                  @click="handleAction('sendInvoice')"
                  class="action-btn btn-primary"
                  :disabled="invoiceLoading === selectedBookingForActions.id"
                >
                  📧 Számla küldése vendégnek
                </button>
                <button
                  v-if="selectedBookingForActions.invoice.status === 'sent'"
                  @click="handleAction('downloadInvoice')"
                  class="action-btn btn-primary"
                >
                  📥 Számla letöltése
                </button>
              </div>

              <!-- Payment Management -->
              <div v-if="selectedBookingForActions.status === 'confirmed' && selectedBookingForActions.payment" class="action-group">
                <h3 class="action-group-title">Fizetés kezelése</h3>
                <button
                  v-if="selectedBookingForActions.payment.status !== 'paid'"
                  @click="handleAction('confirmPayment')"
                  class="action-btn btn-accept"
                  :disabled="paymentLoading === selectedBookingForActions.id"
                >
                  ✓ Fizetés megerősítése
                </button>
                <div v-else class="payment-confirmed">
                  ✅ Fizetés megerősítve
                </div>
              </div>
            </div>
          </div>
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
const hotels = ref([])
const selectedHotelId = ref('')
const currentHotelIndex = ref(0)
const showHotelCarousel = ref(false)
const selectedHotel = computed(() => {
  if (!selectedHotelId.value) return null
  return hotels.value.find(h => h.id === selectedHotelId.value)
})
const bookings = ref([])
const loading = ref(true)
const hotelLoading = ref(true)
const error = ref('')
const updating = ref(null)
const invoiceLoading = ref(null)
const paymentLoading = ref(null)
const successMessage = ref('')

// Invoice editing
const showEditInvoiceModal = ref(false)
const editingInvoice = ref(null)
const savingInvoice = ref(false)
const manualTaxAmount = ref(false)
const manualTotalAmount = ref(false)
const invoiceForm = ref({
  invoice_number: '',
  status: 'draft',
  subtotal: 0,
  tax_rate: 0,
  tax_amount: 0,
  total_amount: 0,
  issue_date: '',
  due_date: ''
})

// Booking editing
const showEditBookingModal = ref(false)
const editingBooking = ref(null)
const savingBooking = ref(false)
const availableRooms = ref([])
const availableServices = ref([])
const selectedRoomToAdd = ref('')
const selectedServiceToAdd = ref('')
const bookingForm = ref({
  startDate: '',
  endDate: '',
  totalPrice: 0,
  status: 'pending',
  rooms: [],
  services: []
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

// Cancellation modal
const showCancellationModal = ref(false)
const cancellationBookingId = ref(null)
const cancellationMessage = ref('')
const cancelling = ref(false)

// Booking actions modal
const showBookingActionsModal = ref(false)
const selectedBookingForActions = ref(null)

// Filter state
const activeFilter = ref('all')
const viewMode = ref('card') // 'card' or 'table'

// Computed properties for counts
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

const archivedCount = computed(() => {
  return bookings.value.filter(b => b.status === 'cancelled' || b.checkInstatus === 'checkedOut').length
})

// Filtered bookings based on active filter
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
  } else if (activeFilter.value === 'archived') {
    return bookings.value.filter(b => b.status === 'cancelled' || b.checkInstatus === 'checkedOut')
  }
  return bookings.value
})

onMounted(async () => {
  await loadHotels()
  // loadHotels now handles hotel selection and carousel display
  await loadBookings()
})

const loadHotels = async () => {
  if (!authStore.state.user) {
    error.value = 'Nincs bejelentkezve'
    hotelLoading.value = false
    return
  }

  try {
    const hotelsData = await hotelService.getHotels()
    hotels.value = hotelsData.filter(h => h.user_id === authStore.state.user.id)
    
    if (hotels.value.length === 0) {
      error.value = 'Nem található szálloda ehhez a felhasználóhoz'
      showHotelCarousel.value = false
    } else if (hotels.value.length === 1) {
      // Auto-select if only one hotel
      selectedHotelId.value = hotels.value[0].id
      showHotelCarousel.value = false
    } else if (hotels.value.length > 1) {
      // Show carousel if multiple hotels
      if (!selectedHotelId.value) {
        showHotelCarousel.value = true
      } else {
        showHotelCarousel.value = false
      }
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'A szálloda információk betöltése sikertelen'
    showHotelCarousel.value = false
  } finally {
    hotelLoading.value = false
  }
}

const handleHotelChange = async () => {
  await loadBookings()
}

// Hotel Carousel Functions
const openCarousel = () => {
  showHotelCarousel.value = true
  if (selectedHotelId.value) {
    const index = hotels.value.findIndex(h => h.id === selectedHotelId.value)
    if (index !== -1) {
      currentHotelIndex.value = index
    }
  }
}

const closeCarousel = () => {
  // Only allow closing if a hotel is already selected
  if (selectedHotelId.value) {
    showHotelCarousel.value = false
  }
  // Don't close if multiple hotels and none selected (required)
}

const selectHotel = async (hotelId) => {
  selectedHotelId.value = hotelId
  showHotelCarousel.value = false
  await loadBookings()
}

const nextHotel = () => {
  if (hotels.value.length === 0) return
  // Loop: if at last hotel, go to first
  if (currentHotelIndex.value >= hotels.value.length - 1) {
    currentHotelIndex.value = 0
  } else {
    currentHotelIndex.value++
  }
}

const previousHotel = () => {
  if (hotels.value.length === 0) return
  // Loop: if at first hotel, go to last
  if (currentHotelIndex.value === 0) {
    currentHotelIndex.value = hotels.value.length - 1
  } else {
    currentHotelIndex.value--
  }
}

const goToHotel = (index) => {
  currentHotelIndex.value = index
}

const getImageUrl = (imagePath) => {
  if (!imagePath) return null
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath
  }
  if (imagePath.startsWith('/storage/')) {
    const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000'
    return `${baseUrl}${imagePath}`
  }
  return imagePath
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
  const placeholder = event.target.nextElementSibling
  if (placeholder && placeholder.classList.contains('hotel-image-placeholder')) {
    placeholder.style.display = 'flex'
  }
}

const loadBookings = async () => {
  // If multiple hotels exist, hotel selection is mandatory
  if (hotels.value.length > 1 && !selectedHotelId.value) {
    bookings.value = []
    loading.value = false
    return
  }

  // If no hotel selected and only one hotel exists, show all bookings from all hotels
  if (!selectedHotelId.value) {
    loading.value = true
    error.value = ''
    try {
      const allBookings = []
      for (const hotel of hotels.value) {
        try {
          const data = await bookingService.getBookingsByHotelId(hotel.id)
          if (data && data.bookings && Array.isArray(data.bookings)) {
            allBookings.push(...data.bookings)
          }
        } catch (err) {
          console.error(`Failed to load bookings for hotel ${hotel.id}:`, err)
        }
      }
      
      // Process all bookings
      bookings.value = await Promise.all(allBookings.map(async (booking) => {
        return await processBooking(booking)
      }))
    } catch (err) {
      console.error('Failed to load bookings:', err)
      error.value = err.response?.data?.message || 'A foglalások betöltése sikertelen'
      bookings.value = []
    } finally {
      loading.value = false
    }
    return
  }

  // Load bookings for selected hotel
  loading.value = true
  error.value = ''
  try {
    const data = await bookingService.getBookingsByHotelId(selectedHotelId.value)
    
    if (data && data.bookings && Array.isArray(data.bookings)) {
      bookings.value = await Promise.all(data.bookings.map(async (booking) => {
        return await processBooking(booking)
      }))
    } else {
      bookings.value = []
    }
  } catch (err) {
    console.error('Failed to load bookings:', err)
    if (err.response?.status === 404) {
      bookings.value = []
    } else {
      error.value = err.response?.data?.message || 'A foglalások betöltése sikertelen'
      bookings.value = []
    }
  } finally {
    loading.value = false
  }
}

const processBooking = async (booking) => {
  // Rooms are already loaded via the relationship
  const rooms = booking.rooms ? booking.rooms.map(room => ({
    id: room.id,
    name: room.name,
    capacity: room.capacity || 'Nincs adat'
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
    invoice: invoice,
    payment: booking.payment || null
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('hu-HU', {
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

const updateBookingStatus = async (bookingId, status) => {
  // If cancelling, show the cancellation modal first
  if (status === 'cancelled') {
    cancellationBookingId.value = bookingId
    cancellationMessage.value = ''
    showCancellationModal.value = true
    return
  }

  // For other statuses, proceed normally
  updating.value = bookingId
  try {
    await bookingService.updateBookingStatus(bookingId, status)
    await loadBookings()
    if (status === 'confirmed') {
      successMessage.value = 'Foglalás megerősítve! A számla előnézet most elérhető.'
      setTimeout(() => { successMessage.value = '' }, 5000)
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'A foglalás státusz frissítése sikertelen'
  } finally {
    updating.value = null
  }
}

const confirmCancellation = async () => {
  if (!cancellationBookingId.value) return

  cancelling.value = true
  try {
    await bookingService.updateBookingStatus(
      cancellationBookingId.value,
      'cancelled',
      cancellationMessage.value.trim() || undefined
    )
    successMessage.value = 'Foglalás sikeresen törölve! A vendég értesítést kapott.'
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
    closeCancellationModal()
  } catch (err) {
    error.value = err.response?.data?.message || 'A foglalás törlése sikertelen'
  } finally {
    cancelling.value = false
  }
}

const closeCancellationModal = () => {
  showCancellationModal.value = false
  cancellationBookingId.value = null
  cancellationMessage.value = ''
}

const formatInvoiceStatus = (status) => {
  const statusMap = {
    'draft': 'Vázlat',
    'approved': 'Jóváhagyva',
    'sent': 'Elküldve'
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
    error.value = err.response?.data?.message || 'A számla előnézet generálása sikertelen'
  } finally {
    invoiceLoading.value = null
  }
}

const approveInvoice = async (invoiceId, bookingId) => {
  if (!confirm('Biztosan jóváhagyja ezt a számlát? Végleges lesz és nem szerkeszthető.')) {
    return
  }
  
  invoiceLoading.value = bookingId
  try {
    await invoiceService.approveInvoice(invoiceId)
    successMessage.value = 'Számla sikeresen jóváhagyva!'
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || 'A számla jóváhagyása sikertelen'
  } finally {
    invoiceLoading.value = null
  }
}

const sendInvoice = async (invoiceId, bookingId) => {
  if (!confirm('Elküldi ezt a számlát a vendégnek e-mailben?')) {
    return
  }
  
  invoiceLoading.value = bookingId
  try {
    await invoiceService.sendInvoice(invoiceId)
    successMessage.value = 'Számla sikeresen elküldve a vendégnek!'
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || 'A számla küldése sikertelen'
  } finally {
    invoiceLoading.value = null
  }
}

// Invoice editing functions
const openEditInvoiceModal = (booking) => {
  if (!booking.invoice) {
    error.value = 'Nem található számla ehhez a foglaláshoz'
    return
  }
  
  // Allow editing even if not draft (super admin mode)
  editingInvoice.value = booking.invoice
  manualTaxAmount.value = false
  manualTotalAmount.value = false
  
  invoiceForm.value = {
    invoice_number: booking.invoice.invoice_number || '',
    status: booking.invoice.status || 'draft',
    subtotal: parseFloat(booking.invoice.subtotal) || 0,
    tax_rate: parseFloat(booking.invoice.tax_rate) || 0,
    tax_amount: parseFloat(booking.invoice.tax_amount) || 0,
    total_amount: parseFloat(booking.invoice.total_amount) || 0,
    issue_date: booking.invoice.issue_date || '',
    due_date: booking.invoice.due_date || ''
  }
  
  showEditInvoiceModal.value = true
}

const closeEditInvoiceModal = () => {
  showEditInvoiceModal.value = false
  editingInvoice.value = null
  manualTaxAmount.value = false
  manualTotalAmount.value = false
  invoiceForm.value = {
    invoice_number: '',
    status: 'draft',
    subtotal: 0,
    tax_rate: 0,
    tax_amount: 0,
    total_amount: 0,
    issue_date: '',
    due_date: ''
  }
}

const recalculateInvoice = () => {
  if (!manualTaxAmount.value) {
    invoiceForm.value.tax_amount = parseFloat(
      (invoiceForm.value.subtotal * (invoiceForm.value.tax_rate / 100)).toFixed(2)
    )
  }
  if (!manualTotalAmount.value) {
    invoiceForm.value.total_amount = parseFloat(
      (parseFloat(invoiceForm.value.subtotal) + parseFloat(invoiceForm.value.tax_amount || 0)).toFixed(2)
    )
  }
}

const calculateTaxAmount = computed(() => {
  if (manualTaxAmount.value && invoiceForm.value.tax_amount) {
    return invoiceForm.value.tax_amount
  }
  return parseFloat((invoiceForm.value.subtotal * (invoiceForm.value.tax_rate / 100)).toFixed(2))
})

const calculateTotalAmount = computed(() => {
  if (manualTotalAmount.value && invoiceForm.value.total_amount) {
    return invoiceForm.value.total_amount
  }
  return parseFloat((parseFloat(invoiceForm.value.subtotal) + parseFloat(calculateTaxAmount.value)).toFixed(2))
})

const calculateDaysUntil = (dateString) => {
  if (!dateString) return 0
  const today = new Date()
  const dueDate = new Date(dateString)
  const diffTime = dueDate - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
}

const saveInvoice = async () => {
  if (!editingInvoice.value) return
  
  savingInvoice.value = true
  try {
    // Auto-calculate if not manually set
    if (!manualTaxAmount.value) {
      invoiceForm.value.tax_amount = calculateTaxAmount.value
    }
    if (!manualTotalAmount.value) {
      invoiceForm.value.total_amount = calculateTotalAmount.value
    }
    
    await invoiceService.updateInvoice(editingInvoice.value.id, {
      invoice_number: invoiceForm.value.invoice_number,
      status: invoiceForm.value.status,
      subtotal: invoiceForm.value.subtotal,
      tax_rate: invoiceForm.value.tax_rate,
      tax_amount: invoiceForm.value.tax_amount,
      total_amount: invoiceForm.value.total_amount,
      issue_date: invoiceForm.value.issue_date,
      due_date: invoiceForm.value.due_date
    })
    successMessage.value = 'Számla sikeresen frissítve!'
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
    closeEditInvoiceModal()
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'A számla frissítése sikertelen'
  } finally {
    savingInvoice.value = false
  }
}

// Booking editing functions
const openEditBookingModal = async (booking) => {
  editingBooking.value = booking
  bookingForm.value = {
    startDate: booking.startDate || '',
    endDate: booking.endDate || '',
    totalPrice: parseFloat(booking.totalPrice) || 0,
    status: booking.status || 'pending',
    rooms: (booking.rooms || []).map(r => r.id),
    services: (booking.services || []).map(s => s.id)
  }
  
  // Load available rooms and services for this hotel
  const hotelId = booking.hotel_id || booking.hotel?.id || selectedHotelId.value
  if (hotelId) {
    try {
      const roomsData = await hotelService.getRoomsByHotelId(hotelId)
      availableRooms.value = roomsData.rooms || roomsData || []
      
      const servicesData = await hotelService.getServicesByHotelId(hotelId)
      availableServices.value = servicesData.services || servicesData || []
    } catch (err) {
      console.error('Failed to load rooms/services:', err)
      availableRooms.value = []
      availableServices.value = []
    }
  }
  
  showEditBookingModal.value = true
}

const closeEditBookingModal = () => {
  showEditBookingModal.value = false
  editingBooking.value = null
  availableRooms.value = []
  availableServices.value = []
  selectedRoomToAdd.value = ''
  selectedServiceToAdd.value = ''
  bookingForm.value = {
    startDate: '',
    endDate: '',
    totalPrice: 0,
    status: 'pending',
    rooms: [],
    services: []
  }
}

const addRoom = () => {
  if (selectedRoomToAdd.value && !bookingForm.value.rooms.includes(selectedRoomToAdd.value)) {
    bookingForm.value.rooms.push(selectedRoomToAdd.value)
    selectedRoomToAdd.value = ''
  }
}

const removeRoom = (roomId) => {
  bookingForm.value.rooms = bookingForm.value.rooms.filter(id => id !== roomId)
}

const addService = () => {
  if (selectedServiceToAdd.value && !bookingForm.value.services.includes(selectedServiceToAdd.value)) {
    bookingForm.value.services.push(selectedServiceToAdd.value)
    selectedServiceToAdd.value = ''
  }
}

const removeService = (serviceId) => {
  bookingForm.value.services = bookingForm.value.services.filter(id => id !== serviceId)
}

const getRoomName = (roomId) => {
  const room = availableRooms.value.find(r => r.id === roomId)
  return room ? room.name : `Szoba #${roomId}`
}

const getServiceName = (serviceId) => {
  const service = availableServices.value.find(s => s.id === serviceId)
  return service ? service.name : `Szolgáltatás #${serviceId}`
}

const getServicePrice = (serviceId) => {
  const service = availableServices.value.find(s => s.id === serviceId)
  return service ? service.price : 0
}

const calculateRoomsPrice = computed(() => {
  if (!bookingForm.value.startDate || !bookingForm.value.endDate) return 0
  const nights = calculateNights(bookingForm.value.startDate, bookingForm.value.endDate)
  return bookingForm.value.rooms.reduce((total, roomId) => {
    const room = availableRooms.value.find(r => r.id === roomId)
    if (!room) return total
    return total + ((room.basePrice || 0) + (room.pricePerNight || 0) * nights)
  }, 0)
})

const calculateServicesPrice = computed(() => {
  return bookingForm.value.services.reduce((total, serviceId) => {
    return total + getServicePrice(serviceId)
  }, 0)
})

const saveBooking = async () => {
  if (!editingBooking.value) return
  
  savingBooking.value = true
  try {
    await bookingService.updateBooking(editingBooking.value.id, {
      startDate: bookingForm.value.startDate,
      endDate: bookingForm.value.endDate,
      totalPrice: bookingForm.value.totalPrice,
      status: bookingForm.value.status,
      rooms: bookingForm.value.rooms,
      services: bookingForm.value.services
    })
    successMessage.value = 'Foglalás sikeresen frissítve!'
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
    closeEditBookingModal()
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'A foglalás frissítése sikertelen'
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
      successMessage.value = 'Vendég sikeresen frissítve!'
    } else {
      // Add new guest
      await guestService.addGuests(booking.id, [{
        name: guestForm.value.name,
        idNumber: guestForm.value.idNumber,
        dateOfBirth: guestForm.value.dateOfBirth
      }])
      successMessage.value = 'Vendég sikeresen hozzáadva!'
    }
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
    closeGuestModal()
    error.value = ''
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
    successMessage.value = 'Vendég sikeresen törölve!'
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'A vendég törlése sikertelen'
  }
}

const confirmPayment = async (bookingId) => {
  if (!confirm('Megerősíti, hogy megkapta a banki átutalást? Ez elküldi a bejelentkezési QR kódot a vendégnek.')) {
    return
  }

  paymentLoading.value = bookingId
  try {
    await bookingService.confirmPayment(bookingId)
    successMessage.value = 'Fizetés megerősítve! A QR kód e-mailben elküldve a vendégnek.'
    setTimeout(() => { successMessage.value = '' }, 5000)
    await loadBookings()
    if (showBookingActionsModal.value) {
      const updatedBooking = bookings.value.find(b => b.id === bookingId)
      if (updatedBooking) {
        selectedBookingForActions.value = updatedBooking
      }
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'A fizetés megerősítése sikertelen'
  } finally {
    paymentLoading.value = null
  }
}

// Booking Actions Modal Functions
const openBookingActionsModal = (booking) => {
  selectedBookingForActions.value = booking
  showBookingActionsModal.value = true
}

const closeBookingActionsModal = () => {
  showBookingActionsModal.value = false
  selectedBookingForActions.value = null
}

const handleAction = async (action, data = null) => {
  if (!selectedBookingForActions.value) return

  const booking = selectedBookingForActions.value

  switch (action) {
    case 'accept':
      await updateBookingStatus(booking.id, 'confirmed')
      closeBookingActionsModal()
      break
    case 'reject':
      await updateBookingStatus(booking.id, 'cancelled')
      closeBookingActionsModal()
      break
    case 'edit':
      closeBookingActionsModal()
      await openEditBookingModal(booking)
      break
    case 'cancel':
      closeBookingActionsModal()
      cancellationBookingId.value = booking.id
      cancellationMessage.value = ''
      showCancellationModal.value = true
      break
    case 'addGuest':
      closeBookingActionsModal()
      openGuestModal(booking)
      break
    case 'editGuest':
      closeBookingActionsModal()
      openEditGuestModal(booking, data)
      break
    case 'deleteGuest':
      if (confirm(`Biztosan törölni szeretné a vendéget: ${data.name}?`)) {
        await deleteGuest(data.id, booking.id)
        await loadBookings()
        const updatedBooking = bookings.value.find(b => b.id === booking.id)
        if (updatedBooking) {
          selectedBookingForActions.value = updatedBooking
        }
      }
      break
    case 'editInvoice':
      closeBookingActionsModal()
      openEditInvoiceModal(booking)
      break
    case 'previewInvoice':
      await previewInvoice(booking.id)
      break
    case 'approveInvoice':
      if (confirm('Biztosan jóváhagyja ezt a számlát? Végleges lesz és nem szerkeszthető.')) {
        await approveInvoice(booking.invoice.id, booking.id)
        await loadBookings()
        const updatedBooking = bookings.value.find(b => b.id === booking.id)
        if (updatedBooking) {
          selectedBookingForActions.value = updatedBooking
        }
      }
      break
    case 'sendInvoice':
      await sendInvoice(booking.invoice.id, booking.id)
      await loadBookings()
      const updatedBooking = bookings.value.find(b => b.id === booking.id)
      if (updatedBooking) {
        selectedBookingForActions.value = updatedBooking
      }
      break
    case 'downloadInvoice':
      try {
        const blob = await invoiceService.downloadInvoice(booking.invoice.id)
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = `szamla_${booking.invoice.invoice_number}.pdf`
        link.click()
        window.URL.revokeObjectURL(url)
      } catch (err) {
        error.value = err.response?.data?.message || 'A számla letöltése sikertelen'
      }
      break
    case 'confirmPayment':
      await confirmPayment(booking.id)
      break
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

.page-header-main {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 2rem;
  flex-wrap: wrap;
}

.page-header-text {
  flex: 1;
  min-width: 0;
}

.page-header-text {
  min-width: 0;
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

/* Header compact selector tweaks */
.hotel-selector-compact.header-compact {
  max-width: 420px;
  margin: 0;
  flex-shrink: 0;
  padding: 0.625rem 0.875rem;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.hotel-selector-compact.header-compact .hotel-compact-info {
  gap: 0.625rem;
}

.hotel-selector-compact.header-compact .hotel-compact-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
  line-height: 1;
}

.hotel-selector-compact.header-compact .hotel-compact-details {
  gap: 0.15rem;
}

.hotel-selector-compact.header-compact .hotel-compact-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
  line-height: 1.2;
}

.hotel-selector-compact.header-compact .hotel-compact-location {
  font-size: 0.75rem;
  color: #6c757d;
  margin: 0;
  line-height: 1.2;
}

.hotel-selector-compact.header-compact .hotel-change-btn {
  padding: 0.5rem 0.875rem;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.35rem;
  white-space: nowrap;
  border-radius: 6px;
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
  gap: 1rem;
}

.btn-add-guest {
  padding: 0.5rem 1rem;
  background: #27ae60;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  flex-shrink: 0;
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

/* Hotel Selector Card */
.hotel-selector-card {
  max-width: 1400px;
  margin: 0 auto 2rem;
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #e0e0e0;
}

.hotel-selector-content {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.hotel-selector-label-row {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.hotel-selector-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #2c3e50;
  font-size: 1rem;
  white-space: nowrap;
}

.selector-icon {
  font-size: 1.5rem;
}

.hotel-select-dropdown {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  color: #2c3e50;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 200px;
}

.hotel-select-dropdown:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.hotel-select-dropdown:hover {
  border-color: #667eea;
}

.hotel-select-dropdown.error {
  border-color: #e74c3c;
  background-color: #fff5f5;
}

.hotel-select-dropdown.error:focus {
  border-color: #e74c3c;
  box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.required-asterisk {
  color: #e74c3c;
  font-weight: 700;
  margin-left: 0.25rem;
}

.hotel-selector-error {
  color: #e74c3c;
  font-size: 0.875rem;
  margin-top: 0.25rem;
  font-weight: 500;
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

/* Filter Tabs */
.bookings-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 2rem;
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
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.filter-btn:hover {
  background: #f0f0f0;
  border-color: #667eea;
  color: #667eea;
  transform: translateY(-1px);
}

.filter-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: #667eea;
  color: white;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.filter-icon {
  font-size: 1.1rem;
}

.filter-count {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.125rem 0.5rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 700;
}

.filter-btn.active .filter-count {
  background: rgba(255, 255, 255, 0.3);
}

.filter-btn:not(.active) .filter-count {
  background: #e5e7eb;
  color: #6b7280;
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
  flex-shrink: 0;
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

  .page-header-main {
    flex-direction: column;
    align-items: stretch;
  }

  .hotel-selector-compact.header-compact {
    max-width: 100%;
  }

  .bookings-grid {
    grid-template-columns: 1fr;
  }

  .booking-dates-section {
    padding: 1rem;
  }

  .booking-details {
    grid-template-columns: 1fr;
  }

  .pending-actions {
    grid-template-columns: 1fr;
  }

  .bookings-filters {
    padding: 0.75rem;
    gap: 0.5rem;
  }

  .filter-btn {
    padding: 0.625rem 1rem;
    font-size: 0.85rem;
  }

  .filter-count {
    display: none;
  }
}

/* Advanced Booking Modal Styles */
.advanced-booking-modal {
  max-width: 800px;
  max-height: 90vh;
}

.advanced-booking-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-section {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid #e0e0e0;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #667eea;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-select {
  padding: 0.75rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.2s ease;
  background: white;
  width: 100%;
}

.form-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input {
  padding: 0.75rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.2s ease;
  width: 100%;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-hint {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.85rem;
  color: #7f8c8d;
}

.guest-info-display {
  background: white;
  border-radius: 8px;
  padding: 1rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e0e0e0;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  font-weight: 600;
  color: #6b7280;
}

.info-value {
  color: #1f2937;
  font-weight: 500;
}

.payment-status {
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 600;
}

.payment-paid {
  background: #d4edda;
  color: #155724;
}

.payment-pending {
  background: #fff3cd;
  color: #856404;
}

.multi-select-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.selected-items {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  min-height: 2.5rem;
  padding: 0.5rem;
  background: white;
  border-radius: 8px;
  border: 2px solid #e0e0e0;
}

.selected-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: #667eea;
  color: white;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 500;
}

.remove-btn {
  background: rgba(255, 255, 255, 0.3);
  border: none;
  color: white;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.remove-btn:hover {
  background: rgba(255, 255, 255, 0.5);
}

.info-box {
  background: white;
  border-radius: 8px;
  padding: 1rem;
  margin-top: 1rem;
  border: 1px solid #e0e0e0;
}

.price-breakdown {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.price-breakdown > div {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e0e0e0;
}

.price-breakdown > div:last-child {
  border-bottom: none;
}

.total-price {
  font-size: 1.1rem;
  font-weight: 700;
  color: #667eea;
  margin-top: 0.5rem;
  padding-top: 0.5rem;
  border-top: 2px solid #667eea;
}

.loading-text {
  color: #7f8c8d;
  font-style: italic;
  padding: 1rem;
  text-align: center;
}

/* Super Invoice Modal Styles */
.super-invoice-modal {
  max-width: 900px;
  max-height: 90vh;
}

.super-invoice-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.invoice-summary-advanced {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  border: 2px solid #e0e0e0;
}

.invoice-summary-advanced .summary-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  font-size: 1rem;
  border-bottom: 1px solid #e0e0e0;
}

.invoice-summary-advanced .summary-row:last-child {
  border-bottom: none;
}

.invoice-summary-advanced .summary-row.total-row {
  border-top: 2px solid #667eea;
  padding-top: 1rem;
  margin-top: 0.5rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #667eea;
}

.days-until {
  margin-left: 0.5rem;
  color: #7f8c8d;
  font-size: 0.9rem;
}

/* Cancellation Modal Styles */
.cancellation-modal {
  max-width: 600px;
  max-height: 90vh;
}

.cancellation-warning {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.cancellation-warning p {
  margin: 0;
  color: #7f1d1d;
  font-size: 0.95rem;
}

.cancellation-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit;
  resize: vertical;
  min-height: 120px;
  transition: all 0.2s ease;
}

.form-textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-danger {
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
  color: white;
}

.btn-danger:hover:not(:disabled) {
  background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(231, 76, 60, 0.5);
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

.guest-cell {
  min-width: 200px;
}

.guest-info-inline {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.guest-name-inline {
  font-weight: 600;
  color: #2c3e50;
}

.guest-email-inline {
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

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .advanced-booking-modal,
  .super-invoice-modal,
  .cancellation-modal {
    width: 95%;
    max-width: 95%;
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

  .actions-modal {
    width: 95%;
    max-width: 95%;
  }

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
}

/* View Switcher */
.view-switcher {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  max-width: 1400px;
  margin-left: auto;
  margin-right: auto;
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
  padding: 0.5rem 1rem;
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
  transform: translateY(-1px);
}

.view-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: #667eea;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.view-icon {
  font-size: 1.1rem;
}

/* Actions Cell in Table */
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

/* Actions Modal */
.actions-modal {
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
}

.actions-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.booking-info-summary {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid #e0e0e0;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e0e0e0;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.95rem;
}

.info-value {
  color: #7f8c8d;
  font-size: 0.95rem;
}

.info-value.price {
  color: #27ae60;
  font-weight: 600;
  font-size: 1.1rem;
}

.actions-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.action-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.action-group-title {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e0e0e0;
}

.action-btn {
  width: 100%;
  padding: 0.875rem 1.25rem;
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  color: #2c3e50;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: left;
}

.action-btn:hover:not(:disabled) {
  transform: translateX(4px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.action-btn.btn-accept {
  border-color: #27ae60;
  color: #27ae60;
}

.action-btn.btn-accept:hover:not(:disabled) {
  background: #27ae60;
  color: white;
}

.action-btn.btn-reject,
.action-btn.btn-danger {
  border-color: #e74c3c;
  color: #e74c3c;
}

.action-btn.btn-reject:hover:not(:disabled),
.action-btn.btn-danger:hover:not(:disabled) {
  background: #e74c3c;
  color: white;
}

.action-btn.btn-edit {
  border-color: #667eea;
  color: #667eea;
}

.action-btn.btn-edit:hover:not(:disabled) {
  background: #667eea;
  color: white;
}

.action-btn.btn-primary {
  border-color: #667eea;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.action-btn.btn-primary:hover:not(:disabled) {
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.guests-list-actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.guest-action-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.guest-name {
  font-weight: 500;
  color: #2c3e50;
}

.guest-item-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  padding: 0.5rem;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 1rem;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-icon:hover {
  transform: scale(1.1);
}

.btn-edit-icon:hover {
  background: #e8f4f8;
  border-color: #667eea;
}

.btn-delete-icon:hover {
  background: #fee2e2;
  border-color: #e74c3c;
}

.payment-confirmed {
  padding: 0.875rem 1.25rem;
  background: #d4edda;
  border: 2px solid #27ae60;
  border-radius: 8px;
  color: #27ae60;
  font-weight: 600;
  text-align: center;
}

/* Minimal Hotel Carousel Styles */
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
  transition: transform 0.3s ease;
}

.hotel-card-item-minimal {
  min-width: 100%;
  padding: 0 1rem;
  display: flex;
  flex-direction: column;
  cursor: pointer;
}

.hotel-card-item-minimal.selected {
  opacity: 1;
}

.hotel-card-image-minimal {
  position: relative;
  width: 100%;
  height: 150px;
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.hotel-cover-image-minimal {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hotel-image-placeholder-minimal {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.hotel-icon-minimal {
  font-size: 3rem;
  opacity: 0.7;
}

.hotel-card-content-minimal {
  background: white;
  padding: 1rem;
  border-radius: 12px;
}

.hotel-card-name-minimal {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 0.25rem 0;
}

.hotel-card-location-minimal {
  font-size: 0.85rem;
  color: #7f8c8d;
  margin: 0 0 1rem 0;
}

.hotel-select-btn-minimal {
  width: 100%;
  padding: 0.75rem 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.hotel-card-item-minimal.selected .hotel-select-btn-minimal {
  background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
}

.hotel-select-btn-minimal:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.carousel-nav-btn-modern {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 2px solid rgba(102, 126, 234, 0.2);
  width: 48px;
  height: 48px;
  border-radius: 10px;
  color: #667eea;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(102, 126, 234, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}

.carousel-nav-btn-modern:hover {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: #667eea;
  transform: translateY(-50%) scale(1.15);
  box-shadow: 0 6px 24px rgba(102, 126, 234, 0.4), 0 0 0 1px rgba(102, 126, 234, 0.2);
}

.carousel-nav-btn-modern:hover svg path {
  stroke: white;
}

.carousel-nav-btn-modern:active {
  transform: translateY(-50%) scale(1.05);
}

.carousel-nav-btn-modern svg {
  width: 24px;
  height: 24px;
  transition: transform 0.2s ease;
}

.carousel-nav-btn-modern:hover svg {
  transform: scale(1.1);
}

.carousel-prev-modern {
  left: 1rem;
}

.carousel-prev-modern:hover svg {
  transform: translateX(-2px) scale(1.1);
}

.carousel-next-modern {
  right: 1rem;
}

.carousel-next-modern:hover svg {
  transform: translateX(2px) scale(1.1);
}

.carousel-indicators-minimal {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem;
  background: white;
}

.carousel-indicator-minimal {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  border: 1px solid #667eea;
  background: transparent;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
}

.carousel-indicator-minimal:hover {
  background: rgba(102, 126, 234, 0.3);
}

.carousel-indicator-minimal.active {
  background: #667eea;
  transform: scale(1.2);
}

/* Compact Hotel Selector */
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

.hotel-change-btn svg {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

.hotel-change-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 10px rgba(102, 126, 234, 0.35);
}

.hotel-change-btn:active {
  transform: translateY(0);
}

/* Fade Transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .hotel-carousel-container-minimal {
    width: 95%;
    max-width: 95%;
  }

  .hotel-card-item-minimal {
    padding: 0 0.75rem;
  }

  .carousel-nav-btn-modern {
    width: 40px;
    height: 40px;
  }

  .carousel-nav-btn-modern svg {
    width: 18px;
    height: 18px;
  }

  .carousel-prev-modern {
    left: 0.5rem;
  }

  .carousel-next-modern {
    right: 0.5rem;
  }

  .carousel-nav-btn-modern {
    width: 40px;
    height: 40px;
  }

  .carousel-nav-btn-modern svg {
    width: 18px;
    height: 18px;
  }

  .carousel-prev-modern {
    left: 0.5rem;
  }

  .carousel-next-modern {
    right: 0.5rem;
  }

  .hotel-card-image-minimal {
    height: 120px;
  }

  .hotel-selector-compact {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .hotel-change-btn {
    width: 100%;
  }
}
</style>
