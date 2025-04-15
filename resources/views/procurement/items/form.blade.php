<form method="POST" action="{{ $route }}">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="Name" class="form-control" value="{{ old('Name', $item->Name ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Type</label>
        <select name="Type" class="form-control" required>
            <option value="goods" {{ old('Type', $item->Type ?? '') === 'goods' ? 'selected' : '' }}>Goods</option>
            <option value="services" {{ old('Type', $item->Type ?? '') === 'services' ? 'selected' : '' }}>Services</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Category</label>
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
        <label class="form-label">Description</label>
        <textarea name="Description" class="form-control">{{ old('Description', $item->Description ?? '') }}</textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Unit of Measure</label>
        <input type="text" name="UOM" class="form-control" value="{{ old('UOM', $item->UOM ?? '') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Unit Price</label>
        <input type="number" step="0.01" name="UnitPrice" class="form-control" value="{{ old('UnitPrice', $item->UnitPrice ?? '') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Service Scope</label>
        <textarea name="ServiceScope" class="form-control">{{ old('service_scope', $item->ServiceScope ?? '') }}</textarea>
    </div>

    <button class="btn btn-success" type="submit">Save</button>
</form>
