@extends('layouts.admin')

@section('title', 'Naptár')

@section('content')
    <div class="container p-0">

        <div class="rounded-xl bg-white shadow-lg p-4" id="calendarContainer">
            <div class="calendar-nav">
                <button class="btn btn-light" id="prevWeek">⬅</button>
                <h5 id="weekLabel">Heti naptár</h5>
                <select id="selectedType" class="form-select calendar-select">
                    <option value="all">Összes típus</option>
                    <option value="worksheets">Összes munkalap</option>
                    <option value="worksheets_szereles">Szerelés</option>
                    <option value="worksheets_karbantartas">Karbantartás</option>
                    <option value="worksheets_felmeres">Felmérés</option>
                    <option value="appointments">Időpontfoglalások</option>
                </select>
                <button class="btn btn-light" id="nextWeek">➡</button>
            </div>

            <div id="calendarArea">
                <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table id="calendar" class="table table-bordered" style="min-width: 1100px;">
                        <thead>
                        <tr>
                            @foreach(range(1, 7) as $i)
                                <th class="text-center">
                                    <div class="calendar-header-label"></div>
                                    <button class="btn btn-circle new_contract_from_calendar" title="Új szerződés">
                                        <i class="fas fa-file-signature" style="color: #8ecae6;"></i>
                                    </button>
                                    <button class="btn btn-circle new_worksheet_from_calendar" title="Új munkalap">
                                        <i class="fas fa-clipboard-list" style="color: #90be6d;"></i>
                                    </button>
                                    <button class="btn btn-circle new_appointment_from_calendar" title="Új időpont">
                                        <i class="fas fa-calendar-check" style="color: #fcbf49;"></i>
                                    </button>
                                </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        <!-- JavaScript tölti ki -->
                        </tbody>
                    </table>
                </div>

                <div id="calendarLoader" style="display: none; text-align: center; margin: 1rem 0;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Betöltés...</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="calendarEntryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="calendarEntryModalTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <div id="calendarEntryModalBody"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Bezárás</button>
                        <button type="button" class="btn btn-primary" id="calendarEntryModalEditBtn">Szerkesztés</button>
                    </div>
                </div>
            </div>
        </div>





    </div>

@endsection

