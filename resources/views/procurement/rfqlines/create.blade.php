@extends('layouts.app')
@section('title', 'Create RFQ')
@section('content')

<div class="container">
    <h3>Create RFQ</h3>

    <div class="container mt-3">
        <form method="POST" action="{{ route('rfqs.store') }}">
            @csrf

            <div class="card mb-3">
                <div class="card-body">
                    
                    {{-- Requisition Dropdown --}}
                    <div class="mb-3">
                        <label>Requisition</label>
                        <select id="requisitionDropdown" class="form-control" required>
                            <option value="">-- Select Requisition --</option>
                            @foreach($requisitions as $req)
                                <option value="{{ $req->Id }}">{{ 'Requisition #' . $req->RequisitionNo }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Item Category Dropdown (Initially empty) --}}
                    <div class="mb-3">
                        <label>Item Category</label>
                        <select name="ItemCategoryId" id="categoryDropdown" class="form-control" required disabled>
                            <option value="">-- Select Category --</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="Comments">Comments<span class="text-danger">*</span></label>
                        <textarea name="Comments" class="form-control" required></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="SubmissionDeadline">Submission Deadline <span class="text-danger">*</span></label>
                        <input type="date" name="SubmissionDeadline" class="form-control" required min="{{ date('Y-m-d') }}">
                    </div> 
                </div>
            </div>
            <button class="btn btn-success" type="submit">Save</button>
        </form>
    </div>
</div>

{{-- JavaScript Section --}}
<script>
    document.getElementById('requisitionDropdown').addEventListener('change', function () {
        const reqId = this.value;
        const categoryDropdown = document.getElementById('categoryDropdown');

        // Clear existing options
        categoryDropdown.innerHTML = '<option value="">-- Select Category --</option>';
        categoryDropdown.disabled = true;

        if (reqId) {
            fetch(`/requisition/${reqId}/categories`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.Name;
                        categoryDropdown.appendChild(option);
                    });
                    categoryDropdown.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching categories:', error);
                });
        }
    });
</script>
@endsection
