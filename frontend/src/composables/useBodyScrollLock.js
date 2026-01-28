import { watch, onUnmounted } from 'vue'

/**
 * Composable to lock/unlock body scroll when modals are open
 * @param {Ref|Array<Ref>} modalStates - Single ref or array of refs that control modal visibility
 */
export function useBodyScrollLock(modalStates) {
  const updateScrollLock = () => {
    const states = Array.isArray(modalStates) ? modalStates : [modalStates]
    const isAnyModalOpen = states.some(state => state.value === true)
    
    if (isAnyModalOpen) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }
  }

  // Watch all modal states
  if (Array.isArray(modalStates)) {
    modalStates.forEach(state => {
      watch(state, updateScrollLock, { immediate: true })
    })
  } else {
    watch(modalStates, updateScrollLock, { immediate: true })
  }

  // Cleanup on unmount
  onUnmounted(() => {
    document.body.style.overflow = ''
  })

  return { updateScrollLock }
}
