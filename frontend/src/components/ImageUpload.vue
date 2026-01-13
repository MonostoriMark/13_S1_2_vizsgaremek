<template>
  <div class="image-upload">
    <div
      v-if="!isUploading"
      class="upload-area"
      :class="{ 'drag-over': isDragOver, 'has-images': images.length > 0 }"
      @drop.prevent="handleDrop"
      @dragover.prevent="isDragOver = true"
      @dragleave.prevent="isDragOver = false"
      @click="triggerFileInput"
    >
      <input
        ref="fileInput"
        type="file"
        multiple
        accept="image/*"
        @change="handleFileSelect"
        style="display: none"
      />
      <div v-if="images.length === 0" class="upload-placeholder">
        <span class="upload-icon">📷</span>
        <p class="upload-text">Drag & drop images here</p>
        <p class="upload-hint">or click to browse</p>
        <p class="upload-info">Supports: JPG, PNG, GIF (Max: {{ maxSizeMB }}MB)</p>
      </div>
      <div v-else class="image-preview-grid">
        <div
          v-for="(image, index) in images"
          :key="image.id || index"
          class="image-preview-item"
        >
          <img :src="image.preview || image.url" :alt="`Preview ${index + 1}`" />
          <button
            class="remove-image"
            @click.stop="removeImage(index)"
            :disabled="isUploading"
          >
            ×
          </button>
          <div v-if="image.uploading" class="upload-progress">
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: `${image.progress || 0}%` }"></div>
            </div>
          </div>
        </div>
        <div v-if="images.length < maxFiles" class="add-more" @click.stop="triggerFileInput">
          <span class="add-icon">+</span>
          <p>Add more</p>
        </div>
      </div>
    </div>
    <div v-else class="uploading-state">
      <div class="spinner"></div>
      <p>Uploading images...</p>
    </div>
    <div v-if="error" class="error-message">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref, watch, getCurrentInstance } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  maxFiles: {
    type: Number,
    default: 10
  },
  maxSizeMB: {
    type: Number,
    default: 4
  },
  accept: {
    type: String,
    default: 'image/*'
  }
})

const emit = defineEmits(['update:modelValue', 'upload', 'remove'])

const fileInput = ref(null)
const isDragOver = ref(false)
const isUploading = ref(false)
const error = ref('')
const images = ref([...props.modelValue])

watch(() => props.modelValue, (newVal) => {
  images.value = [...newVal]
}, { deep: true })

const triggerFileInput = () => {
  if (images.value.length >= props.maxFiles) {
    error.value = `Maximum ${props.maxFiles} images allowed`
    return
  }
  fileInput.value?.click()
}

const validateFile = (file) => {
  const maxSize = props.maxSizeMB * 1024 * 1024
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']

  if (!validTypes.includes(file.type)) {
    throw new Error(`Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.`)
  }

  if (file.size > maxSize) {
    throw new Error(`File size exceeds ${props.maxSizeMB}MB limit.`)
  }

  return true
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  processFiles(files)
  event.target.value = '' // Reset input
}

const handleDrop = (event) => {
  isDragOver.value = false
  const files = Array.from(event.dataTransfer.files).filter(file => file.type.startsWith('image/'))
  processFiles(files)
}

const processFiles = async (files) => {
  error.value = ''
  
  if (images.value.length + files.length > props.maxFiles) {
    error.value = `You can only upload ${props.maxFiles} images total`
    return
  }

  const newImages = []

  for (const file of files) {
    try {
      validateFile(file)
      
      const imageObj = {
        id: `temp-${Date.now()}-${Math.random()}`,
        file,
        preview: URL.createObjectURL(file),
        uploading: false,
        progress: 0
      }

      newImages.push(imageObj)
      images.value.push(imageObj)
    } catch (err) {
      error.value = err.message
    }
  }

  emit('update:modelValue', images.value)

  // Auto-upload if upload handler is provided
  // The parent component should handle the upload via @upload event
  if (newImages.length > 0) {
    // Upload each image individually via the upload event
    for (const imageObj of newImages) {
      // Verify file exists before uploading
      if (!imageObj.file) {
        console.error('No file in imageObj:', imageObj)
        imageObj.error = 'File is missing'
        continue
      }

      imageObj.uploading = true
      imageObj.progress = 0
      
      // Emit upload event - parent component handles the actual upload
      // The parent's async handler will be called, but we can't await emit directly
      // So we'll use a promise-based approach
      const uploadPromise = new Promise((resolve, reject) => {
        // Create a wrapper that the parent can call
        imageObj._uploadPromise = { resolve, reject }
        
        // Emit the event with the imageObj
        // The parent handler should call resolve/reject when done
        emit('upload', imageObj)
        
        // Set a timeout in case parent doesn't handle it
        setTimeout(() => {
          if (imageObj.uploading && imageObj.progress < 100) {
            // If still uploading after 30 seconds, assume it failed
            reject(new Error('Upload timeout'))
          }
        }, 30000)
      })

      try {
        await uploadPromise
        imageObj.progress = 100
        imageObj.uploading = false
      } catch (err) {
        console.error('Upload error:', err)
        imageObj.uploading = false
        imageObj.error = err.message || 'Upload failed'
        // Remove the failed image from the list
        const index = images.value.findIndex(img => img.id === imageObj.id)
        if (index !== -1) {
          images.value.splice(index, 1)
          emit('update:modelValue', images.value)
        }
      }
    }
  }
}

