<template>
  <Transition name="fade">
    <div v-if="visible" class="invoice-data-blocker">
      <div class="blocker-overlay"></div>
      <div class="blocker-content">
        <div class="blocker-icon">📋</div>
        <h2 class="blocker-title">Invoice Data Required</h2>
        <p class="blocker-message">
          To access the admin panel, you must complete your invoice information first.
          This data is required for generating invoices for your hotel bookings.
        </p>
        <div class="blocker-actions">
          <button @click="handleComplete" class="btn-complete">
            Complete Invoice Data
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['complete'])

const handleComplete = () => {
  router.push('/admin/users')
  emit('complete')
}
</script>

<style scoped>
.invoice-data-blocker {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.blocker-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(5px);
}

.blocker-content {
  position: relative;
  background: white;
  border-radius: 20px;
  padding: 3rem;
  max-width: 500px;
  width: 90%;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  z-index: 10001;
}

.blocker-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.blocker-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 1rem;
}

.blocker-message {
  font-size: 1rem;
  color: #666;
  line-height: 1.6;
  margin-bottom: 2rem;
}

.blocker-actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
}

.btn-complete {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 1rem 2rem;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-complete:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

.btn-complete:active {
  transform: translateY(0);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
