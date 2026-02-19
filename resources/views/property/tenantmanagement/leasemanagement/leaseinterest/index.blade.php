@extends('layouts.app')

@section('title','Property Interests')

@section('content')
<div class="container mt-4">

<div class="d-flex justify-content-between mb-3">
<h5>Tenant Interests</h5>
<a href="{{ route('property-interest.create') }}" class="btn btn-success btn-sm">
+ Add Interest
</a>
</div>

<table class="table table-bordered table-hover" id="leaseInterest">
<thead class="table-light">
<tr>
<th>Tenant</th>
<th>Property</th>
<th>Unit</th>
<th>Period</th>
<th>Frequency</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
@foreach($interests as $interest)
<tr>
<td>{{ $interest->tenant->thirdParty->ThirdPartyName ?? '-' }}</td>
<td>{{ $interest->property->PropertyName ?? '-' }}</td>
<td>{{ $interest->unit->UnitCode ?? '-' }}</td>
<td>
{{ $interest->InterestedStartDate }} to {{ $interest->InterestedEndDate }}
</td>
<td>{{ $interest->code->Description ?? '-' }}</td>
<td>
<a class="btn btn-sm btn-info"
href="{{ route('property-interest.show',$interest->Id) }}">View</a>

<a class="btn btn-sm btn-warning"
href="{{ route('property-interest.edit',$interest->Id) }}">Edit</a>

<form method="POST" action="{{ route('property-interest.destroy',$interest->Id) }}"
class="d-inline">
@csrf @method('DELETE')
<button class="btn btn-sm btn-danger"
onclick="return confirm('Delete this interest?')">Delete</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>

</div>
@endsection
@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#leaseInterest').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
