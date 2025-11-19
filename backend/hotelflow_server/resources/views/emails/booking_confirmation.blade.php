<h2>Kedves {{ $booking->user->name }}!</h2>

<p>Köszönjük a foglalását!</p>

<p><strong>Érkezés:</strong> {{ $booking->startDate }}</p>
<p><strong>Távozás:</strong> {{ $booking->endDate }}</p>

<p>Az alábbi QR-kódot mutasd fel a check-in során:</p>

<img src="data:image/png;base64,{{ $qrBase64 }}" alt="QR Code" />

<p>Üdv,<br>Hotel rendszer</p>
