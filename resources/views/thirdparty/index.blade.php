@extends('layouts.app')

@section('title','Third Parties')

@section('styles')
    <style>
        .mouse_pointer {
            cursor: pointer;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <form class="card-body row  px-2" id="searchForm">
                    <div class="col-12 col-sm-6 col-md-3 mb-2">
                        <input type="search" class="form-control w-100 search-form-item"
                               name="id_number" autocomplete="off" maxlength="50" id="id_number"
                               placeholder="id number or Reg No">
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 mb-2">
                        <input type="search" class="form-control w-100 search-form-item"
                               name="name" autocomplete="off" maxlength="50" id="name" placeholder="name">
                    </div>
                    <div class="col-12 col-sm-6 col-md-2 mb-2">
                        <select id="filterBusinessTypes" class="form-select">
                            <option value="all">All Business Types</option>
                            @foreach ($businessTypes as $type)
                                <option
                                    value="{{ $type->Value }}">{{ \Illuminate\Support\Str::of($type->Description)->plural() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-2 mb-2">
                        <select id="filterType" class="form-select">
                            <option value="all">All Types</option>
                            @foreach ($types as $type)
                                <option
                                    value="{{ $type->Code }}">{{ \Illuminate\Support\Str::of($type->Description)->plural() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-1 col-sm-6 mb-2">
                        <button class="btn btn-primary w-100" id="searchFormBtn" type="submit"><i
                                class="fas fa-magnifying-glass"></i>
                        </button>
                    </div>
                    <div class="col-12 col-md-1 col-sm-6 mb-2">
                        <a class="btn btn-primary w-100" href="{{ route('thirdparty.parties.create') }}">
                            <i class="fas fa-plus-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="thirdPartiesTable" class="table table-striped dataTable no-footer dtr-inline w-100">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>ID / Reg No.</th>
                            <th>Type</th>
                            <th>Business Type</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const searchBtn = $('#searchFormBtn'), searchQuery = $('.search-form-item');
        let thirdPartiesTable = null;
        
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';

            $('form#searchForm').submit(function (e) {
                e.preventDefault();
                fetchThirdPartyTable();
                searchBtn.addClass('disabled');
                searchQuery.addClass('disabled');
            });

            fetchThirdPartyTable();
        });

        function getDocumentUrl() {
            return '{{ route("thirdparty.parties.index") }}';
        }

        function fetchThirdPartyTable() {
            searchBtn.html('<i class="fas fa-spinner fa-spin"></i>');
            
            if (thirdPartiesTable === null) {
                thirdPartiesTable = $('#thirdPartiesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    dom: '<"row"<"col-12"r><"col-12 w-100 my-3"t><"col-6"i><"col-6"p>>',
                    ajax: {
                        url: getDocumentUrl() + "?_type=" + $('#filterType').val() + "&id_number=" + $('#id_number').val() + "&_business=" + $('#filterBusinessTypes').val() + "&name=" + $('#name').val(),
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    initComplete: function () {
                        searchBtn.removeClass('disabled').html('<i class="fas fa-magnifying-glass"></i>');
                        searchQuery.removeClass('disabled');
                    },
                    columns: [
                        {
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            width: '5%'
                        },
                        {
                            data: 'ThirdPartyName',
                            name: 'ThirdPartyName'
                        },
                        {
                            data: 'RegistrationNumber',
                            name: 'RegistrationNumber'
                        },
                        {
                            data: 'types',
                            name: 'types',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'business_type.Description',
                            name: 'business_type.Description'
                        },
                        {
                            data: 'country.Name',
                            name: 'country.Name',
                            render: function(data, type, row) {
                                if (row.country && row.country.Flag) {
                                    return row.country.Flag + ' ' + data;
                                }
                                return data;
                            }
                        },
                        {
                            data: 'status.Description',
                            name: 'status.Description',
                            render: function(data) {
                                return '<span class="badge bg-success">' + data + '</span>';
                            }
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false,
                            width: '15%'
                        }
                    ],
                    "oLanguage": {
                        "sEmptyTable": "<div class='text-center'><img class='img-fluid' style='height:30vh' src='{{ asset('assets/img/errors/404.svg') }}' alt='?'></div>"
                    },
                    rowCallback: function(row, data, index) {
                        // Add double-click functionality
                        $(row).on('dblclick', function() {
                            if (data.dbl_click_url) {
                                window.location.href = data.dbl_click_url;
                            }
                        });
                    }
                });

                $("#thirdPartiesTable_filter").addClass('d-none');
                
                thirdPartiesTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the list.");
                    console.log(er);
                });

                // Handle delete button clicks
                $('#thirdPartiesTable tbody').on('click', '.delete-btn', function() {
                    const id = $(this).data('id');
                    const name = $(this).data('name');
                    
                    if (confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone.')) {
                        $.ajax({
                            url: '{{ route("thirdparty.parties.index") }}/' + id,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            success: function(response) {
                                if (response.success) {
                                    nSuccess(response.message);
                                    thirdPartiesTable.ajax.reload();
                                } else {
                                    nError(response.message || 'Failed to delete third party.');
                                }
                            },
                            error: function(xhr) {
                                nError(xhr.responseJSON?.message || 'Failed to delete third party.');
                            }
                        });
                    }
                });
            } else {
                thirdPartiesTable.ajax.reload();
            }
        }
    </script>
@endsection