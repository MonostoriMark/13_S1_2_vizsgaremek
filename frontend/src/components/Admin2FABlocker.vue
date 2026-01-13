<template>
  <Transition name="modal">
    <div v-if="visible" class="blocker-overlay" @click.self.prevent>
      <div class="blocker-content">
        <div class="blocker-header">
          <div class="security-icon">🔒</div>
          <h2>Two-Factor Authentication Required</h2>
          <p class="blocker-subtitle">Hotel administrators must enable 2FA to access the admin panel</p>
        </div>

        <div class="blocker-body">
          <div class="info-section">
            <p class="info-text">
              For security reasons, all hotel administrators are required to enable two-factor authentication (2FA) before accessing the admin panel.
            </p>
            <div class="features-list">
              <div class="feature-item">
                <span class="feature-icon">✓</span>
                <span>Enhanced account security</span>
              </div>
              <div class="feature-item">
                <span class="feature-icon">✓</span>
                <span>Protection against unauthorized access</span>
              </div>
              <div class="feature-item">
                <span class="feature-icon">✓</span>
                <span>Required for all hotel administrators</span>
              </div>
            </div>
          </div>

          <div class="action-buttons">
            <button @click="handleEnable2FA" class="btn-enable">
              <span class="btn-icon">🔐</span>
              Enable 2FA Now
            </button>
          </div>

          <p class="help-text">You can enable 2FA in your profile settings. All admin operations are blocked until 2FA is enabled.</p>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['enable'])

const handleEnable2FA = () => {
  emit('enable')
  // The layout will handle navigation and auto-open 2FA setup
  router.push('/admin/users')
}
</script>

<style scoped>
.blocker-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10001;
  backdrop-filter: blur(8px);
}

.blocker-content {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
  border: 3px solid rgba(239, 68, 68, 0.3);
  border-radius: 20px;
  box-shadow: 0 25px 70px rgba(239, 68, 68, 0.2), 0 0 0 1px rgba(255, 255, 255, 0.5);
  max-width: 600px;
  width: 90%;
  overflow: hidden;
  position: relative;
}

.blocker-header {
  padding: 2.5rem 2rem 1.5rem;
  text-align: center;
  border-bottom: 2px solid rgba(239, 68, 68, 0.2);
  background: linear-gradient(135deg, rgba(254, 226, 226, 0.4) 0%, rgba(252, 165, 165, 0.2) 100%);
}

.security-icon {
  font-size: 5rem;
  margin-bottom: 1rem;
  filter: drop-shadow(0 4px 12px rgba(239, 68, 68, 0.4));
  animation: shake-warning 2s ease-in-out infinite;
}

@keyframes shake-warning {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
    filter: drop-shadow(0 4px 12px rgba(239, 68, 68, 0.4));
  }
  25% {
    transform: translateY(-5px) rotate(-2deg);
    filter: drop-shadow(0 6px 16px rgba(239, 68, 68, 0.5));
  }
  50% {
    transform: translateY(0px) rotate(0deg);
    filter: drop-shadow(0 4px 12px rgba(239, 68, 68, 0.4));
  }
  75% {
    transform: translateY(-5px) rotate(2deg);
    filter: drop-shadow(0 6px 16px rgba(239, 68, 68, 0.5));
  }
}

.blocker-header h2 {
  margin: 0.5rem 0;
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.blocker-subtitle {
  color: #dc2626;
  font-size: 1rem;
  margin-top: 0.5rem;
  margin-bottom: 0;
  font-weight: 600;
}

.blocker-body {
  padding: 2.5rem;
}

.info-section {
  margin-bottom: 2.5rem;
}

.info-text {
  color: #4b5563;
  font-size: 1.05rem;
  line-height: 1.7;
  text-align: center;
  margin-bottom: 1.5rem;
  font-weight: 500;
}

.features-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1.5rem;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(239, 68, 68, 0.05);
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
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.btn-enable {
  width: 100%;
  padding: 1.25rem;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  border: none;
  border-radius: 12px;
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
}

.btn-enable:hover {
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
  font-size: 0.9rem;
  margin: 0;
  padding-top: 1rem;
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
