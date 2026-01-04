# Implementation Notes

## Completed Features

### Authentication
- ✅ Login page
- ✅ Registration page (user and hotel admin)
- ✅ JWT token handling (Laravel Sanctum Bearer tokens)
- ✅ Logout functionality
- ✅ Token storage in localStorage
- ✅ Automatic token attachment to API requests

### Guest Functionality
- ✅ Search page for hotels (city, date range, guests)
- ✅ Display search results with available plans
- ✅ Hotel detail page
- ✅ Create booking request
- ✅ List user's own bookings
- ✅ Cancel bookings

### Hotel Admin Functionality
- ✅ List bookings for their hotel
- ✅ Accept/reject booking requests
- ✅ View booking details

### Routing
- ✅ Role-based route protection
- ✅ Redirect unauthenticated users to login
- ✅ Redirect authenticated users away from login/register

## Known Limitations

### Hotel Admin Bookings
The `/devices/bookings/{hotelId}` endpoint only returns **confirmed** bookings. This means:
- Hotel admins can see confirmed bookings
- Pending bookings are not visible through this endpoint
- The `updateStatus` endpoint can still be used to accept/reject bookings if the booking ID is known

**Recommended Backend Extension:**
Create a new endpoint `/bookings/hotel/{hotelId}` that returns all bookings (pending, confirmed, cancelled, completed) for a hotel, with proper authentication and authorization checks.

Example implementation:
```php
Route::get('/bookings/hotel/{hotelId}', [BookingController::class, 'getBookingsByHotelId'])
    ->middleware('auth:sanctum', 'role:hotel');
```

## API Endpoints Used

### Authentication
- `POST /api/auth/login`
- `POST /api/auth/register-user`
- `POST /api/auth/register-hotel`
- `POST /api/auth/logout`
- `GET /api/auth/me`

### Hotels
- `GET /api/hotels`
- `GET /api/hotels/{id}`
- `GET /api/rooms/hotel/{hotel_id}`

### Search
- `GET /api/search?city=...&startDate=...&endDate=...&guests=...`

### Bookings
- `POST /api/bookings`
- `GET /api/bookings/user/{userId}`
- `DELETE /api/bookings/delete/{id}`
- `PUT /api/bookings/update-status/{id}`
- `GET /api/devices/bookings/{hotelId}` (hotel admin - confirmed only)

## Environment Variables

- `VITE_API_BASE_URL` - Backend API base URL (default: `http://localhost:8000/api`)

## Docker Configuration

The frontend is containerized with:
- Multi-stage Dockerfile (build with Node, serve with nginx)
- nginx configuration for SPA routing
- Port 80 exposed (mapped to 3000 in docker-compose)

## Development vs Production

### Development
- Run `npm run dev` for hot-reload development server
- API calls go to `http://localhost:8000/api` (or configured URL)

### Production
- Build with `npm run build`
- Serve static files with nginx
- Configure `VITE_API_BASE_URL` to point to production backend

## Next Steps (Future Enhancements)

1. **Backend Extension**: Add endpoint for hotel admins to see all bookings
2. **Error Handling**: More comprehensive error messages and retry logic
3. **Loading States**: Better loading indicators
4. **Form Validation**: Client-side validation for better UX
5. **Booking Details**: More detailed booking view with guest information
6. **Search Filters**: Additional search filters (price range, amenities, etc.)
7. **Pagination**: For long lists of bookings/hotels


