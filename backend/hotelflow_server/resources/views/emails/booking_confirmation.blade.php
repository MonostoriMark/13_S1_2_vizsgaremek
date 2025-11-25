<h2>Kedves {{ $booking->user->name }}!</h2>

<p>Köszönjük a foglalását a 
   <strong>{{ $booking->rooms->first()->hotel->name }}</strong> szállodában!</p>

<p><strong>Érkezés:</strong> {{ $booking->startDate }}</p>
<p><strong>Távozás:</strong> {{ $booking->endDate }}</p>

<p><strong>Foglalt szobák:</strong></p>
<ul>
@foreach($booking->rooms as $room)
    <li>{{ $room->name }} ({{ $room->capacity }} fős)</li>
@endforeach
</ul>

<p><strong>Szolgáltatások:</strong></p>
<ul>
@foreach($booking->services as $service)
    <li>{{ $service->name }} – {{ $service->price }} Ft</li>
@endforeach
</ul>
<p>Az alábbi QR-kódot mutasd fel a check-in során:</p>

<img src="{{ $message->embed($qrPath) }}" alt="QR Code" style="width:300px;height:300px;" />
<p>Üdv,<br>HotelFlow rendszer</p>
