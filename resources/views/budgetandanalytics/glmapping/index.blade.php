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
                            {{--                            <th>GL Name</th>--}}
                            <th>Description</th>
                            <th>GL Type</th>
                            <th>Mapped to Budget Line</th>
                            <th>Active</th>
                            {{-- <th>Actions</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($gls as $gls)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$gls->AccountID}}</td>
                            <td class="text-break">{{$gls->Description}}</td>
                            {{--                            <td class="text-break">{{$gls->Description}}</td>--}}
                            <td>{{$gls->GLAccountTypeID}}</td>
                            <td><span class="badge bg-success">✅ Yes</span></td>
                            <td><span class="badge bg-success">✔</span></td>
                            {{-- <td>
                              <button class="btn btn-sm btn-info">🔍 View</button>
                              <button class="btn btn-sm btn-outline-primary">🔗 Map</button>
                            </td> --}}
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
        </div>
</div>
@endsection
