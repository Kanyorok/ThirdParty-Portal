@extends('layouts.app')
@section('title','New Reallocation')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('budgetandanalytics.reallocation.allocate') }}">
                    @csrf
                    @method('GET')

                    <div class="row">
                        <!-- Budget -->
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Budget</label>
                            <select name="BudgetID" class="form-select" required>
                                <option value="">-- Select Budget --</option>
                                @foreach($budgets as $budget)
                                    <option value="{{ $budget->Id }}">{{ $budget->Name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Reallocation Type -->
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Reallocation Type</label>
                            <select name="ReallocationType" id="reallocationType" class="form-select" required>
                                <option value="">-- Choose Type --</option>
                                <option value="Branch">Within Branch</option>
                                @if($isHeadOffice)
                                    <option value="Department">Within Department</option>
                                    <option value="Cross-Department">Across Departments</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <!-- Branch -->
                    <div class="mb-3" id="branchSection" style="display:none;">
                        <label class="form-label">Branch</label>
                        <select name="BranchID" id="branchSelect" class="form-select" required>
                            <option value="">-- Select Branch --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <!-- From Department -->
                        <div class="mb-3 col-md-6" id="departmentSection" style="display:none;">
                            <label class="form-label">From Department</label>
                            <select name="DepartmentID" id="departmentSelect" class="form-select" required>
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->Id }}">{{ $dept->Name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- To Department (Cross-Department) -->
                        <div class="mb-3 col-md-6" id="toDepartmentSection" style="display:none;">
                            <label class="form-label">To Department</label>
                            <select name="ToDepartmentID" id="toDepartmentSelect" class="form-select" required>
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->Id }}">{{ $dept->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="text-end" id="nextButtonContainer" style="display:none;">
                        <button type="submit" class="btn btn-success"
                                onclick="if(this.form.checkValidity()){
                                    this.disabled = true;
                                    this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Please Wait...';
                                    this.form.submit();
                                }">
                            <i class="fas fa-forward me-1"></i> Next
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const reallocationType = document.getElementById('reallocationType');
        const branchSection = document.getElementById('branchSection');
        const departmentSection = document.getElementById('departmentSection');
        const toDepartmentSection = document.getElementById('toDepartmentSection');
        const nextButtonContainer = document.getElementById('nextButtonContainer');

        reallocationType.addEventListener('change', function () {
            let type = this.value;

            // Reset sections
            branchSection.style.display = 'none';
            departmentSection.style.display = 'none';
            toDepartmentSection.style.display = 'none';

            // Reset required attributes
            document.getElementById('branchSelect').required = false;
            document.getElementById('departmentSelect').required = false;
            document.getElementById('toDepartmentSelect').required = false;

            if (type === 'Branch') {
                branchSection.style.display = 'block';
                document.getElementById('branchSelect').required = true;
            } else if (type === 'Department') {
                departmentSection.style.display = 'block';
                document.getElementById('departmentSelect').required = true;
            } else if (type === 'Cross-Department') {
                departmentSection.style.display = 'block';
                toDepartmentSection.style.display = 'block';
                document.getElementById('departmentSelect').required = true;
                document.getElementById('toDepartmentSelect').required = true;
            }

            // Show Next button only if a type is selected
            nextButtonContainer.style.display = type ? 'block' : 'none';
        });

        document.getElementById('reallocationType').addEventListener('change', function () {
            let type = this.value;
            let deptSection = document.getElementById('departmentSection');
            let toDeptSection = document.getElementById('toDepartmentSection');

            deptSection.style.display = 'none';
            toDeptSection.style.display = 'none';

            // Reset classes first
            deptSection.classList.remove('col-md-6', 'col-12');

            if (type === 'Department') {
                // Show full width
                deptSection.style.display = 'block';
                deptSection.classList.add('col-12');
            } else if (type === 'Cross-Department') {
                // Show half width for both
                deptSection.style.display = 'block';
                deptSection.classList.add('col-md-6');
                toDeptSection.style.display = 'block';
            }
        });
    </script>
@endsection
