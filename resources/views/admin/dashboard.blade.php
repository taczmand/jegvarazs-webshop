@extends('layouts.admin')

@section('title', 'Naptár')

@section('content')
    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Naptár</h2>
        </div>


        <div class="rounded-xl bg-white shadow-lg p-4" id="calendarContainer">

            <div class="calendar-nav">
                <button class="btn btn-light" id="prevWeek">⬅ Előző hét</button>
                <h5 id="weekLabel">Heti naptár</h5>
                <button class="btn btn-light" id="nextWeek">Következő hét ➡</button>
            </div>
            <table id="calendar">
                <thead>
                <tr>
                    <th>Hétfő</th>
                    <th>Kedd</th>
                    <th>Szerda</th>
                    <th>Csütörtök</th>
                    <th>Péntek</th>
                    <th>Szombat</th>
                    <th>Vasárnap</th>
                </tr>
                <tr>
                    @foreach(range(1, 7) as $i)
                        <th class="text-center">
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
            <div id="calendarLoader" style="display: none; text-align: center; margin: 1rem 0;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Betöltés...</span>
                </div>
            </div>
        </div>




    </div>

@endsection

@section('scripts')
    <script type="module">

        $(document).ready(function() {

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

            async function fetchWorksheets(startDate, endDate) {
                const url = new URL('{{ route("admin.worksheets.byweek") }}', window.location.origin);
                url.searchParams.append('start_date', startDate);
                url.searchParams.append('end_date', endDate);

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
                    weekLabel.textContent = `Heti naptár: ${startStr} - ${endStr}`;
                }

                const worksheets = await fetchWorksheets(startStr, endStr);

                // 🔹 Loader elrejtése
                document.getElementById('calendarLoader').style.display = 'none';

                // 🔸 Meglévő <th>-ek dátum hozzárendelése (Blade-ből jöttek)
                const headerThs = document.querySelectorAll('#calendar thead tr:nth-child(2) th');
                headerThs.forEach((th, i) => {
                    const dateStr = formatDate(days[i]);
                    th.dataset.date = dateStr;

                    // 🔹 Előző dátumcímke eltávolítása, ha van
                    const oldLabel = th.querySelector('.calendar-date-label');
                    if (oldLabel) {
                        oldLabel.remove();
                    }

                    // 🔹 Új címke hozzáadása
                    const dateLabel = document.createElement('div');
                    dateLabel.textContent = formatDayLabel(days[i]);
                    dateLabel.classList.add('calendar-date-label');
                    th.prepend(dateLabel);

                    // Gombokra is rátesszük a dátumot
                    const contractBtn = th.querySelector('.new_contract_from_calendar');
                    const worksheetBtn = th.querySelector('.new_worksheet_from_calendar');
                    const appointmentBtn = th.querySelector('.new_appointment_from_calendar');


                    if (contractBtn) contractBtn.dataset.date = dateStr;
                    if (worksheetBtn) worksheetBtn.dataset.date = dateStr;
                    if (appointmentBtn) appointmentBtn.dataset.date = dateStr;
                });

                // 🔸 Tartalom cellák
                const tr = document.createElement('tr');
                days.forEach(day => {
                    const td = document.createElement('td');
                    td.dataset.date = formatDate(day);


                    const dayWorksheets = worksheets.filter(w => w.installation_date === td.dataset.date);

                    dayWorksheets.forEach((w, index) => {
                        const div = document.createElement('div');
                        div.style.textAlign = 'left';
                        div.style.paddingLeft = '0.5rem';

                        div.dataset.id = w.id;


                        let modelName, typeIcon = '';
                        switch (w.model) {
                            case 'worksheet':
                                modelName = '<i class="fas fa-clipboard-list" style="color: #90be6d" title="Munkalap"></i> Munkalap';
                                div.style.borderLeftStyle = 'solid';
                                div.style.borderWidth = '5px';
                                div.style.borderColor = '#90be6d';
                                div.style.hover = 'background-color: #90be6d !important; color: white; cursor: pointer;';
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

                        if (w.work_name) {
                            div.innerHTML += `
                                <small>${w.work_name}</small><br>
                            `;
                        }

                        if (w.worker_name) {
                            div.innerHTML += `
                                <i class="fa-solid fa-users-gear"></i>
                                ${w.worker_name}
                                <br>
                            `;
                        }

                        /*div.innerHTML += `
                            ${statusIcon}<strong>${w.work_name}</strong><br>
                            ${w.name}<br>
                            ${w.city}
                        `;*/

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
            }

            $(document).on('click', '.worksheet-entry', function () {
                const worksheet_id = this.dataset.id;
                // TODO: időpontfoglalás
                const url = `${window.appConfig.APP_URL}admin/munkalapok?id=${worksheet_id}`;
                window.open(url, '_blank');
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



