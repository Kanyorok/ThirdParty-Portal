@extends('layouts.app')
@section('title', 'New Budget Line & GL Mapping')
@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>There were some errors with your submission:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="card mb-4">

  <div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('budgetlinemapping.create') }}" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addLineModal">
    + New Budget Line</a>
   </div>
<div class="card-header bg-secondary text-white">📄 Budget Lines List</div>
  <div class="card-body">
    <p class="text-muted mb-2">Manage your budget lines and their associated GL accounts here. You can add, edit, or delete budget lines as needed.Each budget line can be linked to multiple CBS GL accounts. Ensure you map them correctly for accurate financial reporting.</p>
    <table class="table table-bordered  table-hover">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Budget Line Category</th>
          <th>Line Name</th>
          <th>Description</th>
          <th>CBS GLs Mapped</th>
          {{-- <th>ERP GLs Mapped</th> --}}
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($budgetLines as $item)
            <tr>
              <td>{{ $loop->index+1 }}.</td>
              <td>{{ $item->category->CategoryName ?? 'N/A' }}</td>
              <td>
                {{ $item->LineName }}
                {{-- @if ($item->IsDefault)
                  <span class="badge bg-success">Default</span>
                @endif --}}
              </td>
              <td style="white-space: normal; break-word; max-width= 300px;">{{ $item->Description }}</td>
                <td>
                @foreach($item->glAccounts as $gl)
                  <small><div>- {{ $gl->Description }}</div></small>
                @endforeach
                </td>
              {{-- <td>ERP1001</td> --}}
              <td>
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editLineModal{{ $item->Id }}">
                  ✏ Edit</button>
                  <form method="POST" action="{{ route('budgetlinemapping.destroy',$item->Id) }}" class="delete-form d-inline">
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
</div>



<!-- Add Line Modal -->
<div class="modal fade" id="addLineModal" tabindex="-1" aria-labelledby="addSectionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Add New Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <div class="modal-body">
                  <form action="{{ route('budgetlinemapping.store') }}" method="POST">
                    @csrf
                    @method('POST')
                    <!-- 🧾 Budget Line Entry -->

                    <div class="mb-3">
                      <label class="form-label">Budget Category</label>
                      <select class="form-select" name="BudgetLineCategoryID" required>
                        <option selected disabled>-- Select Budget Category --</option>
                        @foreach ($budgetCategories as $category)
                            <option value="{{ $category->Id }}">{{ $category->CategoryName }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Budget Line Name</label>
                      <input type="text" class="form-control" name="LineName" placeholder="e.g. Interest Income, Loan Fees" required>
                      @error('LineName')
                          <div class="text-danger">{{ $message }}</div>
                      @enderror
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Description</label>
                      <textarea class="form-control" rows="2" name="Description" placeholder="Describe this budget line..."></textarea>
                      @error('Description')
                          <div class="text-danger">{{ $message }}</div>
                      @enderror
                    </div>

                    <!-- 🔗 CBS GL Mapping -->
                    <h6>🔗 CBS GL Accounts (Multiple)</h6>
                    <div class="mb-3">
                      <label class="form-label">Select CBS GLs</label>
                      <select multiple class="form-select" name="GLS[]" required>
                        @foreach ($gls as $item)
                            <option value="{{ $item->Id }}">GL00{{ $item->Id }} - {{ $item->Description }}</option>
                        @endforeach
                      </select>
                      <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple GLs.</div>
                    </div>

                    <!-- 🔗 ERP GL Mapping -->
                    {{-- <div class="mb-3">
                      <label class="form-label">ERP GL Account (Optional)</label>
                      <select class="form-select">
                        <option selected disabled>-- Select ERP GL --</option>
                        <option value="ERP001">ERP001 - Interest Revenue</option>
                        <option value="ERP002">ERP002 - Other Income</option>
                      </select>
                    </div> --}}

                    <!-- 🔘 Primary Flag -->
                    <div class="form-check mb-3">
                      <input class="form-check-input" name="IsDefault" type="checkbox" id="primaryCheck">
                      <label class="form-check-label" for="primaryCheck">
                        Mark as Primary Mapping
                      </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Submitting...'; this.form.submit(); }">
                      💾 Save Budget Line & Mapping</button>
                </div>
            </form>
        </div>
    </div>
</div>



@foreach ($budgetLines as $line)
<!-- Edit Line Modal for Line ID: {{ $line->Id }} -->
<div class="modal fade" id="editLineModal{{ $line->Id }}" tabindex="-1" aria-labelledby="editLineModalLabel{{ $line->Id }}" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content rounded-3 shadow">
      <div class="modal-header">
        <h5 class="modal-title">Edit Budget Line</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="{{ route('budgetlinemapping.update', $line->Id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">

          <!-- Budget Line Name -->
          <div class="mb-3">
            <label class="form-label">Budget Line Name</label>
            <input type="text" class="form-control" name="LineName" value="{{ $line->LineName }}" required>
            @error('LineName')
              <div class="text-danger">{{ $message }}</div>
            @enderror
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="Description" rows="2">{{ $line->Description }}</textarea>
            @error('Description')
              <div class="text-danger">{{ $message }}</div>
            @enderror
          </div>

          <!-- CBS GL Mapping -->
          <h6>🔗 CBS GL Accounts</h6>
          <div class="mb-3">
            <label class="form-label">Select CBS GLs</label>
            <select multiple class="form-select" name="GLS[]" required>
              @php
                  $selectedGLs = $line->glAccounts->pluck('Id')->toArray();
              @endphp
              @foreach ($gls as $item)
                <option value="{{ $item->Id }}" @if(in_array($item->Id, $selectedGLs)) selected @endif>
                  GL00{{ $item->Id }} - {{ $item->Description }}
                </option>
              @endforeach
            </select>
            <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple GLs.</div>
          </div>

          <!-- Primary Checkbox -->
          <div class="form-check mb-3">
            <input class="form-check-input" name="IsDefault" type="checkbox" id="primaryCheck{{ $line->Id }}" @checked($line->IsDefault)>
            <label class="form-check-label" for="primaryCheck{{ $line->Id }}">
              Mark as Primary Mapping
            </label>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"
            onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Updating...'; this.form.submit(); }">
            💾 Update Budget Line
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