@section('scripts')
    <script type="module">

        $(document).ready(function() {

            if (window.innerWidth <= 992) {
                $(".sidebar").toggleClass("toggled");
            }

            const calendarBody = document.querySelector('#calendar tbody');
            const weekLabel = document.getElementById('weekLabel');

            let currentMonday = new Date();
            currentMonday.setDate(currentMonday.getDate() - (currentMonday.getDay() + 6) % 7); // hétfőre igazítás

            function formatDate(date) {
                return date.toISOString().split('T')[0];
            }

            function getWeekDays(monday) {
                const days = [];
                for (let i = 0; i < 7; i++) {
                    const d = new Date(monday);
                    d.setDate(monday.getDate() + i);
                    days.push(d);
                }
                return days;
            }

            async function fetchWorksheets(startDate, endDate, selectedType) {
                const url = new URL('{{ route("admin.worksheets.byweek") }}', window.location.origin);
                url.searchParams.append('start_date', startDate);
                url.searchParams.append('end_date', endDate);
                url.searchParams.append('type', selectedType);

                try {
                    const response = await fetch(url);
                    if (!response.ok) {
                        console.error('Hiba az adatok betöltésekor:', response.statusText);
                        return [];
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Fetch error:', error);
                    return [];
                }
            }

            async function renderCalendar() {

                // 🔹 Loader megjelenítése
                document.getElementById('calendarLoader').style.display = 'block';

                calendarBody.innerHTML = '';

                const days = getWeekDays(currentMonday);
                const startStr = formatDate(days[0]);
                const endStr = formatDate(days[6]);

                if (weekLabel) {
                    weekLabel.textContent = `${startStr} - ${endStr}`;
                }

                const selectedType = document.getElementById('selectedType').value;
                const worksheets = await fetchWorksheets(startStr, endStr, selectedType);

                // 🔹 Loader elrejtése
                document.getElementById('calendarLoader').style.display = 'none';

                const daysOfWeek = ["Vasárnap", "Hétfő", "Kedd", "Szerda", "Csütörtök", "Péntek", "Szombat"];

                // 🔸 Meglévő <th>-ek dátum hozzárendelése (Blade-ből jöttek)
                const headerThs = document.querySelectorAll('#calendar thead tr th');
                headerThs.forEach((th, i) => {
                    const dateStr = formatDate(days[i]);
                    th.dataset.date = dateStr;

                    // régi címke törlése
                    const label = th.querySelector('.calendar-header-label');
                    if (label) {
                        label.innerHTML = `
                            <div><small><strong>${daysOfWeek[days[i].getDay()]}</strong></small></div>
                            <div>${formatDayLabel(days[i])}</div>
                        `;
                    }

                    // Gombokra is rátesszük a dátumot
                    const contractBtn = th.querySelector('.new_contract_from_calendar');
                    const worksheetBtn = th.querySelector('.new_worksheet_from_calendar');
                    const appointmentBtn = th.querySelector('.new_appointment_from_calendar');

                    if (contractBtn) contractBtn.dataset.date = dateStr;
                    if (worksheetBtn) worksheetBtn.dataset.date = dateStr;
                    if (appointmentBtn) appointmentBtn.dataset.date = dateStr;

                    // 🔹 Mai nap kiemelése (header TH)
                    const todayStr = formatDate(new Date());
                    if (dateStr === todayStr) {
                        th.classList.add("today");
                    } else {
                        th.classList.remove("today");
                    }
                });

                // 🔸 Tartalom cellák
                const tr = document.createElement('tr');
                days.forEach(day => {
                    const td = document.createElement('td');
                    td.dataset.date = formatDate(day);

                    // 🔹 Mai nap kiemelése (tartalom TD)
                    const todayStr = formatDate(new Date());
                    if (td.dataset.date === todayStr) {
                        td.classList.add("today");
                    }

                    const dayWorksheets = worksheets.filter(w => w.installation_date === td.dataset.date);

                    dayWorksheets.forEach((w, index) => {
                        const div = document.createElement('div');
                        div.style.textAlign = 'left';
                        div.style.paddingLeft = '0.5rem';
                        div.dataset.id = w.id;
                        div.dataset.model = w.model;

                        let modelName, typeIcon = '';
                        switch (w.model) {
                            case 'worksheet':
                                modelName = '<i class="fas fa-clipboard-list" style="color: #90be6d" title="Munkalap"></i> Munkalap';
                                div.style.borderLeftStyle = 'solid';
                                div.style.borderWidth = '5px';
                                div.style.borderColor = '#90be6d';
                                break;
                            case 'appointment':
                                modelName = '<i class="fas fa-calendar-check" style="color: #fcbf49" title="Időpontfoglalás"></i> Időpontfoglalás';
                                div.style.borderLeftStyle = 'solid';
                                div.style.borderWidth = '5px';
                                div.style.borderColor = '#fcbf49';
                                break;
                            default:
                                modelName = '<i class="fas fa-question-circle text-muted" title="Ismeretlen model"></i>';
                        }

                        switch (w.type) {
                            case 'Karbantartás':
                                typeIcon = '<i class="fas fa-tools" title="Karbantartás"></i>';
                                break;
                            case 'Felmérés':
                                typeIcon = '<i class="fas fa-search" title="Felmérés"></i>';
                                break;
                            case 'Egyéb':
                                typeIcon = '<i class="fas fa-ellipsis-h" title="Egyéb"></i>';
                                break;
                            case 'Szerelés':
                                typeIcon = '<i class="fas fa-cogs" title="Szerelés"></i>';
                                break;
                            default:
                                typeIcon = '';
                        }

                        div.innerHTML += `
                            <p style="margin-bottom: 0px"><strong>${w.name}</strong></p>
                            <small>(${w.city})</small>
                            <p style="margin-top: 5px">${typeIcon} ${w.type}</p>
                            <p style="font-style: italic">${w.work_status}</p>
                        `;

                        if (w.work_status === 'Kész') {
                            div.style.backgroundColor = '#d4edda';
                        } else {
                            div.style.backgroundColor = 'none';
                        }

                        if (w.work_name) {
                            div.innerHTML += `<small>${w.work_name}</small><br>`;
                        }
                        if (w.description) {
                            div.innerHTML += `<small>${w.description}</small><br>`;
                        }

                        if (w.worker_name) {
                            div.innerHTML += `
                                <i class="fa-solid fa-users-gear"></i>
                                ${w.worker_name}<br>
                            `;
                        }

                        div.classList.add('worksheet-entry');

                        if (index < dayWorksheets.length - 1) {
                            const separator = document.createElement('hr');
                            separator.classList.add('worksheet-separator');
                            td.appendChild(div);
                            td.appendChild(separator);
                        } else {
                            td.appendChild(div);
                        }
                    });

                    tr.appendChild(td);
                });

                calendarBody.appendChild(tr);

                $("#calendar td").sortable({
                    items: ".worksheet-entry",
                    connectWith: "#calendar td",
                    placeholder: "ui-state-highlight",
                    helper: "clone",
                    cursor: "move",
                    stop: function(event, ui) {
                        // a td, ahova az elem került
                        const $td = ui.item.parent();
                        saveDateAndOrder($td);
                    }
                }).disableSelection();

                function saveDateAndOrder($td) {
                    let date = $td.data("date");
                    let items = $td.find(".worksheet-entry");
                    let data = [];

                    items.each(function(index) {
                        data.push({
                            id: $(this).data("id"),
                            model: $(this).data("model"),
                            date: date,
                            sort_order: index + 1
                        });
                    });

                    $.ajax({
                        url: "/admin/munkalapok/update-orderdate",
                        method: "POST",
                        data: {
                            items: data,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(resp) {
                            showToast("Sorrend mentve", 'success');
                            renderCalendar(); // újrarendereljük a naptárt a frissített adatokkal
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON.message, 'danger');
                            renderCalendar(); // visszaállítjuk az eredeti állapotot
                        }
                    });
                }


            }


            $(document).on('click', '.worksheet-entry', function () {
                const data_id = this.dataset.id;
                const model = this.dataset.model;

                const modalEl = document.getElementById('calendarEntryModal');
                const titleEl = document.getElementById('calendarEntryModalTitle');
                const bodyEl = document.getElementById('calendarEntryModalBody');
                const editBtn = document.getElementById('calendarEntryModalEditBtn');

                if (!modalEl || !titleEl || !bodyEl || !editBtn) {
                    alert('Hiba: a megtekintő ablak nem elérhető.');
                    return;
                }

                function escapeHtml(value) {
                    if (value === null || value === undefined) return '';
                    return String(value)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function row(label, value) {
                    return `
                        <tr>
                            <th class="text-nowrap" style="width: 1%;">${escapeHtml(label)}</th>
                            <td>${escapeHtml(value || '-')}</td>
                        </tr>
                    `;
                }

                function section(title, innerHtml) {
                    return `
                        <div class="mb-3">
                            <h6 class="mb-2">${escapeHtml(title)}</h6>
                            ${innerHtml}
                        </div>
                    `;
                }

                function renderAppointmentPhotosReadOnly(photos = []) {
                    if (!photos || !photos.length) {
                        return '<p class="text-muted mb-0">Nincs feltöltött kép.</p>';
                    }

                    const rows = photos.map(photo => {
                        const fileUrl = `${window.appConfig.APP_URL}admin/appointment-photos/${photo.path}`;
                        return `
                            <tr>
                                <td>
                                    <a href="${fileUrl}" target="_blank">
                                        <img src="${fileUrl}" class="img-thumbnail" style="width: 100px;" alt="">
                                    </a>
                                </td>
                            </tr>
                        `;
                    }).join('');

                    return `
                        <div style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-bordered table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kép</th>
                                    </tr>
                                </thead>
                                <tbody>${rows}</tbody>
                            </table>
                        </div>
                    `;
                }

                function renderWorksheetPhotosReadOnly(photos = []) {
                    const types = [
                        'Helyszíni felmérés',
                        'Adattábla',
                        'Telepítési tanúsítvány',
                        'Szerelés',
                        'Számla'
                    ];

                    if (!photos || !photos.length) {
                        const empty = types.map(t => section(t, '<p class="text-muted mb-0">Nincs feltöltött kép.</p>')).join('');
                        return empty;
                    }

                    const byType = {};
                    photos.forEach(p => {
                        const t = p.image_type || 'Egyéb';
                        if (!byType[t]) byType[t] = [];
                        byType[t].push(p);
                    });

                    function renderTableFor(list = []) {
                        if (!list.length) {
                            return '<p class="text-muted mb-0">Nincs feltöltött kép.</p>';
                        }

                        const rows = list.map(photo => {
                            const fileUrl = `${window.appConfig.APP_URL}admin/worksheets/${photo.image_path}`;
                            const path = (photo.image_path || '').toLowerCase();
                            const extension = path.includes('.') ? path.split('.').pop() : '';
                            const description = photo.description || '';

                            let previewHtml = '';
                            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
                                previewHtml = `
                                    <a href="${fileUrl}" target="_blank" title="${escapeHtml(description)}">
                                        <img src="${fileUrl}" alt="${escapeHtml(description)}" class="img-thumbnail" style="width: 100px;">
                                    </a>
                                `;
                            } else if (extension === 'pdf') {
                                previewHtml = `
                                    <a href="${fileUrl}" target="_blank">${escapeHtml(description || 'PDF fájl')}</a>
                                `;
                            } else if (['doc', 'docx'].includes(extension)) {
                                previewHtml = `
                                    <a href="${fileUrl}" target="_blank">${escapeHtml(description || 'Word dokumentum')}</a>
                                `;
                            } else {
                                previewHtml = `
                                    <a href="${fileUrl}" target="_blank">${escapeHtml(description || photo.image_path)}</a>
                                `;
                            }

                            return `
                                <tr>
                                    <td>${previewHtml}</td>
                                </tr>
                            `;
                        }).join('');

                        return `
                            <div style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-bordered table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fájl</th>
                                        </tr>
                                    </thead>
                                    <tbody>${rows}</tbody>
                                </table>
                            </div>
                        `;
                    }

                    const orderedTypes = [...types];
                    Object.keys(byType).forEach(t => {
                        if (!orderedTypes.includes(t)) orderedTypes.push(t);
                    });

                    return orderedTypes
                        .map(t => section(t, renderTableFor(byType[t] || [])))
                        .join('');
                }

                function translateWorksheetExtraDataEntry(key, value) {
                    const keyTranslations = {
                        pipe: 'Plusz cső',
                        console: 'Konzol',
                        device_qty: 'Készülékek',
                        exist_contract: 'Szerződéskötés',
                        cleaning_type: 'Tisztítás',
                        self_installation: 'Saját telepítés'
                    };

                    const valueTranslations = {
                        exist_contract: {
                            hitel: 'Hitelre lesz',
                            igen: 'Igen',
                            nem: 'Nem'
                        },
                        cleaning_type: {
                            basic_clean: 'Alaptisztítás',
                            full_clean: 'Teljes mosás'
                        },
                        self_installation: {
                            igen: 'Igen',
                            nem: 'Nem'
                        }
                    };

                    const translatedKey = keyTranslations[key] || (String(key || '').replace(/_/g, ' ').replace(/^\w/, c => c.toUpperCase()));

                    let translatedValue = value;
                    if (translatedValue === null || translatedValue === undefined || translatedValue === '') {
                        translatedValue = '-';
                    }

                    const keyValueMap = valueTranslations[key];
                    if (keyValueMap && translatedValue !== '-' && Object.prototype.hasOwnProperty.call(keyValueMap, translatedValue)) {
                        translatedValue = keyValueMap[translatedValue];
                    }

                    return { translatedKey, translatedValue };
                }

                bodyEl.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>';

                let detailsUrl = null;
                let editUrl = null;
                if ("worksheet" === model) {
                    detailsUrl = `${window.appConfig.APP_URL}admin/munkalapok/adatok/${data_id}`;
                    editUrl = `${window.appConfig.APP_URL}admin/munkalapok?id=${data_id}`;
                } else if ("appointment" === model) {
                    detailsUrl = `${window.appConfig.APP_URL}admin/idopontfoglalasok/${data_id}`;
                    editUrl = `${window.appConfig.APP_URL}admin/idopontfoglalasok?id=${data_id}`;
                } else {
                    alert('Ismeretlen elem, nem lehet megnyitni.');
                    return;
                }

                editBtn.dataset.model = model;
                editBtn.dataset.id = data_id;
                editBtn.dataset.url = editUrl;

                fetch(detailsUrl, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(async (resp) => {
                        if (!resp.ok) {
                            const txt = await resp.text();
                            throw new Error(txt || resp.statusText);
                        }
                        return resp.json();
                    })
                    .then((data) => {
                        if (model === 'worksheet') {
                            titleEl.textContent = `Munkalap #${data.id}`;

                            const worksheetBasics = `
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        ${row('Munkalap megnevezése', data.work_name)}
                                        ${row('Telepítés dátuma', data.installation_date)}
                                        ${row('Munkalap típusa', data.work_type)}
                                        ${row('Munkalap állapota', data.work_status)}
                                        ${row('Szerződés', data.contract_id ? ('#' + data.contract_id) : '-')}
                                    </table>
                                </div>
                            `;

                            const contactBlock = `
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        ${row('Kapcsolattartó neve', data.name)}
                                        ${row('E-mail', data.email)}
                                        ${row('Telefon', data.phone)}
                                        ${row('Ország', data.country)}
                                        ${row('Irányítószám', data.zip_code)}
                                        ${row('Város', data.city)}
                                        ${row('Cím', data.address_line)}
                                    </table>
                                </div>
                            `;

                            const descriptionBlock = `
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        ${row('Leírás', data.description)}
                                        ${row('Szerelői jelentés', data.worker_report)}
                                    </table>
                                </div>
                            `;

                            const paymentBlock = `
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        ${row('Fizetés módja', data.payment_method)}
                                        ${row('Fizetett összeg', (data.payment_amount !== null && data.payment_amount !== undefined) ? data.payment_amount : '-')}
                                    </table>
                                </div>
                            `;

                            const workersBlock = (Array.isArray(data.workers) && data.workers.length)
                                ? `
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            ${row('Szerelők', data.workers.map(w => w.name).join(', '))}
                                        </table>
                                    </div>
                                `
                                : '<p class="text-muted mb-0">Nincs megadva.</p>';

                            const extraDataEntries = data.data && typeof data.data === 'object'
                                ? Object.entries(data.data)
                                : [];
                            const extraDataBlock = extraDataEntries.length
                                ? `
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            ${extraDataEntries.map(([k, v]) => {
                                                const t = translateWorksheetExtraDataEntry(k, v);
                                                return row(t.translatedKey, t.translatedValue);
                                            }).join('')}
                                        </table>
                                    </div>
                                `
                                : '<p class="text-muted mb-0">Nincs megadva.</p>';

                            const productsBlock = (Array.isArray(data.products) && data.products.length)
                                ? `
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Termék</th>
                                                    <th class="text-nowrap">Mennyiség</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${data.products.map(p => `
                                                    <tr>
                                                        <td>${escapeHtml(p.title || p.name || ('#' + p.id))}</td>
                                                        <td class="text-nowrap">${escapeHtml(p.quantity)}</td>
                                                    </tr>
                                                `).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                `
                                : '<p class="text-muted mb-0">Nincs termék.</p>';

                            const html = [
                                section('Munkalap adatok', worksheetBasics),
                                section('Kapcsolattartó', contactBlock),
                                section('Leírás / jelentés', descriptionBlock),
                                section('Szerelők', workersBlock),
                                section('Fizetés', paymentBlock),
                                section('Extra adatok', extraDataBlock),
                                section('Termékek', productsBlock),
                                section('Képek / fájlok', renderWorksheetPhotosReadOnly(data.photos || [])),
                            ].join('');

                            bodyEl.innerHTML = html;
                        } else {
                            titleEl.textContent = `Időpontfoglalás #${data.id}`;

                            const basicBlock = `
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        ${row('Ügyfél', data.client_id ? ('#' + data.client_id) : '-')}
                                        ${row('Név', data.name)}
                                        ${row('E-mail', data.email)}
                                        ${row('Telefon', data.phone)}
                                    </table>
                                </div>
                            `;

                            const addressBlock = `
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        ${row('Irányítószám', data.zip_code)}
                                        ${row('Város', data.city)}
                                        ${row('Cím', data.address_line)}
                                    </table>
                                </div>
                            `;

                            const appointmentBlock = `
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        ${row('Időpont', data.appointment_date)}
                                        ${row('Típus', data.appointment_type)}
                                        ${row('Státusz', data.status)}
                                        ${row('Üzenet', data.message)}
                                    </table>
                                </div>
                            `;

                            const html = [
                                section('Alapadatok', basicBlock),
                                section('Cím adatok', addressBlock),
                                section('Időpont adatok', appointmentBlock),
                                section('Képek', renderAppointmentPhotosReadOnly(data.photos || [])),
                            ].join('');

                            bodyEl.innerHTML = html;
                        }

                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    })
                    .catch((err) => {
                        bodyEl.innerHTML = `<div class="alert alert-danger mb-0">Hiba a betöltés során: ${escapeHtml(err.message)}</div>`;
                        titleEl.textContent = 'Megtekintés';
                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    });

            });

            $(document).on('click', '#calendarEntryModalEditBtn', function () {
                const url = this.dataset.url;
                if (!url) return;
                window.open(url, '_blank');
            });

            $(document).on('change', '#selectedType', function () {
                renderCalendar();
            });

            function formatDayLabel(date) {
                // 'MM-DD' formátumban
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day   = String(date.getDate()).padStart(2, '0');
                return `${month}-${day}`;
            }

            function changeWeek(offset) {
                currentMonday.setDate(currentMonday.getDate() + offset * 7);
                renderCalendar();
            }

            $('#prevWeek').click(function() {
                changeWeek(-1);
            });
            $('#nextWeek').click(function() {
                changeWeek(1);
            });

            $(document).on('click', '.new_contract_from_calendar', function () {
                const contract_date = $(this).data('date');
                if (!contract_date) return;

                const url = `${window.appConfig.APP_URL}admin/szerzodesek?make_contract=true&installation_date=${contract_date}`;
                window.open(url, '_blank');
            });

            $(document).on('click', '.new_worksheet_from_calendar', function () {
                const worksheet_date = $(this).data('date');
                if (!worksheet_date) return;

                const url = `${window.appConfig.APP_URL}admin/munkalapok?make_worksheet=true&installation_date=${worksheet_date}`;
                window.open(url, '_blank');
            });

            $(document).on('click', '.new_appointment_from_calendar', function () {
                const appointment_date = $(this).data('date');
                if (!appointment_date) return;

                const url = `${window.appConfig.APP_URL}admin/idopontfoglalasok?make_appointment=true&installation_date=${appointment_date}`;
                window.open(url, '_blank');
            });

            renderCalendar();



        });
    </script>
@endsection



