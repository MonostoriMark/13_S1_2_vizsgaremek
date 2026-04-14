# HotelFlow – Gyors indítás (dev)

Ez a dokumentum a teljes projekt gyors fejlesztői indításához készült (backend + frontend + DB).

## 1) Dockerrel (ajánlott)

### Előfeltételek
- Docker Desktop (Windows)

### Indítás
A projekt gyökerében:

```bash
docker-compose up --build
```

Elérhetőségek:
- **Backend API**: `http://localhost:8000`
- **Backend API prefix**: `http://localhost:8000/api`
- **Frontend**: `http://localhost:3000`
- **DB (MySQL)**: `localhost:3306` (docker-compose szerint)

### Fontos
- A gyökér `docker-compose.yml` a backendnek betölti a `backend/hotelflow_server/.env` fájlt (ha hiányzik, a konténer indulása el fog hasalni).
- A frontend konténerben a `VITE_API_BASE_URL` alapból `http://localhost:8000/api`.

## 2) Docker nélkül (lokális)

### Előfeltételek
- PHP **8.2+**
- Composer
- Node.js **20+**
- MySQL 8 (vagy MariaDB kompatibilis)

### Backend indítás (Laravel)
```bash
cd backend/hotelflow_server
composer install

# .env beállítás (lásd docs/BACKEND_DEVELOPMENT.md)
php artisan key:generate
php artisan migrate

php artisan serve --host=0.0.0.0 --port=8000
```

### Frontend indítás (Vue + Vite)
```bash
cd frontend
npm install

# opcionális: VITE_API_BASE_URL (lásd docs/FRONTEND_DEVELOPMENT.md)
npm run dev -- --host 0.0.0.0 --port 3000
```

## 3) Gyakori hibák

- **CORS / login gondok**: ellenőrizd, hogy a backend `FRONTEND_URL` és a frontend `VITE_API_BASE_URL` egymásra mutat (localhost vs LAN IP).
- **Nem mennek az email-ek**: fejlesztéskor a backend alapból `MAIL_MAILER=log` (a levelek a logba mennek).

