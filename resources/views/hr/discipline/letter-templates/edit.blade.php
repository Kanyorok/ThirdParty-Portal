@extends('layouts.app')

@section('title', 'Edit Letter Template')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Letter Template</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.discipline.letter-templates.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('hr.discipline.letter-templates.update', $template->Id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Letter Type *</label>
                        <select name="LetterType" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($letterTypes as $type)
                                <option value="{{ $type }}" @selected(old('LetterType', $template->LetterType) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Template</label>
                        <select name="TemplateID" class="form-select">
                            <option value="">Select</option>
                            @foreach($legalTemplates as $legalTemplate)
                                <option value="{{ $legalTemplate->Id }}" @selected(old('TemplateID', $template->TemplateID) == $legalTemplate->Id)>
                                    {{ $legalTemplate->Title }} ({{ $legalTemplate->DocumentType }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" @checked(old('IsActive', $template->IsActive))>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.discipline.letter-templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Mapping</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
