@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Jelentések / Szerelések</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-installations'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Név" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="E-mail" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Telefonszám" class="filter-input form-control" data-column="2">
                    </div>

                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th data-priority="1">Név</th>
                        <th data-priority="3">E-mail cím</th>
                        <th data-priority="4">Telefonszám</th>
                        <th data-priority="5">Cím</th>
                        <th data-priority="6">Szerelés dátuma</th>
                        <th data-priority="7">Utolsó karbantartás</th>
                        <th data-priority="2">Utolsó karbantartás óta eltelt idő</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a szerelések megtekintésére.
                </div>
            @endif
        </div>
    </div>


@endsection

@section('scripts')
    <script type="module">

        $(document).ready(function() {
            const table = $('#adminTable').DataTable({
                language: {
                    url: '/lang/datatables/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.stats.installations.data') }}',
                order: [[0, 'asc']],
                columns: [
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'phone' },
                    { data: 'address' },
                    { data: 'installation_date' },
                    { data: 'last_maintenance_date' },
                    {
                        data: 'days_since_service',
                        title: 'Eltelt napok',
                        render: function (data, type) {
                            if (type !== 'display') return data;

                            let color = 'text-success';
                            if (data > 365) color = 'text-danger fw-bold';
                            else if (data > 180) color = 'text-warning fw-bold';

                            return `<span class="${color}">${data} nap</span>`;
                        }
                    }
                ]
            });

            // Szűrők beállítása

            $('.filter-input').on('change keyup', function () {
                var i = $(this).attr('data-column');
                var v = $(this).val();
                table.columns(i).search(v).draw();
            });
        });


    </script>
@endsection

