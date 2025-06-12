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
            <select name="BranchId" class="form-select" required>
              <option value="">-- Select Branch --</option>
              @foreach ($items as $item)
                <option value="{{ $item-> Id }}">{{ $item->Branch }}</option>
              @endforeach
            </select>
</div>
    <div class="col-md-3">
      <label class="form-label">🏢 Store</label>
            <select name="StoreId" class="form-select" required>
              <option value="">-- Select Store --</option>
              @foreach ($items as $item)
                <option value="{{ $item->Id }}">{{ $item->Store }}</option>
              @endforeach
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
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Item Code</th>
          <th>Item Name</th>
          <th>System Qty</th>
          <th>Counted Qty</th>
          <th>Variance</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody id="stockTakeBody">
        <tr>
          <td>1</td>
          <td>ITM-001</td>
          <td>A4 Paper</td>
          <td><span class="system-qty">120</span></td>
          <td><input type="number" class="form-control counted-qty" value="120"></td>
          <td><span class="variance fw-bold text-danger">0</span></td>
          <td><input type="text" class="form-control" placeholder="Optional"></td>
        </tr>
        <tr>
          <td>2</td>
          <td>ITM-002</td>
          <td>Toner Cartridge</td>
          <td><span class="system-qty">10</span></td>
          <td><input type="number" class="form-control counted-qty" value="10"></td>
          <td><span class="variance fw-bold text-danger">0</span></td>
          <td><input type="text" class="form-control" placeholder="Optional"></td>
        </tr>
      </tbody>
    </table>
  </div>
    <button class="btn btn-success mt-3">✅ Submit Stock Count</button>   
  </div>
</div>

<script>
  document.querySelectorAll('.counted-qty').forEach((input, index) => {
    input.addEventListener('input', function () {
      const row = input.closest('tr');
      const systemQty = parseFloat(row.querySelector('.system-qty').innerText) || 0;
      const countedQty = parseFloat(input.value) || 0;
      const variance = countedQty - systemQty;
      row.querySelector('.variance').innerText = variance;
    });
  });
</script>
</form>
@endsection