@extends('layouts.app')
@section('title', 'Business Drivers Types')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There were some error(s) with your submission:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card p-3">
        <div class="mb-2 d-flex justify-content-between">
            <a href="{{ route('budgetdrivers.create') }}" class="btn btn-success" data-bs-toggle="modal"
               data-bs-target="#addDriverModal">
                ➕ Add Driver</a>

        </div>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($drivers as $item)
                <tr>
                    <td>{{ $loop->index+1 }}.</td>
                    <td>{{ $item->DriverName }}</td>
                    @if ($item->IsActive==1)
                        <td><span class="badge bg-success">Active</span></td>
                    @else
                        <td><span class="badge bg-danger">Inactive</span></td>
                    @endif
                    <td>
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                data-bs-target="#editDriverModal-{{ $item->Id }}">
                            ✏️ Edit
                        </button>
                        <form method="POST" action="{{ route('budgetdriverssetup.destroy',$item->Id) }}"
                              class="delete-form d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger delete-btn">🗑 Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>



    <!-- Add Driver Modal -->
    <div class="modal fade" id="addDriverModal" tabindex="-1" aria-labelledby="addSectionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Driver Setup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('budgetdriverssetup.store') }}" method="POST">
                        @csrf
                        @method('POST')
                        <div class="card p-4">
                            <div class="mb-3">
                                <label for="driverName" class="form-label">Driver Name</label>
                                <input type="text" class="form-control" name="DriverName" id="driverName"
                                       placeholder="e.g., Financial, Operational">
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" name="IsActive" type="checkbox" id="isActive" checked>
                                <label class="form-check-label" for="isActive">Active</label>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel
                            </button>
                            <button type="submit" class="btn btn-primary"
                                    onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Updating...'; this.form.submit(); }">
                                💾 Save Driver
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Driver Modal -->
        @foreach ($drivers as $item)
            <div class="modal fade" id="editDriverModal-{{ $item->Id }}" tabindex="-1" aria-labelledby="addSectionLabel"
                 aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content rounded-3 shadow">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addItemModalLabel">Driver Setup</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('budgetdriverssetup.store') }}" method="POST">
                                @csrf
                                @method('POST')
                                <div class="card p-4">
                                    <div class="mb-3">
                                        <label for="driverName" class="form-label">Driver Name</label>
                                        <input type="text" class="form-control" value="" name="DriverName"
                                               placeholder="e.g., Financial, Operational">
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" name="IsActive" type="checkbox" checked>
                                        <label class="form-check-label" for="isActive">Active</label>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary"
                                            onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Updating...'; this.form.submit(); }">
                                        💾 Save Driver
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach


                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const deleteForms = document.querySelectorAll('.delete-form');

                        deleteForms.forEach(form => {
                            form.addEventListener('submit', function (e) {
                                e.preventDefault(); // Stop form from submitting immediately

                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: "This action cannot be undone!",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#3085d6',
                                    confirmButtonText: 'Yes, delete it!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        form.submit(); // Submit the form if confirmed
                                    }
                                });
                            });
                        });
                    });
                </script>

@endsection
