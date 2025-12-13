@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Jelentések / Kapcsolatok</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-contacts'))

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

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Típus" class="filter-input form-control" data-column="3">
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th data-priority="1">Név</th>
                        <th data-priority="2">E-mail</th>
                        <th data-priority="3">Telefonszám</th>
                        <th data-priority="4">Típus</th>
                    </tr>
                    </thead>
                </table>

                <div id="emails-list" style="margin-top:10px; font-weight:bold; background-color: #f5f5f5; padding: 10px; border-radius: 5px;"></div>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a kapcsolatok megtekintésére.
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
                ajax: '{{ route('admin.stats.contacts.data') }}',
                order: [[0, 'asc']],
                columns: [
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'phone' },
                    { data: 'types' }
                ],
                drawCallback: function(settings) {
                    // Összegyűjtjük a látható email címeket
                    var emails = table.column(1, { page: 'current' })
                        .data()
                        .toArray()
                        .filter(email => email) // üres mezők kiszűrése
                        .map(email => email.trim())
                        .join('; ');

                    // Megjelenítjük a divben
                    $('#emails-list').text(emails);
                }
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

