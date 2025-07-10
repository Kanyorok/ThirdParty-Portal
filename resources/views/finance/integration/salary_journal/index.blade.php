@extends('layouts.app')
@section('title', 'Salary Journal Template Setup')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">📄 Salary Journal Template Setup</h4>

        <a href="{{ route('salary-journal-templates.create') }}" class="btn btn-primary mb-3">➕ Add Template</a>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Template</th>
                <th>Component</th>
                <th>Debit Account</th>
                <th>Credit Account</th>
                <th>Branch</th>
                <th>Department</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($templates as $template)
                <tr>
                    <td>{{ $template->TemplateName }}</td>
                    <td>{{ $template->ComponentCode }}</td>
                    <td>{{ $template->DebitGLAccount }}</td>
                    <td>{{ $template->CreditGLAccount }}</td>
                    <td>{{ $template->BranchCode }}</td>
                    <td>{{ $template->DepartmentCode }}</td>
                    <td>
                        <span class="badge bg-{{ $template->IsActive ? 'success' : 'secondary' }}">
                            {{ $template->IsActive ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="#" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
