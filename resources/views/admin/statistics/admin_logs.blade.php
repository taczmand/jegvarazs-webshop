@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Jelentések / Admin tevékenységek</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-admin-logs'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Felhasználónév" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Model" class="filter-input form-control" data-column="2">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Akció" class="filter-input form-control" data-column="3">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Adat" class="filter-input form-control" data-column="4">
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Felhasználónév</th>
                        <th data-priority="2">Model</th>
                        <th data-priority="3">Akció</th>
                        <th data-priority="4">Adat</th>
                        <th data-priority="5">Időpont</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod az admin tevékenységek megtekintésére.
                </div>
            @endif
        </div>
    </div>


@endsection

@section('scripts')
    <script type="module">

        document.addEventListener('DOMContentLoaded', () => {
            initCrud({
                tableId: 'adminTable',
                dataUrl: '{{ route('admin.stats.admin_logs.data') }}',
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                columns: [
                    { data: 'id' },
                    { data: 'user_name' },
                    { data: 'model' },
                    { data: 'action' },
                    {
                        data: 'data',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                const stringData = typeof data === 'string' ? data : JSON.stringify(data ?? '');
                                const shortText = stringData.length > 50 ? stringData.substring(0, 47) + '...' : stringData;

                                return `<span title="${stringData}">${shortText}</span>`;
                            }
                            return data;
                        }
                    },
                    { data: 'created_at' }
                ]
            });
        });


    </script>
@endsection
