# HotelFlow Frontend

Vue.js 3 frontend for the HotelFlow hotel booking system.

## Tech Stack

- Vue.js 3 (Composition API)
- Vite
- Vue Router
- Axios
- Plain CSS (no UI frameworks)

## Development

### Prerequisites

- Node.js 20+ (LTS)
- npm or yarn

### Setup

1. Install dependencies:
```bash
npm install
```

2. Create `.env` file (optional, defaults to `http://localhost:8000/api`):
```bash
cp .env.example .env
```

3. Start development server:
```bash
npm run dev
```

The app will be available at `http://localhost:3000`

## Building for Production

```bash
npm run build
```

The built files will be in the `dist` directory.

## Docker

### Development with Docker

Build and run:
```bash
docker-compose up --build
```

The frontend will be available at `http://localhost:3000`

### Production Build

The Dockerfile uses a multi-stage build:
1. Builds the Vue app with Vite
2. Serves it with nginx

## Project Structure

```
src/
  ├── config/          # Configuration files
  ├── services/        # API service layer
  ├── stores/          # State management (auth store)
  ├── router/          # Vue Router configuration
  ├── views/           # Page components
  │   ├── admin/       # Hotel admin pages
  │   ├── Login.vue
  │   ├── Register.vue
  │   ├── Search.vue
  │   ├── HotelDetail.vue
  │   └── MyBookings.vue
  ├── App.vue          # Root component
  ├── main.js          # Entry point
  └── style.css        # Global styles
```

## Features

### Authentication
- User login/register
- Hotel admin registration
- JWT token-based authentication (Laravel Sanctum)
- Role-based access control

### Guest Features
- Search hotels by city, dates, and guest count
- View hotel details
- Create booking requests
- View and manage own bookings

### Hotel Admin Features
- View bookings for their hotel
- Accept/reject booking requests
- View booking details

## API Configuration

The frontend communicates with the Laravel backend API. The base URL can be configured via:
- Environment variable: `VITE_API_BASE_URL`
- Default: `http://localhost:8000/api`

## Notes

- The frontend uses Laravel Sanctum for authentication (Bearer token)
- All API requests include the auth token automatically
- Role-based routing protects admin pages
- The booking flow stores temporary data in sessionStorage


