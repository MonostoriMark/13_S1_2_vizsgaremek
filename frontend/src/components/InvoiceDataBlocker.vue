<template>
  <Transition name="modal">
    <div v-if="visible" class="invoice-data-blocker">
      <div class="blocker-overlay"></div>
      <div class="blocker-content">
        <div class="blocker-header">
          <div class="warning-icon">⚠️</div>
          <h2>Számlázási adatok hiányoznak</h2>
          <p class="blocker-subtitle">A szállodák számlázási adatainak kitöltése kötelező az admin felület használatához</p>
        </div>

        <div class="blocker-body">
          <div class="info-section">
            <p class="info-text">
              Az admin felület eléréséhez először ki kell töltenie a számlázási adatokat <strong>minden szállodához</strong>.
              Ezek az adatok szükségesek a foglalásokhoz tartozó számlák kiállításához.
            </p>
            <div v-if="missingHotels && missingHotels.length > 0" class="missing-hotels-list">
              <p class="missing-hotels-title">Hiányzó számlázási adatok a következő szállodákhoz:</p>
              <div class="hotel-list">
                <div v-for="hotel in missingHotels" :key="hotel.id" class="hotel-item">
                  <span class="hotel-icon">🏨</span>
                  <span class="hotel-name">{{ hotel.name || `Szálloda #${hotel.id}` }}</span>
                  <span class="hotel-location">{{ hotel.location || '' }}</span>
                </div>
              </div>
            </div>
            <div class="features-list">
              <div class="feature-item">
                <span class="feature-icon">✓</span>
                <span>Adószám, bankszámlaszám és EU adószám megadása kötelező</span>
              </div>
              <div class="feature-item">
                <span class="feature-icon">✓</span>
                <span>Minden szállodához külön kell kitölteni</span>
              </div>
              <div class="feature-item">
                <span class="feature-icon">✓</span>
                <span>Amíg nincs kitöltve, az admin műveletek le vannak tiltva</span>
              </div>
            </div>
          </div>

          <div class="action-buttons">
            <button @click="handleComplete" class="btn-complete">
              <span class="btn-icon">📋</span>
              Számlázási adatok kitöltése most
            </button>
          </div>

          <p class="help-text">
            A számlázási adatokat a "Cégadatok" menüpontban tudod kitölteni minden szállodához.
          </p>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useBodyScrollLock } from '../composables/useBodyScrollLock'

const router = useRouter()

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  missingHotels: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['complete'])

const handleComplete = () => {
  router.push('/admin/company-info')
  emit('complete')
}

// Lock body scroll when blocker is visible
const isVisible = computed(() => props.visible)
useBodyScrollLock(isVisible)
</script>

<style scoped>
.invoice-data-blocker {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 10001;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  overflow-y: auto;
}

.blocker-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(220, 38, 38, 0.85);
  backdrop-filter: blur(8px);
}

.blocker-content {
  background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
  border: 3px solid rgba(239, 68, 68, 0.5);
  border-radius: 20px;
  box-shadow: 0 25px 70px rgba(239, 68, 68, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.5);
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  z-index: 10002;
  margin: 2rem auto;
}

.blocker-header {
  padding: 1.5rem 1.5rem 1rem;
  text-align: center;
  border-bottom: 2px solid rgba(239, 68, 68, 0.3);
  background: linear-gradient(135deg, rgba(254, 226, 226, 0.6) 0%, rgba(252, 165, 165, 0.3) 100%);
}

.warning-icon {
  font-size: 3.5rem;
  margin-bottom: 0.75rem;
  filter: drop-shadow(0 4px 12px rgba(239, 68, 68, 0.5));
  animation: pulse-warning 2s ease-in-out infinite;
}

@keyframes pulse-warning {
  0%, 100% {
    transform: scale(1);
    filter: drop-shadow(0 4px 12px rgba(239, 68, 68, 0.5));
  }
  50% {
    transform: scale(1.1);
    filter: drop-shadow(0 6px 16px rgba(239, 68, 68, 0.7));
  }
}

.blocker-header h2 {
  margin: 0.25rem 0;
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.blocker-subtitle {
  color: #dc2626;
  font-size: 0.9rem;
  margin-top: 0.25rem;
  margin-bottom: 0;
  font-weight: 600;
}

.blocker-body {
  padding: 1.5rem;
}

.info-section {
  margin-bottom: 1.5rem;
}

.info-text {
  color: #4b5563;
  font-size: 0.95rem;
  line-height: 1.6;
  text-align: center;
  margin-bottom: 1rem;
  font-weight: 500;
}

.info-text strong {
  color: #dc2626;
  font-weight: 700;
}

.missing-hotels-list {
  margin: 1rem 0;
  padding: 1rem;
  background: rgba(239, 68, 68, 0.08);
  border-radius: 12px;
  border: 2px solid rgba(239, 68, 68, 0.2);
  max-height: 200px;
  overflow-y: auto;
}

.missing-hotels-title {
  color: #dc2626;
  font-weight: 700;
  font-size: 0.9rem;
  margin-bottom: 0.75rem;
  text-align: center;
}

.hotel-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.hotel-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: white;
  border-radius: 8px;
  border-left: 4px solid #ef4444;
  box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1);
}

.hotel-icon {
  font-size: 1.5rem;
}

.hotel-name {
  font-weight: 600;
  color: #1f2937;
  flex: 1;
}

.hotel-location {
  color: #6b7280;
  font-size: 0.9rem;
}

.features-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1rem;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: rgba(239, 68, 68, 0.08);
  border-radius: 8px;
  border-left: 3px solid #ef4444;
}

.feature-icon {
  color: #22c55e;
  font-weight: 700;
  font-size: 1.2rem;
}

.feature-item span:last-child {
  color: #374151;
  font-size: 0.95rem;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.btn-complete {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  border: none;
  border-radius: 12px;
  color: white;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
}

.btn-complete:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
}

.btn-icon {
  font-size: 1.5rem;
}

.help-text {
  text-align: center;
  color: #6b7280;
  font-size: 0.85rem;
  margin: 0;
  padding-top: 0.75rem;
  border-top: 1px solid #e5e7eb;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.9);
}
</style>
