@extends('layouts.app')
@section('title', 'CBS Data Synchronization')
@section('content')
@stack('scripts')
<div class="container mt-4">
  <h4 class="mb-4">🔄 CBS Data Synchronization Dashboard</h4>

  <div class="row g-3">
    <!-- GL Accounts Sync -->
    <div class="col-md-6">
      <div class="card border-success">
        <div class="card-body">
          <h5 class="card-title">🧾 GL Accounts</h5>
          <p class="card-text text-muted">Last Synced: <strong>2025-05-30 04:45 PM</strong></p>
          <button class="btn btn-success" onclick="syncEntity('gl')">Sync Now</button>
        </div>
      </div>
    </div>

    <!-- Products Sync -->
    <div class="col-md-6">
      <div class="card border-primary">
        <div class="card-body">
          <h5 class="card-title">🏦 CBS Products</h5>
          <p class="card-text text-muted">Last Synced: <strong>2025-05-30 03:10 PM</strong></p>
          <button class="btn btn-primary" onclick="syncEntity('products')">Sync Now</button>
        </div>
      </div>
    </div>

    <!-- Branches Sync -->
    <div class="col-md-6">
      <div class="card border-info">
        <div class="card-body">
          <h5 class="card-title">🏢 Branches</h5>
          <p class="card-text text-muted">Last Synced: <strong>2025-05-29 10:20 AM</strong></p>
          <button class="btn btn-info" onclick="syncEntity('branches')">Sync Now</button>
        </div>
      </div>
    </div>

    <!-- Officers Sync -->
    <div class="col-md-6">
      <div class="card border-warning">
        <div class="card-body">
          <h5 class="card-title">🧑‍💼 Officers</h5>
          <p class="card-text text-muted">Last Synced: <strong>2025-05-30 08:15 AM</strong></p>
          <button class="btn btn-warning" onclick="syncEntity('officers')">Sync Now</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Sync Feedback -->
  <div id="syncStatus" class="alert alert-success mt-4 d-none">
    ✅ Sync request sent successfully.
  </div>
</div>

<script>
  function syncEntity(entity) {
    // Simulated sync logic – in actual system, send an AJAX request
    document.getElementById("syncStatus").classList.remove("d-none");
    document.getElementById("syncStatus").innerText = `🔄 Sync initiated for ${entity.toUpperCase()}. Check logs for updates.`;
  }
</script>
@endsection
