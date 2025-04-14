<form method="POST" action="{{ $route }}">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-control" required>
            <option value="good" {{ old('type', $item->type ?? '') === 'good' ? 'selected' : '' }}>Good</option>
            <option value="service" {{ old('type', $item->type ?? '') === 'service' ? 'selected' : '' }}>Service</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-control">
            <option value="">-- None --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}"
                    {{ old('category_id', $item->category_id ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control">{{ old('description', $item->description ?? '') }}</textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Unit of Measure</label>
        <input type="text" name="unit_of_measure" class="form-control" value="{{ old('unit_of_measure', $item->unit_of_measure ?? '') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Unit Price</label>
        <input type="number" step="0.01" name="unit_price" class="form-control" value="{{ old('unit_price', $item->unit_price ?? '') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Service Scope</label>
        <textarea name="service_scope" class="form-control">{{ old('service_scope', $item->service_scope ?? '') }}</textarea>
    </div>

    <button class="btn btn-success" type="submit">Save</button>
</form>
