<!DOCTYPE html>
<html>
<head>
    <title>Admin bejelentkezés</title>
</head>
<body>
<h1>Admin bejelentkezés</h1>

@if($errors->any())
    <div style="color:red;">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST" action="{{ route('admin.login') }}">
    @csrf
    <label>Email: <input type="email" name="email" value="david.taczman@gmail.com" required></label><br>
    <label>Jelszó: <input type="password" name="password" required></label><br>
    <label>
        <input type="checkbox" name="remember">
        Emlékezz rám
    </label>
    <button type="submit">Bejelentkezés</button>
</form>
</body>
</html>
