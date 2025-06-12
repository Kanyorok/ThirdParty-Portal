@extends('layouts.app')
@section('title', 'Create Receipt')
@section('content')
<div class="card mb-4">
  <div class="card-header bg-success text-white">📥 Post Goods Receipt</div>
  <div class="card-body">
    <form>
      <div class="row mb-3">
        <div class="col">
          <label class="form-label">Transfer Ref</label>
          <select class="form-select">
            <option>Select Transfer</option>
          </select>
        </div>
        <div class="col">
          <label class="form-label">Received By</label>
          <input type="text" class="form-control" />
        </div>
        <div class="col">
          <label class="form-label">Receive Date</label>
          <input type="date" class="form-control" />
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Items Received</label>
        <table class="table table-bordered">
          <thead class="table-light">
            <tr>
              <th>Product</th>
              <th>Qty Received</th>
              <th>Qty Damaged</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><input class="form-control" /></td>
              <td><input class="form-control" /></td>
              <td><input class="form-control" /></td>
              <td><input class="form-control" /></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mb-3">
        <label class="form-label">General Remarks</label>
        <textarea class="form-control" rows="2"></textarea>
      </div>

      <button class="btn btn-success">Post GRN</button>
    </form>
  </div>
</div>

@endsection