<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageResizer
{
    /**
     * Maximum dimensions for different image types
     */
    const MAX_COVER_IMAGE_WIDTH = 1920;
    const MAX_COVER_IMAGE_HEIGHT = 1080;
    const MAX_ROOM_IMAGE_WIDTH = 1200;
    const MAX_ROOM_IMAGE_HEIGHT = 800;
    
    /**
     * Quality for JPEG images (1-100)
     */
    const JPEG_QUALITY = 85;
    const PNG_QUALITY = 9; // 0-9, 9 is best compression

    /**
     * Resize and save an uploaded image
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $directory Storage directory (e.g., 'hotel_images', 'room_images')
     * @param string $type Image type: 'cover' or 'room'
     * @return string Relative path to the saved image (e.g., '/storage/hotel_images/xxx.jpg')
     */
    public static function resizeAndStore(UploadedFile $file, string $directory, string $type = 'room'): string
    {
        // Increase memory limit for large images
        $currentLimit = ini_get('memory_limit');
        $currentLimitBytes = self::convertToBytes($currentLimit);
        if ($currentLimitBytes < 256 * 1024 * 1024) {
            ini_set('memory_limit', '256M');
        }
        
        // Determine max dimensions based on type
        $maxWidth = $type === 'cover' ? self::MAX_COVER_IMAGE_WIDTH : self::MAX_ROOM_IMAGE_WIDTH;
        $maxHeight = $type === 'cover' ? self::MAX_COVER_IMAGE_HEIGHT : self::MAX_ROOM_IMAGE_HEIGHT;

        try {
            // Create image manager with GD driver
            $manager = new ImageManager(new Driver());
            
            // Read the uploaded image - use stream to reduce memory usage
            $image = $manager->read($file->getRealPath());
            
            // Get original dimensions
            $originalWidth = $image->width();
            $originalHeight = $image->height();
            
            // Only resize if image is larger than max dimensions
            if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
                // Resize the image maintaining aspect ratio
                // scaleDown will resize only if image is larger, maintaining aspect ratio
                $image->scaleDown($maxWidth, $maxHeight);
            }
            
            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = uniqid('img_', true) . '.' . $extension;
            $path = $directory . '/' . $filename;
            $fullPath = storage_path('app/public/' . $path);
            
            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Determine quality based on file type
            $mimeType = $file->getMimeType();
            
            // Save the resized image
            if (in_array($mimeType, ['image/jpeg', 'image/jpg'])) {
                $image->toJpeg(self::JPEG_QUALITY)->save($fullPath);
            } elseif ($mimeType === 'image/png') {
                $image->toPng()->save($fullPath);
            } elseif ($mimeType === 'image/webp') {
                $image->toWebp()->save($fullPath);
            } else {
                // Fallback: save as original format
                $image->save($fullPath);
            }
            
            // Return relative path for database storage
            return '/storage/' . $path;
            
        } catch (\Exception $e) {
            \Log::error('Image resizing failed: ' . $e->getMessage());
            
            // Fallback: save original file if resizing fails
            $path = $file->store($directory, 'public');
            return '/storage/' . $path;
        }
    }
    
    /**
     * Convert memory limit string to bytes
     */
    private static function convertToBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
}
