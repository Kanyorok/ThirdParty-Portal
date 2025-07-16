<div class="table-responsive">
    <h4 class="mb-3">Role: {{ $role->name }}</h4>

    @if ($role->users->isEmpty())
        <p class="text-muted">No users assigned to this role.</p>
    @else
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($role->users as $index => $user)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $user->UserID }}</td>
                        <td>{{ $user->Name }}</td>
                        <td>{{ $user->Email }}</td>
                        <td>{{ $user->Phone }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
