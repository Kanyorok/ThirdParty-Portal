<div class="row">
    <div class="col-5 align-content-center text-center">
        <div>
            {!! $employee->getImage('class="img-fluid img-thumbnail  mb-2" width="100" height="100"') !!}
        </div>
    </div>
    <div class="col-7 align-content-center ">
        <ul class="list-group list-group-flush">
            <li class="list-group-item p-1">
                <div
                    class="h4 text-primary text-uppercase text-decoration-underline">{{ strtoupper($employee->EmployeeID) }}</div>
            </li>
            <li class="list-group-item p-1">{{ $employee->full_name }}</li>
            <li class="list-group-item p-1">{{ $employee->JobTitle }}</li>
        </ul>
    </div>
</div>
