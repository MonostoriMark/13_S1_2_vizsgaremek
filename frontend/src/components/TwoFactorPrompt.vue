<template>
  <Transition name="modal">
    <div v-if="visible" class="modal-overlay" @click.self="handleSkip">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <div class="security-icon">🔐</div>
          <h2>Kétfaktoros hitelesítés engedélyezése</h2>
          <p class="modal-subtitle">További biztonsági réteg hozzáadása a fiókjához</p>
        </div>

        <div class="modal-body">
          <div class="info-section">
            <p class="info-text">
              A kétfaktoros hitelesítés (2FA) segít megvédeni a fiókját azáltal, hogy a jelszó mellett telefonjáról is kódot igényel.
            </p>
          </div>

          <div class="action-buttons">
            <button @click="handleEnable" class="btn-enable">
              <span class="btn-icon">🔒</span>
              2FA engedélyezése
            </button>
            <button @click="handleSkip" class="btn-skip">
              Kihagyás most
            </button>
          </div>

          <p class="skip-hint">A 2FA-t bármikor engedélyezheti a profil beállításaiban</p>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue'
import { useBodyScrollLock } from '../composables/useBodyScrollLock'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['enable', 'skip'])

const handleEnable = () => {
  emit('enable')
}

const handleSkip = () => {
  emit('skip')
}

// Lock body scroll when modal is visible
const isVisible = computed(() => props.visible)
useBodyScrollLock(isVisible)
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
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(102, 126, 234, 0.15), 0 0 0 1px rgba(255, 255, 255, 0.5);
  max-width: 500px;
  width: 90%;
  overflow: hidden;
  position: relative;
}

.modal-header {
  padding: 2rem;
  text-align: center;
  border-bottom: 1px solid rgba(102, 126, 234, 0.1);
  background: linear-gradient(135deg, rgba(224, 213, 255, 0.3) 0%, rgba(212, 197, 247, 0.2) 100%);
}

.security-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  filter: drop-shadow(0 4px 12px rgba(102, 126, 234, 0.3));
  animation: pulse-glow 2s ease-in-out infinite;
}

@keyframes pulse-glow {
  0%, 100% {
    filter: drop-shadow(0 4px 12px rgba(102, 126, 234, 0.3));
  }
  50% {
    filter: drop-shadow(0 6px 16px rgba(102, 126, 234, 0.4));
  }
}

.modal-header h2 {
  margin: 0.5rem 0;
  font-size: 1.75rem;
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
  font-size: 0.95rem;
  line-height: 1.6;
  text-align: center;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.btn-enable {
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
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-enable:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

.btn-icon {
  font-size: 1.25rem;
}

.btn-skip {
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

.btn-skip:hover {
  border-color: rgba(102, 126, 234, 0.6);
  color: #764ba2;
  background: rgba(102, 126, 234, 0.05);
  transform: translateY(-1px);
}

.skip-hint {
  text-align: center;
  color: #6b7280;
  font-size: 0.85rem;
  margin: 0;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
