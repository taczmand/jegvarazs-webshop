@extends('layouts.admin')

@section('content')
    <div class="container p-0">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyviteli folyamatok / Partner ajánlatok</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">
            <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                <div class="filter-group">
                    <i class="fa-solid fa-filter text-gray-500"></i>
                </div>

                <div class="filter-group flex-grow-1 flex-md-shrink-0">
                    <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                </div>
                <div class="filter-group flex-grow-1 flex-md-shrink-0">
                    <input type="text" placeholder="Partner" class="filter-input form-control" data-column="1">
                </div>
                <div class="filter-group flex-grow-1 flex-md-shrink-0">
                    <input type="text" placeholder="Cím" class="filter-input form-control" data-column="2">
                </div>
                <div class="filter-group flex-grow-1 flex-md-shrink-0">
                    <input type="text" placeholder="Címzett e-mail" class="filter-input form-control" data-column="3">
                </div>
                <div class="filter-group flex-grow-1 flex-md-shrink-0">
                    <input type="text" placeholder="Küldve" class="filter-input form-control" data-column="4">
                </div>
                <div class="filter-group flex-grow-1 flex-md-shrink-0">
                    <input type="text" placeholder="Létrehozva" class="filter-input form-control" data-column="5">
                </div>
            </div>

            <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Partner</th>
                    <th>Cím</th>
                    <th>Címzett e-mail</th>
                    <th>Küldve</th>
                    <th>Létrehozva</th>
                    <th>Műveletek</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        $(document).ready(function() {
            const table = $('#adminTable').DataTable({
                language: { url: '/lang/datatables/hu.json' },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.partner-offers.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'customer', name: 'customer' },
                    { data: 'title' },
                    { data: 'recipient_email' },
                    { data: 'sent_at' },
                    { data: 'created_at' },
                    { data: 'action', orderable: false, searchable: false },
                ],
            });

            $('.filter-input').on('keyup change', function () {
                const columnIndex = $(this).data('column');
                table.column(columnIndex).search(this.value).draw();
            });
        });
    </script>
@endsection
