@extends('layouts.app')

@section('content')
<div class="container-fluid">
	<div class="row mb-3">
		<div class="col">
			<h3>Licensing</h3>
			@if(session('success'))
				<div class="alert alert-success">{{ session('success') }}</div>
			@endif
			@if(session('error'))
				<div class="alert alert-danger">{{ session('error') }}</div>
			@endif
			<div class="card mb-4">
				<div class="card-body">
					<p>Status: <strong>{{ $license->isValid() ? 'Valid' : 'Invalid ('.$license->reason.')' }}</strong></p>
					@if($license->isValid())
						<p>Expires: {{ $license->expiresAt }}</p>
						<p>Modules: {{ implode(', ', $license->allowedModules) }}</p>
					@endif
				</div>
			</div>

			<div class="card">
				<div class="card-header">Upload License</div>
				<div class="card-body">
					<form method="post" action="{{ route('admin.license.store') }}">
						@csrf
						<div class="mb-3">
							<label class="form-label">License ID</label>
							<input type="text" class="form-control" name="license_id" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Public Key ID</label>
							<input type="text" class="form-control" name="public_key_id" value="{{ config('licensing.public_key_id') }}" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Payload (JSON)</label>
							<textarea class="form-control" name="payload" rows="8" required></textarea>
						</div>
						<div class="mb-3">
							<label class="form-label">Signature (Base64)</label>
							<input type="text" class="form-control" name="signature" required>
						</div>
						<button class="btn btn-primary" type="submit">Upload</button>
					</form>
				</div>
			</div>

			<div class="card mt-4">
				<div class="card-header">Recent Licenses</div>
				<div class="card-body">
					<table class="table table-sm">
						<thead>
							<tr>
								<th>ID</th>
								<th>LicenseId</th>
								<th>Status</th>
								<th>CreatedOn</th>
							</tr>
						</thead>
						<tbody>
						@foreach($records as $r)
							<tr>
								<td>{{ $r->Id }}</td>
								<td>{{ $r->LicenseId }}</td>
								<td>{{ $r->Status }}</td>
								<td>{{ $r->CreatedOn }}</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection