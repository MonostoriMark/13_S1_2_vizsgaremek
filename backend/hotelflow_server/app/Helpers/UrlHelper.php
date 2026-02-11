<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class UrlHelper
{
    /**
     * Get the frontend URL dynamically based on the current request
     * Uses the current request's scheme and host, with the frontend port
     * 
     * @param string|null $path Optional path to append
     * @return string
     */
    public static function getFrontendUrl(?string $path = null): string
    {
        // Try to get from current request
        $request = request();
        
        if ($request && $request->getSchemeAndHttpHost()) {
            // Get the current request's scheme and host
            $scheme = $request->getScheme(); // http or https
            $host = $request->getHost(); // IP or domain (without port)
            
            // If host contains port, extract just the host part
            if (strpos($host, ':') !== false) {
                $host = explode(':', $host)[0];
            }
            
            // Get frontend port from env or use default (3000 for Vite dev server)
            // You can set FRONTEND_PORT in .env file if different
            $frontendPort = env('FRONTEND_PORT', '3000');
            
            // Build frontend URL with the same scheme and host, but frontend port
            $frontendUrl = $scheme . '://' . $host . ':' . $frontendPort;
        } else {
            // Fallback to config if no request is available
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        }
        
        // Append path if provided
        if ($path) {
            $path = ltrim($path, '/');
            return $frontendUrl . '/' . $path;
        }
        
        return $frontendUrl;
    }
}
