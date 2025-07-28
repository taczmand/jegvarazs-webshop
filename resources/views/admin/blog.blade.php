@extends('layouts.admin')

@section('content')
    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Tartalomkezelés / Blog</h2>
            @if(auth('admin')->user()->can('create-blog'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új bejegyzés</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-blogs'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Cím" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="3">
                            <option value="">Állapot (összes)</option>
                            <option value="draft">Szerkesztés alatt</option>
                            <option value="published">Élesítve</option>
                            <option value="archived">Archiválva</option>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Cím</th>
                        <th>Szerző</th>
                        <th>Státusz</th>
                        <th>Létrehozva</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a blog bejegyzések megtekintéséhez.
                </div>
            @endif
        </div>
    </div>

    <!-- Modális ablak -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="adminModalForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">Bejegyzés szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="blog_id" name="blog_id">
                        <div class="mb-3">
                            <label for="blog_title" class="form-label">Név</label>
                            <input type="text" class="form-control" id="blog_title" name="blog_title" required>
                        </div>
                        <div class="mb-3 d-none" id="exist_image_area">
                            <label for="" class="form-label">Borítókép</label>
                            <img src="" id="exist_image_url" class="img-fluid mt-2" style="max-width:300px" alt="Borítókép előnézet">
                            <button id="delBlogImage" type="button" class="btn btn-danger mt-2">Borítókép törlése</button>
                        </div>
                        <div class="mb-3 d-none" id="empty_image_area">
                            <label for="image_upload" class="form-label">Borítókép feltöltése</label>
                            <input type="file" class="form-control" id="image_upload" name="image_upload" required>
                        </div>
                        <div class="mb-3">
                            <label for="blog_content" class="form-label">Leírás</label>
                            <textarea class="form-control" id="blog_content" name="blog_content"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="blog_status" class="form-label">Állapot</label>
                            <select name="status" id="blog_status" class="form-select">
                                <option value="draft">Szerkesztés alatt</option>
                                <option value="published">Élesítve</option>
                                <option value="archived">Archiválva</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-btn" id="saveBlog">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://cdn.tiny.cloud/1/k486ypuedp01hfc64g7mn3t9rc5lp8h53a5korymr6qvuvb9/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script type="module">
        const adminModalDOM = document.getElementById('adminModal');
        const adminModal = new bootstrap.Modal(adminModalDOM);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        tinymce.init({
            selector: 'textarea#blog_content',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table'
        });

        $(document).ready(function() {
            const table = $('#adminTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.blog.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'title' },
                    { data: 'creator_name' },
                    { data: 'status' },
                    { data: 'created' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            // Szűrők beállítása

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            // Új blog bejegyzés tétel létrehozása modal megjelenítése
            $('#addButton').on('click', async function () {

                resetForm('Új blog bejegyzés létrehozása');
                $('#empty_image_area').removeClass('d-none');

                adminModal.show();
            });

            // Blog bejegyzés szerkesztése

            $('#adminTable').on('click', '.edit', async function () {

                resetForm('Blog bejegyzés szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const blog_data = await loadBlog(row_data.id);

                $('#blog_id').val(blog_data.id);
                $('#blog_title').val(blog_data.title);
                $('#blog_status').val(blog_data.status);

                tinymce.get('blog_content').setContent(blog_data.content);

                if (blog_data.featured_image) {
                    $('#exist_image_area').removeClass('d-none');
                    $('#exist_image_url').attr("src", window.appConfig.APP_URL + "storage/" + blog_data.featured_image);
                } else {
                    $('#empty_image_area').removeClass('d-none');
                }
                adminModal.show();
            });

            $('#delBlogImage').on('click', function (e) {
                const blogId = $('#blog_id').val();

                if (!confirm('Biztosan törölni szeretnéd a borítóképet?')) return;

                $.ajax({
                    url: `${window.appConfig.APP_URL}admin/blog/delete-photo`,
                    method: 'DELETE',
                    data: { id: blogId, _token: $('meta[name="csrf-token"]').attr('content') },
                    success: () => {
                        $('#exist_image_area').addClass('d-none');
                        $('#exist_image_url').attr("src", "");
                        $('#empty_image_area').removeClass('d-none');
                        showToast('Kép törölve', 'success');
                    },
                    error: () => showToast('Nem sikerült törölni a képet', 'danger')
                });
            });

            async function loadBlog(id) {
                try {
                    const response = await fetch(`{{ url('/admin/blog/fetch') }}/${id}`, {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    if (!response.ok) {
                        throw new Error('Hiba a blog lekérdezésekor');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Lekérdezési hiba:', error);
                    return [];
                }
            }

            // Blog bejegyzés mentése

            $('#saveBlog').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('adminModalForm');
                tinymce.triggerSave();
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const blogId = $('#blog_id').val();

                let url = '{{ route('admin.blog.store') }}';
                let method = 'POST';  // Alapértelmezett metódus

                if (blogId) {
                    url = `${window.appConfig.APP_URL}admin/blog/${blogId}`;  // update URL, ha van ID
                    formData.append('_method', 'PUT');  // PUT metódus jelzése
                }

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success(response) {
                        showToast(response.message || 'Sikeres!', 'success');
                        table.ajax.reload();
                        adminModal.hide();
                    },
                    error(xhr) {
                        let msg = 'Hiba!';
                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');
                    },
                    complete: () => {
                        $(this).html(originalSaveButtonHtml).prop('disabled', false);
                    }
                });

            });

            // Blog bejegyzés törlése
            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const blogId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd ezt a blog bejegyzést?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/blog') }}/${blogId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Blog bejegyzés sikeresen törölve!', 'success');
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt a blog bejegyzés törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt a blog bejegyzés törlésekor', 'danger');
                }
            });

            function resetForm(title = null) {
                $('#empty_image_area').addClass('d-none');
                $('#exist_image_area').addClass('d-none');
                $('#adminModalForm')[0].reset();
                $('#adminModalLabel').text(title);
                $('#blog_id').val('');
            }
        });
    </script>
@endsection
