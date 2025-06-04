@extends('layouts.app')
@section('title', 'Technical Criteria')
@section('content')

 
 <!-- Items Table -->
<div class="card shadow-sm mb-4">
<div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
<h5 class="card-title mb-3">📦 Criteria</h5>
        <h4></h4>
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addSectionModal">
    + Add Criteria
</button>
    </div>
<div class="table-responsive">
<table class="table table-bordered table-striped1 align-middle">
<thead class="table-light">
<tr>
<th>#</th>
<th>Criteria Name</th>
<th>Description</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
    @foreach ($criterias as $item)        
    <tr>
          <td>{{$loop->index+1}}</td>
          <td>{{$item->CriteriaName}}</td>
          <td>{{$item->Description}}</td>
          <td>
              <a href="#" class="btn btn-sm btn-outline-primary" title="Edit">
                  <i class="fas fa-edit"></i>
              </a>
              <form action="#" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                          onclick="return confirm('Are you sure you want to delete tender \'cr\'? This action cannot be undone.')">
                      <i class="fas fa-trash-alt"></i>
                  </button>
              </form>
          </td>
    </tr>
    @endforeach

</tbody>
<tfoot class="table-light fw-bold text-end">
</tfoot>
</table>
</div>
</div>
</div>


<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Add New Criteria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('criterias.store')}}" method="POST">
                @csrf
                @method('POST')
                <input type="hidden" name="section_id" value="{{ $sectionID }}">

                <div class="modal-body">
                  <div class="mb-3">
                        <label class="form-label">Criteria Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Experience, Compliance, Methodology">
                        @error('name')
                        <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                  </div>
                  <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="desc" rows="2" placeholder="Describe the purpose of this criteria"></textarea>
                        @error('desc')
                        <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                  </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button 
                        type="submit" 
                        class="btn btn-success" 
                        onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();"
                    >
                        Save Criteria
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
