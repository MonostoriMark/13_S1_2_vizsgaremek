import { API_BASE_URL } from '../config/api'

/**
 * Get full image URL from relative path stored in database
 * @param {string} imagePath - Relative path like /storage/hotel_images/xxx.jpg
 * @returns {string|null} Full URL or null if no path provided
 */
export function getImageUrl(imagePath) {
  if (!imagePath) return null
  
  // If already a full URL, return as is
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath
  }
  
  // If relative path starting with /storage/, construct full URL
  if (imagePath.startsWith('/storage/')) {
    const baseUrl = API_BASE_URL.replace('/api', '') || 'http://localhost:8000'
    return `${baseUrl}${imagePath}`
  }
  
  // Return as is if it's already a valid path
  return imagePath
}

/**
 * Get hotel cover image or fallback to first room image
 * @param {Object} hotel - Hotel object with cover_image and rooms
 * @param {string} fallback - Fallback image URL
 * @returns {string} Image URL
 */
export function getHotelCoverImage(hotel, fallback) {
  if (!hotel) return fallback
  
  // First priority: hotel cover_image
  if (hotel.cover_image) {
    const url = getImageUrl(hotel.cover_image)
    if (url) return url
  }
  
  // Second priority: first room's first image
  if (hotel.rooms && hotel.rooms.length > 0) {
    for (const room of hotel.rooms) {
      if (room.images && room.images.length > 0) {
        const url = getImageUrl(room.images[0].url)
        if (url) return url
      }
    }
  }
  
  return fallback
}

/**
 * Get room images
 * @param {Object} room - Room object with images array
 * @returns {Array} Array of image URLs
 */
export function getRoomImages(room) {
  if (!room || !room.images || !Array.isArray(room.images)) {
    return []
  }
  
  return room.images
    .map(img => getImageUrl(img.url))
    .filter(url => url !== null)
}
