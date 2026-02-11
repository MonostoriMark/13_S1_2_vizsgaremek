# Swagger/OpenAPI Documentation Setup

## Overview

The Swagger/OpenAPI documentation is served directly from the Laravel backend server, independent of the frontend application.

## Access the Documentation

Once the backend server is running, you can access the API documentation at:

**Main Documentation Page:**
- `http://localhost:8000/api-docs` (or your backend server URL)
- `http://172.16.50.35:8000/api-docs` (development server)

**Direct Swagger YAML:**
- `http://localhost:8000/api/api-docs/swagger.yaml`

## Current Setup

The setup uses a manual `swagger.yaml` file that is already configured:
- **Location:** `backend/hotelflow_server/swagger.yaml`
- **View:** `backend/hotelflow_server/resources/views/api-docs.blade.php`
- **Route:** Defined in `backend/hotelflow_server/routes/web.php`

## Features

- ✅ **Backend-only**: No frontend dependencies
- ✅ **Interactive Testing**: Try API endpoints directly from the documentation
- ✅ **Auto-authentication**: Uses tokens from browser localStorage if available
- ✅ **Dynamic Server URLs**: Automatically adjusts to your backend server URL

## Updating the Documentation

To add more endpoints or update existing ones:

1. Edit `backend/hotelflow_server/swagger.yaml`
2. Follow the OpenAPI 3.0 specification
3. The changes will be reflected immediately when you refresh the documentation page

## Adding Authentication

The Swagger UI automatically includes authentication tokens from browser localStorage if available. To test authenticated endpoints:

1. Log in to your application in the same browser
2. The token will be automatically included in API requests from Swagger UI
3. You can also manually set the token in the "Authorize" button in Swagger UI

## File Structure

```
backend/hotelflow_server/
├── swagger.yaml                    # OpenAPI specification file
├── routes/
│   ├── web.php                     # Web routes (includes /api-docs route)
│   └── api.php                     # API routes (includes swagger.yaml route)
├── resources/
│   └── views/
│       └── api-docs.blade.php      # Swagger UI HTML page
└── SWAGGER_SETUP.md                # This file
```

## Next Steps

To expand the documentation:

1. Add more endpoints to `swagger.yaml`
2. Define request/response schemas
3. Add examples for better understanding
4. Document error responses
5. Add more detailed descriptions

For more information, visit: https://swagger.io/specification/
