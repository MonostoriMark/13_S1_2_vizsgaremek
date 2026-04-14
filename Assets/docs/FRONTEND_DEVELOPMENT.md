# HotelFlow – Frontend fejlesztői dokumentáció (Vue 3 + Vite)

## Tech stack
- **Vue 3** (Composition API)
- **Vite** (dev server + build)
- **Vue Router**
- **Axios**
- **Plain CSS** (globális: `src/style.css`, komponens-szintű `<style>`)

## Projekt struktúra
`frontend/src/`:
- **Belépési pont**: `main.js`
- **Root komponens**: `App.vue`
- **Router**: `router/index.js`
- **Layoutok**: `layouts/AdminLayout.vue`, `layouts/SuperAdminLayout.vue`
- **State**: `stores/auth.js`
- **Service layer (API hívások)**: `services/*.js`
- **Oldalak**: `views/**`
- **Komponensek**: `components/**`
- **Segédek**: `utils/**`, `composables/**`

## Futtatás

### Lokális dev
```bash
cd frontend
npm install
npm run dev
```

Elérés: alapból `http://localhost:3000` (a `vite.config.js` szerint).

### Production build
```bash
npm run build
npm run preview
```

## Docker
Frontend Docker:
- **Build stage**: Node 20 (`npm ci`, `npm run build`)
- **Serve stage**: nginx (SPA routing beállítva `nginx.conf`-ban)

Gyökérből:
```bash
docker-compose up --build
```

## Konfiguráció / környezeti változók
Az API base URL a Vite env-ből jön:
- **VITE_API_BASE_URL**
  - default: `http://localhost:8000/api` (a frontend `README.md` szerint)
  - docker-compose-ben is be van állítva a frontend service-nél

Hol használjuk:
- `src/config/api.js` és/vagy `src/services/api.js` (axios base URL, interceptors)

## Auth (frontend)
- Token tárolás: jellemzően `localStorage`
- Automatikus header: axios interceptor(ok)
- Route védelem: router guardok (`router/index.js`)

## Fő funkciók (hol keresd)

### Foglalás (vendég oldal)
- Keresés: `views/Search.vue`
- Szálloda részletek + foglalás indítás: `views/HotelDetail.vue`
- Saját foglalások: `views/MyBookings.vue`

### Admin (hotel)
- Booking kezelés: `views/admin/BookingsList.vue`
- Szobák/szolgáltatások/RFID stb.: `views/admin/*`

### Számla + fizetés oldal
- Fizetési oldal (emailben kapott link): `views/Payment.vue`
- Számla API: `services/invoiceService.js`

### Super admin
- Super admin layout + oldalak: `layouts/SuperAdminLayout.vue`, `views/super-admin/*`

## UI / statikus assetek
- Favicon + title: `frontend/index.html` (pl. `Kép1.png`, “HotelFlow - Szálloda Foglalási Rendszer”)
- Logó használat:
  - Általános header/layout: `Kép1.png`
  - Számla PDF: backend oldalon külön logo (`HotelFlowLogoLIla.png`) a PDF blade-ben

## Tesztek (E2E jelleg)
`frontend/tests/*`:
- Selenium WebDriver + ChromeDriver alapú tesztek (scripts/driver).

## Hibakeresés
- API 401/403: ellenőrizd a tokent + role-okat.
- CORS / rossz API base URL: `VITE_API_BASE_URL` és backend `FRONTEND_URL` legyen összhangban.

