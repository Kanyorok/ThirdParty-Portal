@extends('layouts.app')
@section('title', 'CBS GL Accounts')
@section('content')
<div class="card mb-4">
  <div class="card-header bg-dark text-white">
    🧾 CBS GL Accounts
  </div>
  <div class="card-body">
    <p class="text-muted">Below is a list of General Ledger accounts synced from Core Banking System (CBS). You can monitor mapping status to budget lines and products.</p>

    <table class="table table-bordered table-striped table-hover align-middle">
      <thead class="table-light">
        @if($gls->count())
        <tr>
          <th>#</th>
          <th>GL Account No</th>
          <th>GL Name</th>
          <th>Description</th>
          <th>GL Type</th>
          <th>Mapped to Budget Line</th>
          <th>Mapped to Product</th> 
          <th>Active</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($gls as $gls)
        <tr>
          <td>{{$loop->iteration}}</td>
          <td>GL00{{$gls->Id}}</td>
          <td>{{$gls->Description}}</td>
          <td>{{$gls->Description}}</td>
          <td>{{$gls->GTType}}</td>
          <td><span class="badge bg-success">✅ Yes</span></td>
          <td><span class="badge bg-success">✅ Yes</span></td>
          <td><span class="badge bg-success">✔</span></td>
          <td>
            <button class="btn btn-sm btn-info">🔍 View</button>
            <button class="btn btn-sm btn-outline-primary">🔗 Map</button>
          </td>
        </tr>
        @endforeach
        <!-- Repeat rows as needed -->
      </tbody>
    </table>
    @else
        <div class="alert alert-info">
          No General Ledger Accounts to vview
        </div>
    @endif
  </div>
</div>
@endsection
