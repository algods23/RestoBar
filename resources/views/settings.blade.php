@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="mb-1">Settings</h1>
            <p class="text-muted mb-0">Set up the cashier printer and kitchen printer separately to avoid conflicts.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('settings.save') }}" id="printer_settings_form">
        @csrf

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-1">Cashier Printer</h5>
                        <p class="text-muted small mb-3">For customer receipts at the counter.</p>

                        <label for="cashier_printer" class="form-label">Bluetooth Printer Port</label>
                        <input
                            id="cashier_printer"
                            type="text"
                            name="cashier_printer"
                            class="form-control @error('cashier_printer') is-invalid @enderror"
                            value="{{ old('cashier_printer', $cashier_printer) }}"
                            placeholder="Example: COM3"
                        >
                        @error('cashier_printer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <label for="cashier_paper_size" class="form-label mt-3">Paper</label>
                        <select id="cashier_paper_size" name="cashier_paper_size" class="form-select">
                            <option value="58mm" @selected(old('cashier_paper_size', $cashier_paper_size) === '58mm')>58mm</option>
                            <option value="80mm" @selected(old('cashier_paper_size', $cashier_paper_size) === '80mm')>80mm</option>
                        </select>

                        <div class="input-group mt-3">
                            <select id="cashier_select" class="form-select">
                                @if($cashier_printer)
                                    <option value="{{ $cashier_printer }}" selected>Saved: {{ $cashier_printer }}</option>
                                @else
                                    <option value="">Scan to choose</option>
                                @endif
                            </select>
                            <button data-printer-type="cashier" type="button" class="btn btn-outline-secondary scan-select-btn">Scan</button>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button data-printer-type="cashier" type="button" class="btn btn-secondary test-btn">Connect & Test</button>
                        </div>

                        <div id="cashier_status" class="small mt-3"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-1">Kitchen Printer</h5>
                        <p class="text-muted small mb-3">For kitchen order tickets.</p>

                        <label for="kitchen_printer" class="form-label">Bluetooth Printer Port</label>
                        <input
                            id="kitchen_printer"
                            type="text"
                            name="kitchen_printer"
                            class="form-control @error('kitchen_printer') is-invalid @enderror"
                            value="{{ old('kitchen_printer', $kitchen_printer) }}"
                            placeholder="Example: COM4"
                        >
                        @error('kitchen_printer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <label for="kitchen_paper_size" class="form-label mt-3">Paper</label>
                        <select id="kitchen_paper_size" name="kitchen_paper_size" class="form-select">
                            <option value="58mm" @selected(old('kitchen_paper_size', $kitchen_paper_size) === '58mm')>58mm</option>
                            <option value="80mm" @selected(old('kitchen_paper_size', $kitchen_paper_size) === '80mm')>80mm</option>
                        </select>

                        <div class="input-group mt-3">
                            <select id="kitchen_select" class="form-select">
                                @if($kitchen_printer)
                                    <option value="{{ $kitchen_printer }}" selected>Saved: {{ $kitchen_printer }}</option>
                                @else
                                    <option value="">Scan to choose</option>
                                @endif
                            </select>
                            <button data-printer-type="kitchen" type="button" class="btn btn-outline-secondary scan-select-btn">Scan</button>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button data-printer-type="kitchen" type="button" class="btn btn-secondary test-btn">Connect & Test</button>
                        </div>

                        <div id="kitchen_status" class="small mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button class="btn btn-primary" type="submit">Save Printer Settings</button>
        </div>
    </form>
</div>

<script>
const controls = {
    cashier: {
        input: document.getElementById('cashier_printer'),
        paper: document.getElementById('cashier_paper_size'),
        select: document.getElementById('cashier_select'),
        status: document.getElementById('cashier_status'),
    },
    kitchen: {
        input: document.getElementById('kitchen_printer'),
        paper: document.getElementById('kitchen_paper_size'),
        select: document.getElementById('kitchen_select'),
        status: document.getElementById('kitchen_status'),
    },
};

function setStatus(type, message, color = 'muted') {
    controls[type].status.className = 'small mt-3 text-' + color;
    controls[type].status.textContent = message;
}

function transportFor(printer) {
    return /^COM\d+$/i.test(printer.trim()) ? 'bluetooth' : 'auto';
}

for (const [type, control] of Object.entries(controls)) {
    control.select.addEventListener('change', function() {
        if (this.value) {
            control.input.value = this.value;
        }
    });
}

async function loadSerialPorts(type) {
    try {
        const res = await fetch('{{ route('printers.serial') }}', { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Could not load COM ports');
        return await res.json();
    } catch (e) {
        setStatus(type, e.message, 'danger');
        return [];
    }
}

async function loadBluetoothDevices(type) {
    const control = controls[type];
    control.select.disabled = true;
    control.select.innerHTML = '<option value="">Scanning...</option>';

    try {
        const res = await fetch('{{ route('printers.bluetooth') }}', { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Could not scan Bluetooth devices');
        const devices = await res.json();
        control.select.innerHTML = devices.length ? '<option value="">Choose a Bluetooth printer</option>' : '<option value="">No Bluetooth devices found</option>';
        devices.forEach((device) => {
            const option = document.createElement('option');
            option.value = device.address || '';
            option.textContent = device.address
                ? `${device.name} (${device.address})`
                : `${device.name} (no COM port found)`;
            option.title = device.caption || device.name;
            option.disabled = !device.address;
            control.select.appendChild(option);
        });
    } catch (e) {
        control.select.innerHTML = '<option value="">Error scanning Bluetooth</option>';
        setStatus(type, e.message, 'danger');
    } finally {
        control.select.disabled = false;
    }
}

function chooseBestPrinter(type, serialPorts = []) {
    const control = controls[type];
    const usedByOtherPrinter = type === 'cashier' ? controls.kitchen.input.value.trim() : controls.cashier.input.value.trim();
    const bluetoothOption = Array.from(control.select.options).find((item) => {
        return item.value && !item.disabled && item.value.toUpperCase() !== usedByOtherPrinter.toUpperCase();
    });

    if (bluetoothOption) {
        control.select.value = bluetoothOption.value;
        control.input.value = bluetoothOption.value;
        return bluetoothOption.textContent;
    }

    const serialPort = serialPorts.find((port) => {
        return port.id && port.id.toUpperCase() !== usedByOtherPrinter.toUpperCase();
    });
    if (serialPort) {
        control.input.value = serialPort.id;
        return serialPort.caption || serialPort.id;
    }

    return '';
}

async function scanPrinter(type, button = null) {
    if (button) button.disabled = true;
    setStatus(type, 'Scanning Bluetooth printer...');

    try {
        const [, serialPorts] = await Promise.all([
            loadBluetoothDevices(type),
            loadSerialPorts(type),
        ]);

        const detected = chooseBestPrinter(type, serialPorts);
        if (detected) {
                setStatus(type, 'Detected: ' + detected + '. Saving...', 'success');
                // auto-save detected printer
                try {
                    const res = await fetch('{{ route('settings.save_key') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        credentials: 'same-origin',
                        body: JSON.stringify({ key: type + '_printer', value: controls[type].input.value })
                    });
                    const data = await res.json();
                    if (res.ok) setStatus(type, 'Detected and saved: ' + detected, 'success');
                    else setStatus(type, 'Detected but save failed: ' + (data.message || 'error'), 'danger');
                } catch (e) {
                    setStatus(type, 'Detected but save error: ' + e.message, 'danger');
                }
        } else {
            setStatus(type, 'No Bluetooth printer port was detected. Pair the printer in Windows first, then scan again.', 'warning');
        }
    } finally {
        if (button) button.disabled = false;
    }
}

async function testPrinter(type, button) {
    const printer = controls[type].input.value.trim();
    const paperSize = controls[type].paper.value;
    button.disabled = true;
    setStatus(type, 'Connecting to Bluetooth printer...');

    try {
        const res = await fetch('{{ route('settings.test_print') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                printer,
                printer_type: type,
                paper_size: paperSize,
                transport: transportFor(printer),
            }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Print failed');

        await Promise.all([
            saveSetting(type + '_printer', printer),
            saveSetting(type + '_paper_size', paperSize),
        ]);
        setStatus(type, (data.message || 'Connected and printed') + '. Saved ' + printer + ' / ' + paperSize + '.', 'success');
    } catch (e) {
        setStatus(type, 'Error: ' + e.message, 'danger');
    } finally {
        button.disabled = false;
    }
}

async function saveSetting(key, value) {
    const res = await fetch('{{ route('settings.save_key') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ key, value }),
    });
    if (!res.ok) {
        const data = await res.json();
        throw new Error(data.message || 'Save failed');
    }
}

document.querySelectorAll('.scan-btn, .scan-select-btn').forEach((button) => {
    button.addEventListener('click', function() {
        scanPrinter(this.dataset.printerType, this);
    });
});

document.querySelectorAll('.test-btn').forEach((button) => {
    button.addEventListener('click', function() {
        testPrinter(this.dataset.printerType, this);
    });
});
</script>
@endsection
