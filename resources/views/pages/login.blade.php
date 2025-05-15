<form method="POST" action="{{ route('login') }}">
    @csrf
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Jelszó" required />
    <button type="submit">Belépés</button>
</form>

