@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Banks</h1>
    <a href="{{ route('finance.bank.create') }}" class="btn btn-primary">Add New Bank</a>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Bank Name</th>
                <th>Short Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($banks as $bank)
                <tr>
                    <td>{{ $bank->BankName }}</td>
                    <td>{{ $bank->ShortName }}</td>
                    <td>
                        <a href="{{ route('finance.bank.show', $bank->BankID) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('finance.bank.edit', $bank->BankID) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('finance.bank.destroy', $bank->BankID) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
