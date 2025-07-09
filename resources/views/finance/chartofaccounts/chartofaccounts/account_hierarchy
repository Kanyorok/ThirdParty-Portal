@extends('layouts.app')
@section('title', 'Account Hierarchy Viewer')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🌳 Account Hierarchy Viewer</h4>

    <div class="card">
        <div class="card-body">
            <div class="accordion" id="accountTree">
                @php $accordionIndex = 0; @endphp
                @foreach ($accounts->where('ParentGLID', null) as $parent)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $accordionIndex }}">
                            <button class="accordion-button {{ $accordionIndex > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $accordionIndex }}" aria-expanded="{{ $accordionIndex === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $accordionIndex }}">
                                {{ $parent->GLCode }} – {{ $parent->AccountName }}
                            </button>
                        </h2>
                        <div id="collapse{{ $accordionIndex }}" class="accordion-collapse collapse {{ $accordionIndex === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $accordionIndex }}" data-bs-parent="#accountTree">
                            <div class="accordion-body ps-4">
                                @include('finance.chartofaccounts.chartofaccounts.partials.subtree', [
                                    'accounts' => $accounts,
                                    'parentId' => $parent->GLCode
                                ])
                            </div>
                        </div>
                    </div>
                    @php $accordionIndex++; @endphp
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
