@extends('layouts.app')
@section('title','Notice Register')
@section('content')

<div class="container my-5">
  <h2 class="text-primary mb-4">➕ Issue Notice</h2>

  <form action="create.php" method="POST" class="border p-4 rounded shadow-sm bg-light">
    <div class="mb-3">
      <label for="type" class="form-label">Notice Type</label>
      <select name="type" id="type" class="form-select" required>
        <option value="">Select Type</option>
        <option value="Claim">Claim</option>
        <option value="Default">Default</option>
        <option value="Warning">Warning</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="party" class="form-label">Party Name</label>
      <input type="text" name="party" id="party" class="form-control" placeholder="e.g. XYZ Ltd." required>
    </div>

    <div class="mb-3">
      <label for="date" class="form-label">Issue Date</label>
      <input type="date" name="date" id="date" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="ref" class="form-label">Reference ID</label>
      <input type="text" name="ref" id="ref" class="form-control" placeholder="e.g. NDEF-007" required>
    </div>

    <button type="submit" class="btn btn-primary">📤 Issue Notice</button>
    <a href="{{route('register.index')}} " class="btn btn-outline-secondary ms-2">← Back to Register</a>
  </form>
</div>

@endsection