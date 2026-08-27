@extends('layouts.admin')

@section('content')
    <div class="container p-0">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyviteli folyamatok / Dokumentumok</h2>
            @if(auth('admin')->user()->can('create-documents'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új dokumentum feltöltése</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">
            @if(auth('admin')->user()->can('view-documents'))
                <div id="documentsGrid" class="row g-4">
                    <!-- Documents will be loaded here via JavaScript -->
                </div>
                <div id="documentsLoader" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Betöltés...</span>
                    </div>
                </div>
                <div id="documentsEmpty" class="text-center py-5 d-none">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Még nincsenek dokumentumok feltöltve.</p>
                </div>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultsága a dokumentumok megtekintéséhez.
                </div>
            @endif
        </div>
    </div>

    <!-- Modal for create/edit -->
    <div class="modal fade admin-modal-soft" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="documentForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-custom">
                        <h5 class="modal-title" id="documentModalLabel">Új dokumentum feltöltése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="document_id" name="id">

                        <div class="mb-3">
                            <label for="title" class="form-label">Cím <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Leírás</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Fájl <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="file" name="file" accept="*/*">
                            <div class="form-text">Maximális fájlméret: 100 MB</div>
                        </div>

                        <div id="currentFileInfo" class="mb-3 d-none">
                            <label class="form-label">Jelenlegi fájl:</label>
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file fa-2x text-muted me-3"></i>
                                        <div>
                                            <div id="currentFileName" class="fw-bold"></div>
                                            <div id="currentFileSize" class="text-muted small"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Mégse</button>
                        <button type="submit" class="btn btn-primary" id="saveButton">
                            <i class="fas fa-save me-1"></i> Mentés
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete confirmation modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Törlés megerősítése</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                <div class="modal-body">
                    <p>Biztosan törlöd ezt a dokumentumot?</p>
                    <input type="hidden" id="deleteDocumentId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash me-1"></i> Törlés
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let documents = [];

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            function getFileIcon(fileType) {
                if (!fileType) return 'fa-file';

                if (fileType.includes('pdf')) return 'fa-file-pdf text-danger';
                if (fileType.includes('word') || fileType.includes('doc')) return 'fa-file-word text-primary';
                if (fileType.includes('excel') || fileType.includes('sheet')) return 'fa-file-excel text-success';
                if (fileType.includes('powerpoint') || fileType.includes('presentation')) return 'fa-file-powerpoint text-warning';
                if (fileType.includes('image')) return 'fa-file-image text-info';
                if (fileType.includes('video')) return 'fa-file-video text-danger';
                if (fileType.includes('audio')) return 'fa-file-audio text-secondary';
                if (fileType.includes('zip') || fileType.includes('rar') || fileType.includes('archive')) return 'fa-file-zipper text-warning';
                if (fileType.includes('text')) return 'fa-file-lines text-muted';

                return 'fa-file text-secondary';
            }

            function loadDocuments() {
                $('#documentsLoader').removeClass('d-none');
                $('#documentsGrid').addClass('d-none');
                $('#documentsEmpty').addClass('d-none');

                $.ajax({
                    url: '{{ route("admin.documents.data") }}',
                    method: 'GET',
                    success: function(response) {
                        documents = response;
                        renderDocuments();
                    },
                    error: function(xhr) {
                        showToast('Hiba a dokumentumok betöltésekor: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                        $('#documentsLoader').addClass('d-none');
                        $('#documentsEmpty').removeClass('d-none');
                    }
                });
            }

            function renderDocuments() {
                $('#documentsLoader').addClass('d-none');

                if (documents.length === 0) {
                    $('#documentsEmpty').removeClass('d-none');
                    $('#documentsGrid').addClass('d-none');
                    return;
                }

                $('#documentsEmpty').addClass('d-none');
                $('#documentsGrid').removeClass('d-none');

                const grid = $('#documentsGrid');
                grid.empty();

                documents.forEach(doc => {
                    const fileIcon = getFileIcon(doc.file_type);
                    const isImage = doc.file_type && doc.file_type.includes('image');
                    const canEdit = {{ auth('admin')->user()->can('edit-documents') ? 'true' : 'false' }};
                    const canDelete = {{ auth('admin')->user()->can('delete-documents') ? 'true' : 'false' }};

                    const card = `
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card h-100 document-card" data-id="${doc.id}">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3">
                                        <a href="${doc.file_url}" target="_blank" class="file-icon-wrapper me-3 text-decoration-none">
                                            ${isImage && doc.file_url ?
                                                `<img src="${doc.file_url}" alt="${doc.title}" class="document-thumbnail" />` :
                                                `<i class="fas ${fileIcon} fa-2x"></i>`
                                            }
                                        </a>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <a href="${doc.file_url}" target="_blank" class="card-title mb-1 text-truncate text-decoration-none text-dark" title="${doc.title}">${doc.title}</a>
                                            <small class="text-muted d-block text-truncate" title="${doc.file_name}">${doc.file_name}</small>
                                        </div>
                                    </div>

                                    ${doc.description ? `<p class="card-text small text-muted mb-2 text-truncate" title="${doc.description}">${doc.description}</p>` : ''}

                                    <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
                                        <span><i class="fas fa-database me-1"></i>${formatFileSize(doc.file_size)}</span>
                                        <span><i class="fas fa-calendar me-1"></i>${new Date(doc.created_at).toLocaleDateString('hu-HU')}</span>
                                    </div>

                                    <div class="btn-group w-100" role="group">
                                        ${canEdit ? `
                                            <button class="btn btn-outline-secondary btn-sm edit-btn" data-id="${doc.id}" title="Szerkesztés">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        ` : ''}
                                        ${canDelete ? `
                                            <button class="btn btn-outline-danger btn-sm delete-btn" data-id="${doc.id}" title="Törlés">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    grid.append(card);
                });
            }

            // Add button click
            $('#addButton').click(function() {
                $('#documentModalLabel').text('Új dokumentum feltöltése');
                $('#documentForm')[0].reset();
                $('#document_id').val('');
                $('#currentFileInfo').addClass('d-none');
                $('#file').prop('required', true);
                $('#documentModal').modal('show');
            });

            // Edit button click
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                const doc = documents.find(d => d.id === id);

                if (doc) {
                    $('#documentModalLabel').text('Dokumentum szerkesztése');
                    $('#document_id').val(doc.id);
                    $('#title').val(doc.title);
                    $('#description').val(doc.description || '');
                    $('#file').prop('required', false);

                    $('#currentFileInfo').removeClass('d-none');
                    $('#currentFileName').text(doc.file_name);
                    $('#currentFileSize').text(formatFileSize(doc.file_size));

                    $('#documentModal').modal('show');
                }
            });

            // Delete button click
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                $('#deleteDocumentId').val(id);
                $('#deleteModal').modal('show');
            });

            // Confirm delete
            $('#confirmDeleteBtn').click(function() {
                const id = $('#deleteDocumentId').val();

                $.ajax({
                    url: '{{ route("admin.documents.destroy", ":id") }}'.replace(':id', id),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showToast(response.message, 'success');
                        $('#deleteModal').modal('hide');
                        loadDocuments();
                    },
                    error: function(xhr) {
                        showToast('Hiba a törlés során: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                    }
                });
            });

            // Form submit
            $('#documentForm').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const id = $('#document_id').val();
                const url = id ?
                    '{{ route("admin.documents.update", ":id") }}'.replace(':id', id) :
                    '{{ route("admin.documents.store") }}';
                const method = id ? 'PUT' : 'POST';

                // For PUT method, we need to use _method
                if (method === 'PUT') {
                    formData.append('_method', 'PUT');
                }

                $('#saveButton').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Mentés...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showToast(response.message, 'success');
                        $('#documentModal').modal('hide');
                        loadDocuments();
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        let errorMessage = 'Hiba a mentés során: ';

                        if (Object.keys(errors).length > 0) {
                            errorMessage += Object.values(errors).flat().join(', ');
                        } else {
                            errorMessage += xhr.responseJSON?.message || xhr.statusText;
                        }

                        showToast(errorMessage, 'danger');
                    },
                    complete: function() {
                        $('#saveButton').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Mentés');
                    }
                });
            });

            // Load documents on page load
            loadDocuments();
        });
    </script>

    <style>
        .document-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e0e0e0;
        }

        .document-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .file-icon-wrapper {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .document-thumbnail {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 4px;
        }

        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@endsection
