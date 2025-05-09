@extends('layouts.app')
@section('title', 'accessmanagement')
@section('content')
<div class="container mt-5" style="max-width: 900px;">
  <h4 class="mb-4">🔐 Assign Access Permissions</h4>

  <form method="post" action="save_permission.php">
    <div class="mb-3">
      <label class="form-label">Select User or Role</label>
      <select class="form-select" name="target" required>
        <option value="">-- Choose --</option>
        <option value="user:john_doe">User: John Doe</option>
        <option value="role:admin">Role: Admin</option>
        <option value="role:editor">Role: Editor</option>
        <option value="role:viewer">Role: Viewer</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Access Level Target</label>
      <select class="form-select" name="target_type" required>
        <option value="module">Module</option>
        <option value="folder">Folder</option>
        <option value="document">Specific Document</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Target Name or ID</label>
      <input type="text" class="form-control" name="target_id" placeholder="e.g., HR Module, Folder ID 23, Document ID 101" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Permission Level</label>
      <select class="form-select" name="permission" required>
        <option value="">-- Select Permission --</option>
        <option value="view">View Only</option>
        <option value="edit">Edit</option>
        <option value="delete">Delete</option>
        <option value="full">Full Access</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Assign Permission</button>
  </form>
</div>
@endsection