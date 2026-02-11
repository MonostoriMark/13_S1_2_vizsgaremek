<template>
  <div class="payment-page">
    <!-- Home Button -->
    <router-link to="/" class="home-button">
      <span class="home-icon">🏠</span>
      <span class="home-text">Kezdőlap</span>
    </router-link>

    <!-- Payment Card -->
    <div class="payment-container">
      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Fizetési információk betöltése...</p>
      </div>

      <div v-else-if="error" class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>Hiba történt</h1>
        <p>{{ error }}</p>
        <router-link to="/" class="btn-primary">Vissza a kezdőlapra</router-link>
      </div>

      <div v-else-if="paymentProcessed" class="success-container">
        <div class="success-icon">✓</div>
        <h1>Fizetés sikeres!</h1>
        <p>Köszönjük a fizetést! A számla sikeresen ki lett fizetve.</p>
        <div class="invoice-summary">
          <div class="summary-row">
            <span>Sorszámla szám:</span>
            <strong>{{ invoiceData.invoice_number }}</strong>
          </div>
          <div class="summary-row">
            <span>Fizetett összeg:</span>
            <strong>{{ formatCurrency(invoiceData.total_amount) }}</strong>
          </div>
        </div>
        <router-link to="/" class="btn-primary">Vissza a kezdőlapra</router-link>
      </div>

      <div v-else-if="invoiceData" class="payment-content">
        <div class="payment-header">
          <div class="payment-icon">💳</div>
          <h1>Bankkártyás fizetés</h1>
          <p class="subtitle">Számla kifizetése</p>
        </div>

        <div class="invoice-details">
          <h2>Számla részletei</h2>
          <div class="detail-card">
            <div class="detail-row">
              <span class="label">Sorszámla szám:</span>
              <span class="value">{{ invoiceData.invoice_number }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Szálloda:</span>
              <span class="value">{{ invoiceData.booking?.hotel?.name || 'N/A' }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Foglalás azonosító:</span>
              <span class="value">#{{ invoiceData.booking?.id || 'N/A' }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Kibocsátás dátuma:</span>
              <span class="value">{{ formatDate(invoiceData.issue_date) }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Fizetési határidő:</span>
              <span class="value">{{ formatDate(invoiceData.due_date) }}</span>
            </div>
            <div class="detail-row total">
              <span class="label">Fizetendő összeg:</span>
              <span class="value">{{ formatCurrency(invoiceData.total_amount) }}</span>
            </div>
          </div>
        </div>

        <div class="payment-form">
          <h2>Fizetési adatok</h2>
          <div class="form-group">
            <label>Kártyaszám *</label>
            <input 
              v-model="cardNumber" 
              type="text" 
              placeholder="1234 5678 9012 3456"
              maxlength="19"
              @input="formatCardNumber"
            />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Lejárati dátum *</label>
              <input 
                v-model="expiryDate" 
                type="text" 
                placeholder="MM/YY"
                maxlength="5"
                @input="formatExpiryDate"
              />
            </div>
            <div class="form-group">
              <label>CVV *</label>
              <input 
                v-model="cvv" 
                type="text" 
                placeholder="123"
                maxlength="4"
                @input="formatCVV"
              />
            </div>
          </div>
          <div class="form-group">
            <label>Kártyatulajdonos neve *</label>
            <input 
              v-model="cardholderName" 
              type="text" 
              placeholder="Kovács János"
            />
          </div>

          <div v-if="paymentError" class="error-message">
            {{ paymentError }}
          </div>

          <button 
            @click="processPayment" 
            class="btn-pay"
            :disabled="processing || !isFormValid"
          >
            <span v-if="processing">Feldolgozás...</span>
            <span v-else>Fizetés: {{ formatCurrency(invoiceData.total_amount) }}</span>
          </button>

          <p class="security-note">
            🔒 Biztonságos fizetés SSL titkosítással
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { invoiceService } from '../services/invoiceService'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref('')
const invoiceData = ref(null)
const paymentProcessed = ref(false)
const processing = ref(false)
const paymentError = ref('')

const cardNumber = ref('')
const expiryDate = ref('')
const cvv = ref('')
const cardholderName = ref('')

const isFormValid = computed(() => {
  return cardNumber.value.replace(/\s/g, '').length === 16 &&
         expiryDate.value.length === 5 &&
         cvv.value.length >= 3 &&
         cardholderName.value.trim().length > 0
})

const formatCardNumber = (e) => {
  let value = e.target.value.replace(/\s/g, '')
  if (value.length > 16) value = value.slice(0, 16)
  cardNumber.value = value.match(/.{1,4}/g)?.join(' ') || value
}

const formatExpiryDate = (e) => {
  let value = e.target.value.replace(/\D/g, '')
  if (value.length > 4) value = value.slice(0, 4)
  if (value.length >= 2) {
    value = value.slice(0, 2) + '/' + value.slice(2)
  }
  expiryDate.value = value
}

const formatCVV = (e) => {
  cvv.value = e.target.value.replace(/\D/g, '').slice(0, 4)
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('hu-HU', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('hu-HU')
}

const loadInvoice = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const data = await invoiceService.getInvoiceByPaymentToken(route.params.token)
    invoiceData.value = data.invoice
  } catch (err) {
    error.value = err.response?.data?.error || 'Nem sikerült betölteni a számla adatait'
  } finally {
    loading.value = false
  }
}

const processPayment = async () => {
  if (!isFormValid.value) {
    paymentError.value = 'Kérjük, töltsd ki az összes mezőt'
    return
  }

  processing.value = true
  paymentError.value = ''

  try {
    // Simulate payment processing delay
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    const data = await invoiceService.processPayment(route.params.token)
    
    if (data.message) {
      paymentProcessed.value = true
      if (window.showToast) {
        window.showToast('Fizetés sikeresen feldolgozva', 'success')
      }
    }
  } catch (err) {
    paymentError.value = err.response?.data?.error || 'A fizetés feldolgozása sikertelen'
    if (window.showToast) {
      window.showToast(paymentError.value, 'error')
    }
  } finally {
    processing.value = false
  }
}

onMounted(() => {
  loadInvoice()
})
</script>

<style scoped>
.payment-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.home-button {
  position: absolute;
  top: 1.5rem;
  left: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  color: #667eea;
  text-decoration: none;
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: 100;
}

.home-button:hover {
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.payment-container {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  max-width: 600px;
  width: 100%;
  padding: 2.5rem;
}

.loading-container,
.error-container,
.success-container {
  text-align: center;
  padding: 2rem;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #f3f4f6;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-icon,
.success-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.success-icon {
  color: #22c55e;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(34, 197, 94, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  font-size: 3rem;
}

.payment-header {
  text-align: center;
  margin-bottom: 2rem;
}

.payment-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.payment-header h1 {
  font-size: 1.75rem;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #6b7280;
  font-size: 1rem;
}

.invoice-details {
  margin-bottom: 2rem;
}

.invoice-details h2 {
  font-size: 1.25rem;
  color: #1f2937;
  margin-bottom: 1rem;
}

.detail-card {
  background: #f9fafb;
  border-radius: 8px;
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row.total {
  border-top: 2px solid #667eea;
  margin-top: 0.5rem;
  padding-top: 1rem;
  font-size: 1.1rem;
}

.detail-row .label {
  color: #6b7280;
  font-weight: 500;
}

.detail-row .value {
  color: #1f2937;
  font-weight: 600;
}

.detail-row.total .value {
  color: #667eea;
  font-size: 1.25rem;
}

.payment-form h2 {
  font-size: 1.25rem;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #374151;
  font-weight: 500;
  font-size: 0.9rem;
}

.form-group input {
  width: 100%;
  padding: 0.875rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.2s;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.error-message {
  background: #fee2e2;
  border: 1px solid #fca5a5;
  color: #dc2626;
  padding: 0.875rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.btn-pay {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 1rem;
}

.btn-pay:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-pay:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  display: inline-block;
  padding: 0.875rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  margin-top: 1rem;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.security-note {
  text-align: center;
  color: #6b7280;
  font-size: 0.875rem;
  margin-top: 1rem;
}

.invoice-summary {
  background: #f9fafb;
  border-radius: 8px;
  padding: 1.5rem;
  margin: 1.5rem 0;
  border: 1px solid #e5e7eb;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.summary-row:last-child {
  border-bottom: none;
}

.summary-row strong {
  color: #667eea;
  font-size: 1.1rem;
}
</style>
