<template>
  <Transition name="modal">
    <div v-if="visible" class="modal-overlay" @click.self="handleDismiss">
      <div class="modal-content">
        <div class="modal-header">
          <div class="welcome-icon">🏨</div>
          <h2>Üdvözöljük a HotelFlow Admin felületén</h2>
          <p class="modal-subtitle">Szálloda kezelő irányítópultja</p>
        </div>

        <div class="modal-body">
          <div class="info-section">
            <p class="info-text">
              {{ message || 'Kezelje szállodáit, szobáit, szolgáltatásait és foglalásait egy helyen. Kezdje el az irányítópult felfedezésével!' }}
            </p>
          </div>

          <div v-if="showQuickActions" class="quick-actions-section">
            <h3 class="actions-title">Gyors műveletek</h3>
            <div class="actions-grid">
              <button @click="handleAction('hotels')" class="action-btn">
                <span class="action-icon">🏨</span>
                <span>Szállodák kezelése</span>
              </button>
              <button @click="handleAction('rooms')" class="action-btn">
                <span class="action-icon">🛏️</span>
                <span>Szobák kezelése</span>
              </button>
              <button @click="handleAction('bookings')" class="action-btn">
                <span class="action-icon">📅</span>
                <span>Foglalások megtekintése</span>
              </button>
            </div>
          </div>

          <div class="action-buttons">
            <button v-if="primaryAction" @click="handlePrimary" class="btn-primary">
              <span class="btn-icon">{{ primaryIcon || '✨' }}</span>
              {{ primaryAction }}
            </button>
            <button @click="handleDismiss" class="btn-dismiss">
              {{ dismissText || 'Rendben!' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue'
import { useBodyScrollLock } from '../composables/useBodyScrollLock'
import { useRouter } from 'vue-router'

const router = useRouter()

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  message: {
    type: String,
    default: ''
  },
  primaryAction: {
    type: String,
    default: ''
  },
  primaryIcon: {
    type: String,
    default: ''
  },
  dismissText: {
    type: String,
    default: 'Rendben!'
  },
  showQuickActions: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['dismiss', 'primary', 'action'])

const handleDismiss = () => {
  emit('dismiss')
}

// Lock body scroll when modal is visible
const isVisible = computed(() => props.visible)
useBodyScrollLock(isVisible)

const handlePrimary = () => {
  emit('primary')
}

const handleAction = (action) => {
  emit('action', action)
  
  // Navigate based on action
  switch(action) {
    case 'hotels':
      router.push('/admin/hotels')
      break
    case 'rooms':
      router.push('/admin/rooms')
      break
    case 'bookings':
      router.push('/admin/bookings')
      break
    case 'services':
      router.push('/admin/services')
      break
  }
  
  emit('dismiss')
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(6px);
}

.modal-content {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
  border: 2px solid rgba(102, 126, 234, 0.2);
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(102, 126, 234, 0.15), 0 0 0 1px rgba(255, 255, 255, 0.5);
  max-width: 550px;
  width: 90%;
  overflow: hidden;
  position: relative;
}

.modal-header {
  padding: 2.5rem 2rem 1.5rem;
  text-align: center;
  border-bottom: 1px solid rgba(102, 126, 234, 0.1);
  background: linear-gradient(135deg, rgba(224, 213, 255, 0.3) 0%, rgba(212, 197, 247, 0.2) 100%);
}

.welcome-icon {
  font-size: 4.5rem;
  margin-bottom: 1rem;
  filter: drop-shadow(0 4px 12px rgba(102, 126, 234, 0.3));
  animation: float-gentle 3s ease-in-out infinite;
}

@keyframes float-gentle {
  0%, 100% {
    transform: translateY(0px);
    filter: drop-shadow(0 4px 12px rgba(102, 126, 234, 0.3));
  }
  50% {
    transform: translateY(-8px);
    filter: drop-shadow(0 6px 16px rgba(102, 126, 234, 0.4));
  }
}

.modal-header h2 {
  margin: 0.5rem 0;
  font-size: 1.85rem;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.modal-subtitle {
  color: #6b7280;
  font-size: 0.95rem;
  margin-top: 0.5rem;
  margin-bottom: 0;
  font-weight: 500;
}

.modal-body {
  padding: 2rem;
}

.info-section {
  margin-bottom: 2rem;
}

.info-text {
  color: #4b5563;
  font-size: 1rem;
  line-height: 1.7;
  text-align: center;
  font-weight: 400;
}

.quick-actions-section {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.6);
  border-radius: 12px;
  border: 1px solid rgba(102, 126, 234, 0.1);
}

.actions-title {
  font-size: 1rem;
  font-weight: 600;
  color: #667eea;
  margin: 0 0 1rem 0;
  text-align: center;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
}

.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 0.75rem;
  background: white;
  border: 1px solid rgba(102, 126, 234, 0.2);
  border-radius: 10px;
  color: #667eea;
  font-weight: 500;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 2px 4px rgba(102, 126, 234, 0.1);
}

.action-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
  border-color: rgba(102, 126, 234, 0.4);
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
}

.action-icon {
  font-size: 1.75rem;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.btn-primary {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 12px;
  color: white;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-icon {
  font-size: 1.25rem;
}

.btn-dismiss {
  width: 100%;
  padding: 0.875rem;
  background: white;
  border: 2px solid rgba(102, 126, 234, 0.3);
  border-radius: 12px;
  color: #667eea;
  font-weight: 500;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-dismiss:hover {
  border-color: rgba(102, 126, 234, 0.6);
  color: #764ba2;
  background: rgba(102, 126, 234, 0.05);
  transform: translateY(-1px);
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(-10px);
}
</style>
