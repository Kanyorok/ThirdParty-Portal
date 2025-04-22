@extends('layouts.app')

@section('title', 'Create RFQ')

@section('content')
<div class="container">
    <h3>Create RFQ</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('rfqs.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Select Tender</label>
            <select name="TenderId" class="form-control" required>
                <option value="">-- Choose Tender --</option>
                @foreach($tenders as $tender)
                    <option value="{{ $tender->Id }}">{{ $tender->TenderNumber }}{{ $tender->Title }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Item Category</label>
            <select name="ItemCategoryId" id="ItemCategoryId" class="form-control" required>
                <option value="">-- Choose Category --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->Id }}">{{ $category->Name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Suppliers</label>
            <select name="SupplierIds[]" id="SupplierIds" class="form-control" multiple required>
                <!-- Dynamically filled by JS -->
            </select>
        </div>

        <button class="btn btn-success">Send RFQ</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('ItemCategoryId').addEventListener('change', function () {
        let categoryId = this.value;

        fetch('{{ route('rfqs.getSuppliers') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ category_id: categoryId })
        })
        .then(res => res.json())
        .then(data => {
            let supplierSelect = document.getElementById('SupplierIds');
            supplierSelect.innerHTML = '';

            data.forEach(supplier => {
                let opt = document.createElement('option');
                opt.value = supplier.Id;
                opt.innerHTML = supplier.SupplierName;
                supplierSelect.appendChild(opt);
            });
        });
    });
</script>
@endsection
