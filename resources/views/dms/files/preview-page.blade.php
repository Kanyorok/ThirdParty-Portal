@extends('layouts.app')

@section('title', $file->Name ?? 'Document Preview')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <h5 class="mb-0">{{ $file->Name ?? 'Document Preview' }}</h5>
                <form action="{{ route('file-download.store', [$file->DocumentId]) }}" method="post" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Download</button>
                </form>
            </div>
            <div class="card-body">
                @include('dms.files.preview')
            </div>
        </div>
    </div>
@endsection
