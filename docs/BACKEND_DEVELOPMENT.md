# HotelFlow – Backend fejlesztői dokumentáció (Laravel)

## Tech stack
- **Laravel 12** (`backend/hotelflow_server`)
- **PHP 8.2+**
- **MySQL 8** (docker-compose)
- **Auth**: Laravel Sanctum (Bearer token)
- **PDF**: `dompdf/dompdf`
- **QR**: `endroid/qr-code` + `simplesoftwareio/simple-qrcode`
- **Képek**: `intervention/image`

## Projekt struktúra (fontosabb pontok)
- **API route-ok**: `backend/hotelflow_server/routes/api.php`
- **Controllerek**: `backend/hotelflow_server/app/Http/Controllers/`
- **Modellek**: `backend/hotelflow_server/app/Models/`
- **Mailables**: `backend/hotelflow_server/app/Mail/`
- **Email blade-ek**: `backend/hotelflow_server/resources/views/emails/`
- **Számla PDF blade**: `backend/hotelflow_server/resources/views/invoices/pdf.blade.php`
- **Migrations/Seedek**: `backend/hotelflow_server/database/`
- **Swagger**: `backend/hotelflow_server/swagger.yaml` (+ `SWAGGER_SETUP.md`)

## Futtatás

### Dockerrel
A gyökérből:

```bash
docker-compose up --build
```

### Docker nélkül (lokális)

```bash
cd backend/hotelflow_server
composer install

php artisan key:generate
php artisan migrate

php artisan serve --host=0.0.0.0 --port=8000
```

## Környezeti változók (`.env`)
Nincs repo-ban `.env.example`, ezért fejlesztéshez a minimálisan szükséges kulcsok:

### Alap Laravel
- **APP_NAME**: `HotelFlow`
- **APP_ENV**: `local`
- **APP_KEY**: (artisan generálja)
- **APP_DEBUG**: `true`
- **APP_URL**: `http://localhost:8000`

### Frontend linkek (email / redirect)
- **FRONTEND_URL**: `http://localhost:3000` *(vagy Vite esetén `http://localhost:5173`, attól függően hol fut a frontend)*

### DB (MySQL docker-compose default)
- **DB_CONNECTION**: `mysql`
- **DB_HOST**: `db` *(Docker esetén)* / `127.0.0.1` *(lokál)*
- **DB_PORT**: `3306`
- **DB_DATABASE**: `laravel`
- **DB_USERNAME**: `laravel`
- **DB_PASSWORD**: `secret`

### Mail
Fejlesztéshez a legegyszerűbb:
- **MAIL_MAILER**: `log`

SMTP-hez tipikusan:
- **MAIL_MAILER**: `smtp`
- **MAIL_HOST**, **MAIL_PORT**, **MAIL_USERNAME**, **MAIL_PASSWORD**
- **MAIL_FROM_ADDRESS**, **MAIL_FROM_NAME**

### Számla pénznem
- **INVOICE_CURRENCY**: `EUR`

Megjegyzés: a configban szerepel egy `INVOICE_EUR_TO_HUF_RATE` is, de a projekt jelenlegi iránya szerint a megjelenítés EUR-ban történik.

## Auth / jogosultságok

### Sanctum
- A védett API route-ok `auth:sanctum` middleware-rel vannak levédve.
- A frontend **Bearer** tokent küld az `Authorization` headerben.

### Szerepkörök
Több route `role:*` middleware-t használ (pl. `role:hotel`, `role:super_admin`). Ezek a szabályok az API belépési pontjain érvényesülnek.

## Fő üzleti folyamatok (hol keresd)

### Foglalás
- **Foglalás létrehozás / listázás / státusz**: `BookingController`
- **Vendégek kezelése**: `GuestController` + booking guest route-ok

### Számla (PDF + email)
- **Számla életciklus** (létrehozás / approve / send / letöltés): `InvoiceController`
- **PDF generálás**: `resources/views/invoices/pdf.blade.php`
- **Email sablonok**: `resources/views/emails/`
- **Külön email kártya vs átutalás**: `InvoiceMail`, `InvoiceBankTransferMail`

### Fizetés (tokenes, publikus)
- **Publikus végpontok**: `GET/POST /api/invoices/payment/{token}*` (`InvoiceController`)
- A token a számlához van kötve (payment link).

### Check-in QR
- A visszaigazoló email(ek) és a QR generálás a `Mail` rétegben + foglalás/fizetés megerősítés folyamatban kapcsolódik össze.

## Swagger / API dokumentáció
- YAML: `backend/hotelflow_server/swagger.yaml`
- Szervírozás: `GET /api/api-docs/swagger.yaml` (route `routes/api.php` elején)

## Naplózás és hibakeresés
- Logok: `backend/hotelflow_server/storage/logs/laravel.log`
- Email `MAIL_MAILER=log` esetén a kimenet is a logban landol.

## Hasznos parancsok
```bash
php artisan migrate:fresh --seed
php artisan cache:clear
php artisan config:clear
php artisan route:list
```

