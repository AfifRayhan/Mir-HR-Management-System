<x-app-layout>
    @push('styles')
    @vite(['resources/css/ui-roster.css'])
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            {{-- Page Header --}}
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-calendar3 me-2 text-success"></i>{{ $pageTitle ?? 'Roster' }} Management</h5>
                        <p class="mb-0 small text-muted">Manage monthly shifts for designated groups</p>
                    </div>
                    <div class="text-end text-sm text-gray-500">
                        <i class="bi bi-calendar-event me-2 text-success"></i>{{ now()->format('l, d M Y') }}
                    </div>
                </div>
            </div>

            {{-- Group Tabs + Month Selector --}}
            <div class="ui-panel p-3 mb-3">
                <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
                    <div class="d-flex gap-2 overflow-auto pb-1" style="max-width: 100%;">
                        @foreach($groups as $slug => $label)
                        <a href="{{ route(($routePrefix ?? 'roster.') . 'index', ['group' => $slug, 'month' => $monthParam, 'mode' => $mode]) }}"
                           class="btn btn-sm btn-outline-success rounded-pill group-tab px-3 text-nowrap {{ $groupSlug === $slug ? 'active' : '' }}"
                           id="tab-{{ $slug }}">
                            <i class="bi {{ $slug === 'all' ? 'bi-grid-3x3-gap' : (str_starts_with($slug, 'noc') ? 'bi-broadcast-pin' : (str_starts_with($slug, 'driver') ? 'bi-car-front' : 'bi-tools')) }} me-1"></i>{{ $label }}
                        </a>
                        @endforeach
                    </div>
                    
                    <div class="d-flex flex-column align-items-start align-items-md-end gap-2">
                        {{-- Mode Toggle --}}
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route(($routePrefix ?? 'roster.') . 'index', ['group' => $groupSlug, 'month' => $monthParam, 'mode' => 'weekly']) }}" 
                               class="btn {{ $mode === 'weekly' ? 'btn-success' : 'btn-outline-success' }}">Weekly</a>
                            <a href="{{ route(($routePrefix ?? 'roster.') . 'index', ['group' => $groupSlug, 'month' => $monthParam, 'mode' => 'monthly']) }}" 
                               class="btn {{ $mode === 'monthly' ? 'btn-success' : 'btn-outline-success' }}">Monthly</a>
                        </div>

                        <form method="GET" action="{{ route(($routePrefix ?? 'roster.') . 'index') }}" class="d-flex align-items-center gap-2 mb-0">
                            <input type="hidden" name="group" value="{{ $groupSlug }}">
                            <input type="hidden" name="mode" value="{{ $mode }}">
                            <label class="small text-muted mb-0 d-none d-sm-block">Month:</label>
                            <input type="month" name="month" value="{{ $monthParam }}"
                                   class="form-control form-control-sm rounded-pill"
                                   style="max-width: 160px;"
                                   onchange="this.form.submit()">
                        </form>
                    </div>
                </div>
            </div>

            {{-- Roster Table --}}
            <div class="ui-panel p-0 mb-4">
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-white">
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <h6 class="mb-0 font-bold text-gray-800 d-none d-lg-block">
                            <i class="bi bi-table me-2 text-success"></i>
                            @if($mode === 'weekly')
                                Weekly Pattern Template
                            @else
                                {{ $monthStart->format('F Y') }}
                            @endif
                        </h6>
                    </div>
                    <div class="d-flex align-items-stretch gap-2">
                        @if($employees->isNotEmpty())
                            @if($mode === 'weekly')
                                <button onclick="importPreviousSchedule()" class="btn btn-outline-success px-4 rounded-pill font-bold shadow-sm d-flex align-items-center text-nowrap">
                                    <i class="bi bi-download me-1"></i> Import Previous
                                </button>
                            @endif

                            <div class="dropdown d-flex">
                                <button class="btn btn-outline-success px-4 rounded-pill font-bold shadow-sm dropdown-toggle d-flex align-items-center text-nowrap w-100" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-cloud-download me-1"></i> Download
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li><a class="dropdown-item px-3 py-2" href="{{ route(($routePrefix ?? 'roster.') . 'export', ['group' => $groupSlug, 'month' => $monthParam, 'mode' => $mode, 'format' => 'xlsx']) }}">
                                        <i class="bi bi-file-earmark-excel text-success me-2"></i> Excel (.xlsx)</a></li>
                                    <li><a class="dropdown-item px-3 py-2" href="{{ route(($routePrefix ?? 'roster.') . 'export', ['group' => $groupSlug, 'month' => $monthParam, 'mode' => $mode, 'format' => 'csv']) }}">
                                        <i class="bi bi-file-earmark-spreadsheet text-info me-2"></i> CSV (.csv)</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><button class="dropdown-item px-3 py-2" onclick="downloadAsImage()">
                                        <i class="bi bi-file-earmark-image text-warning me-2"></i> Image (.png)</button></li>
                                    <li><button class="dropdown-item px-3 py-2" onclick="downloadAsPDF()">
                                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i> PDF (.pdf)</button></li>
                                </ul>
                            </div>

                            <button id="saveRosterBtn" class="btn btn-success px-4 rounded-pill font-bold shadow-sm d-flex align-items-center text-nowrap">
                                <i class="bi bi-floppy me-1"></i> Save Roster
                            </button>
                        @endif
                    </div>
                </div>

                @if($employees->isEmpty())
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-people" style="font-size: 2.5rem; display: block; margin-bottom: 0.75rem; color: #94a3b8;"></i>
                    <p class="fw-semibold mb-1">No employees in <strong>{{ $groupLabel }}</strong></p>
                    <p class="small mb-0">Assign a Roster Group to employees via
                        <a href="{{ route('personnel.employees.index') }}" class="text-success">Employee Management</a>.
                    </p>
                </div>
                @else
                <div class="p-0" id="roster-wrapper" 
                     data-employees="{{ json_encode($employees) }}"
                     data-shifts="{{ json_encode(array_keys($shiftTypes)) }}">
                    <table class="table roster-table mb-0 table-bordered w-100">
                        <thead>
                            <tr>
                                <th class="ps-3">{{ $mode === 'weekly' ? 'Day Name' : 'Day' }}</th>
                                <th class="text-center">{{ $mode === 'weekly' ? '#' : 'Date' }}</th>
                                @foreach($shiftTypes as $type => $config)
                                <th>
                                    <span class="shift-badge {{ $config['badge'] }}">{{ $config['label'] }}</span> 
                                    @if($config['time'])
                                    <small class="d-block opacity-75">({{ $config['time'] }})</small>
                                    @endif
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($days as $index => $day)
                            @php
                                $dateStr   = $day->format('Y-m-d');
                                $dayOfWeek = $day->dayOfWeek;
                                $isWeekend = in_array($dayOfWeek, [5, 6]); // Fri=5, Sat=6 (BD)

                                $shiftBuckets = [];
                                foreach(array_keys($shiftTypes) as $st) { $shiftBuckets[$st] = []; }
                                
                                foreach ($employees as $emp) {
                                    if ($mode === 'weekly') {
                                        $assigned = $patternMap[$index][$emp->id] ?? 'Off';
                                    } else {
                                        $assigned = $scheduleMap[$emp->id][$dateStr] ?? 'Off';
                                    }
                                    // Compatibility check: if old data exists that doesn't match new keys
                                    if (!isset($shiftBuckets[$assigned])) {
                                        $assigned = 'Off';
                                    }
                                    $shiftBuckets[$assigned][] = $emp->id;
                                }
                            @endphp
                            <tr class="{{ $isWeekend ? 'weekend-row' : '' }}" 
                                data-date="{{ $dateStr }}" 
                                data-day-index="{{ $index }}">
                                <td class="ps-3 day-label">{{ $day->format('l') }}</td>
                                <td class="text-center fw-bold">{{ $mode === 'weekly' ? $index + 1 : $day->format('d') }}</td>

                                @foreach($shiftTypes as $shift => $config)
                                @php $assigned = $shiftBuckets[$shift] ?? []; @endphp
                                <td>
                                    <div class="roster-cell-container" data-shift-type="{{ $shift }}">
                                        {{-- The real select (hidden) --}}
                                        <select class="form-select form-select-sm roster-cell d-none"
                                                multiple
                                                data-shift="{{ $shift }}"
                                                data-date="{{ $dateStr }}"
                                                data-day-index="{{ $index }}">
                                            @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}" {{ in_array($emp->id, $assigned) ? 'selected' : '' }}>
                                                {{ $emp->name }}
                                            </option>
                                            @endforeach
                                        </select>

                                        {{-- The Interactive UI --}}
                                        <div class="badge-list" id="badge-list-{{ $index }}-{{ $shift }}">
                                            {{-- Badges will be rendered here by JS --}}
                                        </div>

                                        @if($shift !== 'Off')
                                            <div class="add-emp-btn" 
                                                 title="Add employee to {{ $shift }}"
                                                 onclick="toggleEmpSelector(this, '{{ $index }}', '{{ $shift }}')">
                                                <i class="bi bi-plus"></i>
                                            </div>
                                            
                                            {{-- Selection Menu --}}
                                            <div class="emp-selector-dropdown" id="selector-{{ $index }}-{{ $shift }}">
                                                <input type="text" class="form-control form-control-sm emp-selector-search" 
                                                       placeholder="Search..." 
                                                       onclick="event.stopPropagation()"
                                                       onkeyup="filterEmpList(this)">
                                                <div class="emp-selector-list">
                                                    {{-- Available employees will be listed here by JS --}}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </main>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const rosterWrapper = document.getElementById('roster-wrapper');
        const employees = rosterWrapper ? JSON.parse(rosterWrapper.dataset.employees || '[]') : [];
        const activeShifts = rosterWrapper ? JSON.parse(rosterWrapper.dataset.shifts || '[]') : [];
        const rows = document.querySelectorAll('tr[data-date]');
        
        // --- Helper Functions ---
        
        // Sync the Interactive UI with hidden multi-selects
        window.syncRosterUI = function(rowIndex, forceAll = false) {
            const row = document.querySelector(`tr[data-day-index="${rowIndex}"]`);
            if (!row) return;

            activeShifts.forEach(shift => {
                const select = row.querySelector(`select[data-shift="${shift}"]`);
                const badgeList = document.getElementById(`badge-list-${rowIndex}-${shift}`);
                if (!badgeList) return;

                badgeList.innerHTML = '';
                Array.from(select.selectedOptions).forEach(opt => {
                    const emp = employees.find(e => e.id == opt.value);
                    if (!emp) return;

                    const badge = document.createElement('div');
                    badge.className = 'emp-badge';
                    badge.innerHTML = `
                        <span>${emp.name}</span>
                        ${shift !== 'Off' ? `<span class="remove-btn" onclick="removeEmployee('${rowIndex}', '${shift}', '${emp.id}')">&times;</span>` : ''}
                    `;
                    badgeList.appendChild(badge);
                });
            });
        };

        // Initialize all rows
        rows.forEach(row => syncRosterUI(row.dataset.dayIndex));

        // --- Interaction Handlers ---

        window.toggleEmpSelector = function(btn, rowIndex, shift) {
            const dropdown = document.getElementById(`selector-${rowIndex}-${shift}`);
            const isShowing = dropdown.classList.contains('show');
            
            // Close all others
            document.querySelectorAll('.emp-selector-dropdown').forEach(d => d.classList.remove('show'));
            
            if (!isShowing) {
                dropdown.classList.add('show');
                populateAvailableEmployees(rowIndex, shift);
            }
        };

        window.populateAvailableEmployees = function(rowIndex, currentShift) {
            const row = document.querySelector(`tr[data-day-index="${rowIndex}"]`);
            const dropdown = document.getElementById(`selector-${rowIndex}-${currentShift}`);
            const list = dropdown.querySelector('.emp-selector-list');
            
            // Find all selected IDs in ANY shift (excluding 'Off' for now to identify who is "free")
            // Actually, an employee is only "available" if they are currently in the 'Off' bucket.
            const offSelect = row.querySelector('select[data-shift="Off"]');
            const availableEmpIds = Array.from(offSelect.selectedOptions).map(opt => opt.value);
            
            list.innerHTML = '';
            
            if (availableEmpIds.length === 0) {
                list.innerHTML = '<div class="text-muted small p-2">All employees assigned</div>';
                return;
            }

            availableEmpIds.forEach(id => {
                const emp = employees.find(e => e.id == id);
                const item = document.createElement('div');
                item.className = 'emp-selector-item';
                item.textContent = emp.name;
                item.onclick = () => addEmployee(rowIndex, currentShift, id);
                list.appendChild(item);
            });
        };

        window.addEmployee = function(rowIndex, targetShift, empId) {
            const row = document.querySelector(`tr[data-day-index="${rowIndex}"]`);
            
            // 1. Remove from wherever they are
            activeShifts.forEach(s => {
                const sel = row.querySelector(`select[data-shift="${s}"]`);
                const opt = Array.from(sel.options).find(o => o.value == empId);
                if (opt) opt.selected = false;
            });

            // 2. Add to target shift
            const targetSel = row.querySelector(`select[data-shift="${targetShift}"]`);
            const opt = Array.from(targetSel.options).find(o => o.value == empId);
            if (opt) opt.selected = true;

            // 3. Close dropdown and Sync UI
            document.querySelectorAll('.emp-selector-dropdown').forEach(d => d.classList.remove('show'));
            syncRosterUI(rowIndex);
        };

        window.removeEmployee = function(rowIndex, fromShift, empId) {
            const row = document.querySelector(`tr[data-day-index="${rowIndex}"]`);
            
            // 1. Remove from current shift
            const currentSel = row.querySelector(`select[data-shift="${fromShift}"]`);
            const opt = Array.from(currentSel.options).find(o => o.value == empId);
            if (opt) opt.selected = false;

            // 2. Add back to 'Off' bucket
            const offSel = row.querySelector('select[data-shift="Off"]');
            const offOpt = Array.from(offSel.options).find(o => o.value == empId);
            if (offOpt) offOpt.selected = true;

            syncRosterUI(rowIndex);
        };

        window.filterEmpList = function(input) {
            const query = input.value.toLowerCase();
            const list = input.nextElementSibling;
            const items = list.querySelectorAll('.emp-selector-item');
            items.forEach(item => {
                item.style.display = item.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        };

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.roster-cell-container')) {
                document.querySelectorAll('.emp-selector-dropdown').forEach(d => d.classList.remove('show'));
            }
        });

        // --- Original Save Logic (Preserved) ---
        const saveBtn = document.getElementById('saveRosterBtn');
        if (!saveBtn) return;

        const mode = '{{ $mode }}';
        const month = '{{ $monthParam }}';

        saveBtn.addEventListener('click', function () {
            const rows = document.querySelectorAll('tr[data-date]');
            const schedules = [];
            const employeeIds = employees.map(e => e.id);

            rows.forEach(row => {
                const date = row.dataset.date;
                const dayIndex = row.dataset.dayIndex;
                const assigned = {};

                // Collect selections from each shift select
                row.querySelectorAll('select.roster-cell').forEach(sel => {
                    const shift = sel.dataset.shift;
                    Array.from(sel.selectedOptions).forEach(opt => {
                        assigned[opt.value] = shift;
                    });
                });

                // Unassigned employees default to 'Off'
                employeeIds.forEach(id => {
                    if (!assigned[id]) assigned[id] = 'Off';
                });

                Object.entries(assigned).forEach(([empId, shiftType]) => {
                    const entry = { employee_id: parseInt(empId), shift_type: shiftType };
                    if (mode === 'weekly') {
                        entry.day_index = parseInt(dayIndex);
                    } else {
                        entry.date = date;
                    }
                    schedules.push(entry);
                });
            });

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

            fetch('{{ route(($routePrefix ?? "roster.") . "save") }}', {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ mode, month, schedules })
            })
            .then(async r => {
                const data = await r.json();
                if (!r.ok) throw new Error(data.message || 'Server returned an error');
                return data;
            })
            .then(data => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-floppy me-1"></i> Save Roster';
                if (data.success) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Saved!', 
                        text: data.message, 
                        timer: 2000, 
                        showConfirmButton: false, 
                        toast: true, 
                        position: 'top-end' 
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to save roster.' });
                }
            })
            .catch((err) => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-floppy me-1"></i> Save Roster';
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error', 
                    text: err.message === 'Failed to fetch' ? 'Could not connect to server.' : err.message 
                });
            });
        });

        // Import Previous Month Roster Logic
        window.importPreviousSchedule = function() {
            Swal.fire({
                title: 'Importing...',
                text: 'Fetching previous month schedule pattern',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch('{{ route(($routePrefix ?? "roster.") . "import-previous") }}?month={{ $monthParam }}&group={{ $groupSlug }}')
            .then(r => r.json())
            .then(data => {
                Swal.close();
                if (Object.keys(data).length === 0) {
                    Swal.fire({ icon: 'info', title: 'No Data', text: 'No roster pattern found in the previous month.' });
                    return;
                }

                // Apply imported pattern to the current rows
                const rows = document.querySelectorAll('tr[data-date]');
                rows.forEach((row, rowIndex) => {
                    // 1. Clear current assignments (transfer everyone to 'Off')
                    const badges = row.querySelectorAll('.emp-badge .bi-x');
                    badges.forEach(b => b.click());
                    
                    // 2. Apply imported assignments if they exist for this dayIndex
                    if (data[rowIndex]) {
                        Object.entries(data[rowIndex]).forEach(([empId, shiftType]) => {
                            // Find 'Off' select (source)
                            const offSel = row.querySelector('select[data-shift="Off"]');
                            if (!offSel) return;
                            
                            const optToMove = Array.from(offSel.options).find(o => o.value == empId);
                            if (!optToMove) return;

                            // Deselect from 'Off'
                            optToMove.selected = false;
                            
                            // Select in target shift
                            const targetSel = row.querySelector(`select[data-shift="${shiftType}"]`);
                            if (targetSel) {
                                const targetOpt = Array.from(targetSel.options).find(o => o.value == empId);
                                if (targetOpt) targetOpt.selected = true;
                            }
                        });
                        // Sync UI for this row
                        syncRosterUI(rowIndex);
                    }
                });

                Swal.fire({ 
                    icon: 'success', 
                    title: 'Imported!', 
                    text: 'Previous month pattern has been applied.', 
                    timer: 2000, 
                    showConfirmButton: false,
                    toast: true, 
                    position: 'top-end' 
                });
            })
            .catch(err => {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to import previous roster.' });
            });
        };
        
        // --- Export Logic ---
        window.downloadAsImage = function() {
            const table = document.querySelector('.roster-table');
            if (!table) return;

            Swal.fire({ title: 'Capturing...', text: 'Preparing image download', didOpen: () => Swal.showLoading() });

            html2canvas(table, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                Swal.close();
                const link = document.createElement('a');
                link.download = `Roster_{{ $groupLabel }}_{{ $monthStart->format('Y-M') }}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        };

        window.downloadAsPDF = function() {
            const table = document.querySelector('.roster-table');
            if (!table) return;

            Swal.fire({ title: 'Generating PDF...', text: 'This may take a moment', didOpen: () => Swal.showLoading() });

            html2canvas(table, {
                scale: 1.5,
                useCORS: true,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const { jsPDF } = window.jspdf;
                const imgData = canvas.toDataURL('image/png');
                
                // Landscape orientation for roster
                const pdf = new jsPDF('l', 'mm', 'a4');
                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                pdf.save(`Roster_{{ $groupLabel }}_{{ $monthStart->format('Y-M') }}.pdf`);
                Swal.close();
            });
        };
    });
    </script>
    @endpush
</x-app-layout>




