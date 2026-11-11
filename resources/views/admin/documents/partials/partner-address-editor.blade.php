            function buildPartnerAddressForm({ source = '', pid = '', aid = '', name = '', tax = '', country = 'HU', zip = '', city = '', line = '' } = {}) {
                const countryOptions = $('#new_client_country').html() || '<option value="HU">Magyarország</option>';
                const $form = $(`
                    <div class="partner-address-form border rounded p-2 my-2 bg-light"
                        data-source="${escapeHtml(source)}"
                        data-pid="${escapeHtml(pid)}"
                        data-aid="${escapeHtml(aid)}">
                        <div class="row g-2">
                            <div class="col-12 col-md-7">
                                <input type="text" class="form-control form-control-sm" data-field="name" placeholder="Név" maxlength="255" autocomplete="off">
                            </div>
                            <div class="col-12 col-md-5">
                                <input type="text" class="form-control form-control-sm" data-field="tax_number" placeholder="Adószám" maxlength="50" autocomplete="off">
                            </div>
                            <div class="col-12 col-md-4">
                                <select class="form-select form-select-sm" data-field="country">${countryOptions}</select>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="text" class="form-control form-control-sm" data-field="zip_code" placeholder="Irányítószám*" maxlength="20" autocomplete="off">
                            </div>
                            <div class="col-6 col-md-5">
                                <input type="text" class="form-control form-control-sm" data-field="city" placeholder="Város*" maxlength="100" autocomplete="off">
                            </div>
                            <div class="col-12">
                                <input type="text" class="form-control form-control-sm" data-field="address_line" placeholder="Cím*" maxlength="255" autocomplete="off">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary partner-address-cancel">Mégse</button>
                            <button type="button" class="btn btn-sm btn-primary partner-address-save">Mentés</button>
                        </div>
                    </div>
                `);

                $form.find('[data-field="name"]').val(name || '');
                $form.find('[data-field="tax_number"]').val(tax || '');
                $form.find('[data-field="zip_code"]').val(zip || '');
                $form.find('[data-field="city"]').val(city || '');
                $form.find('[data-field="address_line"]').val(line || '');

                const $country = $form.find('[data-field="country"]');
                $country.val(country || 'HU');
                if (!$country.val()) {
                    $country.val('HU');
                }

                return $form;
            }

            function closePartnerAddressForms() {
                $('#partner_select_results .partner-address-form').remove();
                $('#partner_select_results .client-address-item').addClass('d-flex').show();
            }

            $('#partner_select_results').on('click', '.partner-address-edit', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const $row = $(this).closest('.client-address-item');
                closePartnerAddressForms();

                const $form = buildPartnerAddressForm({
                    source: $row.attr('data-source') || '',
                    pid: $row.attr('data-pid') || '',
                    aid: $row.attr('data-aid') || '',
                    name: $row.attr('data-client-name') || '',
                    tax: $row.attr('data-addr-tax') || '',
                    country: $row.attr('data-country') || 'HU',
                    zip: $row.attr('data-zip') || '',
                    city: $row.attr('data-city') || '',
                    line: $row.attr('data-line') || '',
                });

                $row.removeClass('d-flex').hide().after($form);
                $form.find('[data-field="name"]').trigger('focus');
            });

            $('#partner_select_results').on('click', '.partner-address-new', function () {
                const $btn = $(this);
                closePartnerAddressForms();

                const $form = buildPartnerAddressForm({
                    source: $btn.attr('data-source') || '',
                    pid: $btn.attr('data-pid') || '',
                });

                $btn.closest('.partner-address-new-wrap').before($form);
                $form.find('[data-field="name"]').trigger('focus');
            });

            $('#partner_select_results').on('click', '.partner-address-cancel', function () {
                closePartnerAddressForms();
            });

            $('#partner_select_results').on('click', '.partner-address-save', async function () {
                const $btn = $(this);
                const $form = $btn.closest('.partner-address-form');
                const fieldValue = field => String($form.find(`[data-field="${field}"]`).val() || '').trim();

                const payload = {
                    source: $form.attr('data-source') || '',
                    partner_id: $form.attr('data-pid') || '',
                    address_id: $form.attr('data-aid') || null,
                    name: fieldValue('name'),
                    tax_number: fieldValue('tax_number'),
                    country: fieldValue('country') || 'HU',
                    zip_code: fieldValue('zip_code'),
                    city: fieldValue('city'),
                    address_line: fieldValue('address_line'),
                };

                if (!payload.zip_code || !payload.city || !payload.address_line) {
                    showToast('Az irányítószám, a város és a cím megadása kötelező.', 'danger');
                    return;
                }

                $btn.prop('disabled', true);

                try {
                    const resp = await fetch(`{{ route('admin.documents.partner-address.save') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(payload),
                    });

                    const json = await resp.json().catch(() => ({}));

                    if (!resp.ok) {
                        const firstError = json?.errors ? Object.values(json.errors).flat()[0] : null;
                        throw new Error(firstError || json?.message || 'Hiba történt a cím mentése során.');
                    }

                    showToast(json?.message || 'Cím sikeresen mentve!', 'success');
                    $('#partner_search_input').trigger('input');
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                    $btn.prop('disabled', false);
                }
            });
