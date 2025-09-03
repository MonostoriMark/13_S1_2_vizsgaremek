# Szabó Máté | Monostori Márk György 
**Szállodai foglaló, számlázó és adminisztrációs rendszer**

Az alkalmazásunk feladata a szállodák számára biztosítani egy egyszerű, érthető felületet, amelyen feltölthetik az általuk kínált szobákat, szolgáltatásokat és eszközöket. Emellett lehetőség van vendégeknek regisztrálni, így foglalhatnak is az adott szállodánál. Végül, a szállodák adminisztrációs költségeit csökkentve, egy automatizált számla készítő rendszert is integrálunk az alkalmazásba, amely képes kommunikálni a Nemzeti Adó-és Vámhivatallal.


# Szálloda Foglalási Rendszer  
## Projektfeladat specifikáció  

**Informatikai Biztonsági és**  
**Adatvédelmi Tanácsadó Kft.**

---

## 1. Tartalomjegyzék

1. Tartalomjegyzék ...... 2  
2. Bevezetés ...... 3  
   - 2.1 A feladat címe  
   - 2.2 A feladat rövid ismertetése  
3. Elvárások a feladattal kapcsolatban ...... 4  
   - 3.1 Operációs rendszer, környezet  
   - 3.2 Felhasználandó technológiák  
   - 3.3 Megoldás formátuma  
   - 3.4 Szoftverfejlesztés  
   - 3.5 Modulok  
4. Szoftver specifikáció ...... 6  
   - 4.1 Megjelenés  
   - 4.2 Funkciók  
5. Dokumentáció ...... 7  
   - 5.1 Erőforrás-terv, munkaidő nyilvántartás  
   - 5.2 Technikai dokumentáció  
   - 5.3 Forráskód dokumentáció  
   - 5.4 Felhasználói dokumentáció  
6. A projekt értékelése ...... 8  
   - 6.1 Felhasználói oldali szempontok  
   - 6.2 Technikai szempontok  
   - 6.3 Piaci értékelés  
7. Projekt adatlap ...... 9  

---

## 2. Bevezetés

### 2.1 A feladat címe  
**HotelFlow** – Szálloda Foglalási Rendszer

### 2.2 A feladat rövid ismertetése  
A HotelFlow egy modern, biztonságos és felhasználóbarát online szálloda-foglalási platform, amely lehetővé teszi a vendégek számára, hogy könnyedén keressenek és foglaljanak szobákat, valamint a szállodák számára, hogy hatékonyan kezeljék foglalásaikat, számlázzanak és integrálódjanak külső rendszerekkel (pl. NAV, fizetési gateway-ek). A rendszer tartalmaz egy hardveres RFID-alapú beléptetési modult is, amely egyszerűsíti a check-in és check-out folyamatokat.

---

## 3. Elvárások a feladattal kapcsolatban

### 3.1 Operációs rendszer, környezet  
- Windows, Linux, macOS (többplatformos)
- Docker támogatás

### 3.2 Felhasználandó technológiák  
- Backend: Django (Python)  
- Frontend: Vue.js  
- Autentikáció: JWT  
- Adatbázis: PostgreSQL  
- Fizetési integráció: Stripe, OTP Simple  
- RFID eszköz integráció: Python-szerver oldali kód  

### 3.3 Megoldás formátuma  
- Teljes projekt környezet (Git repository)  
- Forráskód dokumentáció  
- Technikai dokumentáció (ODT és PDF)  
- Erőforrás-terv és munkaidő-nyilvántartás  
- Felhasználói dokumentáció  

### 3.4 Szoftverfejlesztés  
A feladat egy olyan webalkalmazás készítése, amelyben a vendégek regisztrálhatnak, kereshetnek szállodák között, foglalhatnak, és a szállodák adminisztrálhatják szobáikat, áraikat és foglalásaikat. A rendszer támogatja az online fizetést, számlagenerálást és RFID-alapú azonosítást.

### 3.5 Modulok  
- Felhasználói regisztráció és hitelesítés (vendég, szálloda, admin)  
- Keresőmodul (dátum, város, vendégek száma)  
- Foglalási és fizetési rendszer  
- Számlagenerálás (NAV integráció)  
- Admin felület szobák, szolgáltatások, árak kezelésére  
- RFID check-in/check-out modul  
- Statisztikai és jelentéskészítő modul  

---

## 4. Szoftver specifikáció

### 4.1 Megjelenés  
- Reszponzív, modern kinézet  
- Intuitív kezelőfelület  
- Személyre szabható szállodai oldalak  

### 4.2 Funkciók  
- Regisztráció és profilkezelés  
- Keresés és szűrés  
- Foglalás és online fizetés  
- Számlagenerálás  
- RFID-es beléptetés  
- Admin felület teljes körű kezeléshez  
- E-mail értesítések  

---

## 5. Dokumentáció

### 5.1 Erőforrás-terv, munkaidő nyilvántartás  
- Részfeladatok, felelősök, időbecslések  
- Tényleges munkaidő nyilvántartása  

### 5.2 Technikai dokumentáció  
- Rendszerarchitektúra  
- API dokumentáció  
- Telepítési útmutató  

### 5.3 Forráskód dokumentáció  
- Kommentek, osztályleírások  
- Funkciónkénti magyarázat  

### 5.4 Felhasználói dokumentáció  
- Képernyőképes útmutató  
- Részletes funkcióismertetés  

---

## 6. A projekt értékelése

### 6.1 Felhasználói oldali szempontok  
- Funkcionalitás  
- Felhasználói élmény  
- Megbízhatóság  

### 6.2 Technikai szempontok  
- Kódminőség  
- Dokumentáció  
- Tesztelés  

### 6.3 Piaci értékelés  
- Hatékonyság  
- Skálázhatóság  
- Piaci alkalmasság  

---

## 7. Projekt adatlap

**Projekt neve:** HotelFlow – Szálloda Foglalási Rendszer  
**Feladat ismertetése:** Online foglalási platform szállodák és vendégek számára  
**Technológia:** Django, Vue.js, JWT, PostgreSQL, Stripe, RFID  
**Készítette:** [Csapat neve]  
**Dátum:** 2025.09.03.  

---

Ez a specifikáció a mellékelt PDF logikáját és szerkezetét követi, és a HotelFlow projektre szabott. Ha szükséges, bővítheted további részekkel.
