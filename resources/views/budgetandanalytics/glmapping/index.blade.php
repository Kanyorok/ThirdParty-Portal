@extends('layouts.app')
@section('title', 'CBS GL Accounts')
@section('content')
    <div class="card mb-4">
{{--        <div class="card-header bg-dark text-white">--}}
{{--            🧾 CBS GL Accounts--}}
{{--        </div>--}}
        <div class="card-body">
            <p class="text-muted">
                Below is a list of General Ledger accounts synced from Core Banking System (CBS).
                You can monitor mapping status to budget lines and products.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-light">
                    @if($gls->count())
                        <tr>
                            <th>#</th>
                            <th>GL Account No</th>
                            <th>Description</th>
                            <th>GL Type</th>
{{--                            <th>Mapped to Budget Line</th>--}}
                            <th>Active</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($gls as $item)
                        <tr>
                            <td>{{ ($gls->currentPage() - 1) * $gls->perPage() + $loop->iteration }}</td>
                            <td>{{ $item->AccountID }}</td>
                            <td class="text-break">{{ $item->Description }}</td>
                            <td>{{ $item->GLAccountTypeID }}</td>
{{--                            <td><span class="badge bg-success">✅ Yes</span></td>--}}
                            <td><span class="badge bg-success">✔</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                    @else
                        <div class="alert alert-info">
                            No General Ledger Accounts to view.
                        </div>
                    @endif
                </table>
            </div>

            {{-- Pagination controls --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $gls->links() }}
            </div>


        </div>
</div>
@endsection
