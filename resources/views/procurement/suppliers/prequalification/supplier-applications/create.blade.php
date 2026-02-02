@extends('layouts.app')

@section('title', 'Create Manual Prequalification Application')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        /* Globally hide any original select element converted to Select2 */
        .select2-hidden-accessible {
            border: 0 !important;
            clip: rect(0 0 0 0) !important;
            height: 1px !important;
            margin: -1px !important;
            overflow: hidden !important;
            padding: 0 !important;
            position: absolute !important;
            width: 1px !important;
        }

        /* Ensure Select2 container displays properly */
        .select2-container {
            width: 100% !important;
        }
    </style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Create Manual Prequalification Application</h4>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    <i class="feather icon-arrow-left"></i> Back
                </a>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('prequalification.applications.store-manual') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <!-- Round Selection -->
                        <div class="col-md-6 mb-3">
                            <label for="round_id" class="form-label">Prequalification Round <span class="text-danger">*</span></label>
                            <select name="round_id" id="round_id" class="form-control" required>
                                <option value="">Select Round</option>
                                @foreach($rounds as $round)
                                    <option value="{{ $round->RoundID }}" 
                                        {{ (old('round_id') == $round->RoundID || (isset($prequalificationRound) && $prequalificationRound->RoundID == $round->RoundID)) ? 'selected' : '' }}>
                                        {{ $round->Title }} ({{ $round->StartDate?->format('d M Y') ?? 'N/A' }} - {{ $round->EndDate?->format('d M Y') ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('round_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- Supplier Selection -->
                        <div class="col-md-6 mb-3">
                            <label for="supplier_id" class="form-label">Select Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="supplier_id" class="form-control" required>
                                <option value="">Search Supplier...</option>
                            </select>
                            @error('supplier_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Categories Selection -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Select Categories</h5>
                            <p class="text-muted small">Select the categories this supplier is applying for in this round.</p>
                            
                            <div id="categories-container" class="border p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                                <div class="text-center text-muted py-3" id="categories-loader">
                                    Select a Supplier first to load their categories...
                                </div>
                                <div id="categories-list" style="display: none;">
                                    <!-- Dynamic content -->
                                </div>
                            </div>
                            @error('category_ids') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather icon-save"></i> Submit Application
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/select2/js/select2.min.js') }}?v={{ time() }}"></script>
<script>
$(document).ready(function() {
    // Initialize Round dropdown as regular Select2 (no AJAX needed)
    $('#round_id').select2({
        placeholder: "Select Round",
        allowClear: true,
        width: '100%'
    });

    // Supplier Search via AJAX (Server-side search)
    var $supplierSelect = $('#supplier_id');

    // Destroy any existing instances
    if ($supplierSelect.hasClass('select2-hidden-accessible')) {
        $supplierSelect.select2('destroy');
    }
    
    // Aggressively remove any leftover containers
    $supplierSelect.siblings('.select2-container').remove();
    $('.select2-container--default').remove(); // remove any floating orphans

    $supplierSelect.select2({
            placeholder: "Search Supplier...",
            allowClear: true,
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: "{{ route('suppliers.search') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || '', 
                        page: params.page || 1
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.company_name + (item.registration_number ? ' (' + item.registration_number + ')' : ''),
                                id: item.id
                            }
                        })
                    };
                },
                cache: true,
                error: function(xhr, status, error) {
                    console.error("Supplier Search Error:", xhr.responseText);
                }
            },
            language: {
                searching: function() {
                    return "Searching...";
                },
                noResults: function() {
                    return "No suppliers found";
                }
            }
    });

    // When Supplier is selected, fetch their categories
    $supplierSelect.on('select2:select', function(e) {
        var supplierId = e.params.data.id;
        loadCategories(supplierId);
    });
    
    $supplierSelect.on('select2:clear', function() {
        $('#categories-list').hide().empty();
        $('#categories-loader').show().text('Select a Supplier first to load their categories...');
    });

    // If there's an old value (from validation error), reload it
    var oldSupplierId = "{{ old('supplier_id') }}";
    if(oldSupplierId) {
        loadCategories(oldSupplierId);
    }

    function loadCategories(supplierId) {
        var container = $('#categories-list');
        var loader = $('#categories-loader');
        
        if(!supplierId) {
            container.hide().empty();
            loader.show().text('Select a Supplier first to load their categories...');
            return;
        }

        loader.show().text('Loading categories...');
        container.hide();

        $.ajax({
            url: "{{ route('supplier-categories.all') }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                var categories = response.data || response;
                var html = '';
                
                if(categories.length > 0) {
                    html += '<div class="row">';
                    $.each(categories, function(index, cat) {
                        var checked = '';
                        var oldCats = @json(old('category_ids', []));
                        if(oldCats.map(String).includes(String(cat.id || cat.SupplierCategoryID))) {
                            checked = 'checked';
                        }

                        var catId = cat.id || cat.SupplierCategoryID;
                        var catName = cat.name || cat.CategoryName;
                        var catCode = cat.code || cat.Code || '';

                        html += '<div class="col-md-4 mb-2">';
                        html += '<div class="form-check">';
                        html += '<input class="form-check-input" type="checkbox" name="category_ids[]" value="' + catId + '" id="cat_' + catId + '" ' + checked + '>';
                        html += '<label class="form-check-label" for="cat_' + catId + '">';
                        html += catName + ' <small class="text-muted">(' + catCode + ')</small>';
                        html += '</label>';
                        html += '</div>';
                        html += '</div>';
                    });
                    html += '</div>';
                } else {
                    html = '<div class="text-warning">No categories found in the system.</div>';
                }

                loader.hide();
                container.html(html).fadeIn();
            },
            error: function(xhr) {
                console.error('Category load error:', xhr);
                loader.html('<span class="text-danger">Failed to load categories. Please try again.</span>');
            }
        });
    }
});
</script>
@endpush