const removeImage = (index) => {
  const image = images.value[index]
  
  // Clean up preview URL
  if (image.preview && image.preview.startsWith('blob:')) {
    URL.revokeObjectURL(image.preview)
  }

  images.value.splice(index, 1)
  emit('update:modelValue', images.value)
  emit('remove', image)
}

const uploadImages = async (imageObjs) => {
  isUploading.value = true
  error.value = ''

  try {
    for (const imageObj of imageObjs) {
      imageObj.uploading = true
      imageObj.progress = 0

      // Simulate upload progress
      const progressInterval = setInterval(() => {
        if (imageObj.progress < 90) {
          imageObj.progress += 10
        }
      }, 100)

      try {
        // Emit upload event - parent component handles the actual upload
        emit('upload', imageObj)
        // Wait a bit for the upload to complete
        await new Promise(resolve => setTimeout(resolve, 100))
        imageObj.progress = 100
        clearInterval(progressInterval)
      } catch (err) {
        clearInterval(progressInterval)
        imageObj.uploading = false
        imageObj.error = err.message || 'Upload failed'
        throw err
      } finally {
        imageObj.uploading = false
      }
    }
  } catch (err) {
    error.value = err.message || 'Failed to upload images'
  } finally {
    isUploading.value = false
  }
}

defineExpose({
  uploadImages,
  clear: () => {
    images.value.forEach(img => {
      if (img.preview && img.preview.startsWith('blob:')) {
        URL.revokeObjectURL(img.preview)
      }
    })
    images.value = []
    emit('update:modelValue', [])
  }
})
</script>

<style scoped>
.image-upload {
  width: 100%;
}

.upload-area {
  border: 2px dashed #bdc3c7;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background-color: #f8f9fa;
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-area:hover {
  border-color: #3498db;
  background-color: #e8f4f8;
}

.upload-area.drag-over {
  border-color: #3498db;
  background-color: #d6eaf8;
  transform: scale(1.02);
}

.upload-placeholder {
  width: 100%;
}

.upload-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: 1rem;
}

.upload-text {
  font-size: 1.1rem;
  font-weight: 500;
  color: #2c3e50;
  margin: 0.5rem 0;
}

.upload-hint {
  font-size: 0.9rem;
  color: #7f8c8d;
  margin: 0.25rem 0;
}

.upload-info {
  font-size: 0.85rem;
  color: #95a5a6;
  margin-top: 0.5rem;
}

.image-preview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
  width: 100%;
}

.image-preview-item {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid #e0e0e0;
  background: #f5f5f5;
}

.image-preview-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.remove-image {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background-color: rgba(231, 76, 60, 0.9);
  color: white;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  line-height: 1;
}

.remove-image:hover {
  background-color: #e74c3c;
  transform: scale(1.1);
}

.remove-image:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.upload-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: rgba(0, 0, 0, 0.7);
  padding: 0.5rem;
}

.progress-bar {
  width: 100%;
  height: 4px;
  background-color: rgba(255, 255, 255, 0.3);
  border-radius: 2px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background-color: #3498db;
  transition: width 0.3s ease;
}

.add-more {
  aspect-ratio: 1;
  border: 2px dashed #bdc3c7;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  background-color: #f8f9fa;
}

.add-more:hover {
  border-color: #3498db;
  background-color: #e8f4f8;
}

.add-icon {
  font-size: 2rem;
  color: #7f8c8d;
  margin-bottom: 0.5rem;
}

.add-more p {
  font-size: 0.85rem;
  color: #7f8c8d;
  margin: 0;
}

.uploading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #7f8c8d;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #ecf0f1;
  border-top-color: #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.error-message {
  margin-top: 1rem;
  padding: 0.75rem;
  background-color: #fee;
  color: #c0392b;
  border-radius: 6px;
  font-size: 0.9rem;
}
</style>
