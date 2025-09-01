@extends('layouts.admin')

@section('title', 'Profil')

@section('content')
    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Profil</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">
            <form id="profileForm">
                @csrf
                @method('PUT')

                <input type="hidden" name="id" value="{{ $profil->id }}">

                <div class="mb-3">
                    <label for="name" class="form-label">Név</label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="{{ $profil->name }}" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email cím</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="{{ $profil->email }}" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Új jelszó <small>(nem kötelező)</small></label>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Új jelszó">
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Jelszó megerősítése</label>
                    <input type="password" class="form-control" id="password_confirmation"
                           name="password_confirmation" placeholder="Új jelszó újra">
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save me-1"></i> Mentés
                </button>
            </form>
        </div>

        <div id="formMessage" class="mt-3"></div>


    </div>

@endsection

@section('scripts')
    <script type="module">
        document.getElementById('profileForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const id = formData.get('id');

            fetch(`${window.appConfig.APP_URL}admin/felhasznalok/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    const msg = document.getElementById('formMessage');
                    if (data.message) {
                        msg.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        // Frissítjük a profil nevet a topbarban
                        const profileName = document.getElementById('profile_name');
                        if (profileName) {
                            profileName.textContent = data.user.name;
                        }
                    } else if (data.errors) {
                        msg.innerHTML = `<div class="alert alert-danger">${data.errors}</div>`;
                    }
                })
                .catch(error => {
                    document.getElementById('formMessage').innerHTML =
                        `<div class="alert alert-danger">Hiba történt: ${error}</div>`;
                });
        });
    </script>
@endsection



