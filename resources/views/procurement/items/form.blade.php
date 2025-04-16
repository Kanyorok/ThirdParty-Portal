<form method="POST" action="{{ $route }}">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="Name" class="form-control" value="{{ old('Name', $item->Name ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="Type" class="form-control" required>
            <option value="good" {{ old('Type', $item->Type ?? '') === 'good' ? 'selected' : '' }}>Good</option>
            <option value="service" {{ old('Type', $item->Type ?? '') === 'service' ? 'selected' : '' }}>Service</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Category <span class="text-danger">*</span></label>
        <select name="CategoryId" class="form-control">
            <option value="">-- None --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}"
                    {{ old('CategoryId', $item->CategoryId ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->Name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="Description" class="form-control">{{ old('Description', $item->Description ?? '') }}</textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Unit of Measure <span class="text-danger">*</span></label>
        <select name="UOM" class="form-control" required>
            <option value="">-- Select Unit of Measure --</option>
            @php
                $units = [
                    'pcs' => 'Pieces',
                    'kg' => 'Kilograms',
                    'ltr' => 'Liters',
                    'm' => 'Meters',
                    'box' => 'Box',
                    'set' => 'Set',
                    'hour' => 'Hour',
                    'day' => 'Day',
                    'service' => 'Service',
                ];
            @endphp
            @foreach($units as $key => $label)
                <option value="{{ $key }}" {{ old('UOM', $item->UOM ?? '') === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Currency <span class="text-danger">*</span></label>
        <select name="Currency" class="form-control" required>
            <option value="">-- Select Currency --</option>
            @php
                $currencies = ['KES', 'USD', 'EUR', 'GBP', 'UGX', 'TZS', 'ZAR']; // extend as needed
            @endphp
            @foreach($currencies as $currency)
                <option value="{{ $currency }}"
                    {{ old('Currency', $item->Currency ?? '') === $currency ? 'selected' : '' }}>
                    {{ $currency }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Unit Price <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="UnitPrice" class="form-control" value="{{ old('UnitPrice', $item->UnitPrice ?? '') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Service Scope</label>
        <textarea name="ServiceScope" class="form-control">{{ old('service_scope', $item->ServiceScope ?? '') }}</textarea>
    </div>

    <button class="btn btn-success" type="submit">Save</button>
</form>
