@extends('layouts.app')
@section('title', 'Physical Stock Take')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📝 Physical Stock Take</h4>
  <!-- Header Info -->
   <form action="{{ route('stocktake.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
  <div class="row mb-3">
    <div class="col-md-3">
      <label class="form-label">📍 Branch</label>
            <select name="BranchId" id="branch-select" class="form-select" required>
              <option value="">-- Select Branch --</option>
              @foreach ($branches as $branch)
                <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
              @endforeach
            </select>
</div>
    <div class="col-md-3">
      <label class="form-label">🏢 Store</label>
            <select name="StoreId" id="store-select" class="form-select" required>
              <option value="">-- Select Store --</option>
            </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">🧑‍💼 Counted By</label>
      <input type="text" class="form-control" id="countedBy" placeholder="Enter name"name="CountedBy">
    </div>
    <div class="col-md-3">
      <label class="form-label">📅 Count Date</label>
      <input type="date" class="form-control" id="countedDate" value="2025-05-02"name="CountDate">
    </div>
  </div>

  <!-- Items Grid -->
  


<script>
  //gettign stores per Branch
    document.addEventListener('DOMContentLoaded', function () {
      const branchSelect = document.getElementById('branch-select');
      const storeSelect = document.getElementById('store-select');

      branchSelect.addEventListener('change', function () {
          const branchId = this.value;

          // Reset type dropdown
          storeSelect.innerHTML = '<option value="">-- Select a store --</option>';

          if (branchId) {
              // Construct the URL from the named route
              const url = `{{ route('getstore', ':Id') }}`.replace(':Id', branchId);

              fetch(url)
                  .then(response => response.json())
                  .then(stores => {
                      stores.forEach(store => {
                          const option = document.createElement('option');
                          option.value = store.Id;
                          option.textContent = store.StoreName;
                          storeSelect.appendChild(option);
                      });
                  })
                  .catch(error => console.error('Error loading the store:', error));
          }
      });
  });
  //compare quantity
  document.querySelectorAll('.counted-qty').forEach((input, index) => {
    input.addEventListener('input', function () {
      const row = input.closest('tr');
      const ActualQty = parseFloat(row.querySelector('.system-qty').innerText) || 0;
      const countedQty = parseFloat(input.value) || 0;
      const variance = countedQty - ActualQty;
      row.querySelector('.variance').innerText = variance;
    });
  });
</script>
@endsection