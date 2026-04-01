@extends('layouts.admin_minimal')

@section('title', 'Havi km rögzítés')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="color-dark-blue mb-0">Havi km rögzítés</h2>
    </div>

    <div class="rounded-xl bg-white shadow-lg p-4">
        @if(($vehicles ?? collect())->count() === 0)
            <div class="alert alert-info mb-0">Nincs hozzárendelt járműved.</div>
        @else
            <form id="vehicleKmForm">
                <div class="row g-3">
                    @foreach($vehicles as $vehicle)
                        <div class="col-12">
                            <div class="border rounded p-3 d-flex align-items-center justify-content-between gap-3 flex-wrap">
                                <div class="fw-semibold">{{ $vehicle->license_plate }}</div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="text-muted small">Jelenlegi: {{ $vehicle->current_odometer !== null ? (int) $vehicle->current_odometer : '-' }}</div>
                                    <input type="number" class="form-control" style="width: 180px" name="kms[{{ $vehicle->id }}]" min="{{ $vehicle->current_odometer !== null ? (int) $vehicle->current_odometer : 0 }}" step="1" placeholder="Km óra állás">
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100" id="vehicleKmSaveBtn">Mentés</button>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('vehicleKmForm');
            if (!form) return;

            const btn = document.getElementById('vehicleKmSaveBtn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Mentés...';
                }

                try {
                    const formData = new FormData(form);
                    const res = await fetch(`{{ url('/admin/jarmuvek/km') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    const payload = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        let msg = payload?.message || 'Hiba!';
                        if (payload?.errors) {
                            msg = Object.values(payload.errors).flat().join(' ');
                        }
                        showToast(msg, 'danger');
                        return;
                    }

                    showToast(payload?.message || 'Sikeres mentés!', 'success');
                    window.location.href = `{{ route('admin.dashboard') }}`;
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                } finally {
                    if (btn) {
                        btn.disabled = false;
                        btn.textContent = 'Mentés';
                    }
                }
            });
        });
    </script>
@endsection
