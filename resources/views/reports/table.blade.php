<div class="card-header">
    <div class="float-end">
        <a href="{{ route($module.'-reports.export',[$report->Id,'IMAGE'])."?".$params}}" target="_blank"
           class="btn btn-secondary">
            <i class="fas fa-file-image"></i> Image
        </a>
        <a href="{{ route($module.'-reports.export',[$report->Id,'PDF'])."?".$params }}" target="_blank"
           class="btn btn-secondary">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="{{ route($module.'-reports.export',[$report->Id,'EXCELOPENXML'])."?".$params}}"
           target="_blank" class="btn btn-secondary">
            <i class="fas fa-file-excel"></i> Excel
        </a>
        <a href="{{ route($module.'-reports.index') }}" class="btn btn-info">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>
</div>
<div class="card-body">
    @if($data['error'] || empty($data['data']))
        @include('snippets.errors')
    @else
        @if(!empty($data['header']))
            <div class="mb-3 text-center">
                @foreach($data['header'] as $key=>$value)
                    @if($loop->first ||  (array_key_exists('name',array_change_key_case($data['header'] , CASE_LOWER)) && \Illuminate\Support\Str::of($key)->lower()->contains('name')))
                        <h2>{{ $value }}</h2>
                    @else
                        <span class="h5">{{ $value }}&nbsp;</span>  &nbsp;
                    @endif
                @endforeach
            </div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered w-100" id="reports-table">
                <thead>
                <tr>
                    @if($data['isGrouped'] && $data['groupKeyAttribute'])
                        <th>{{ ucfirst($data['groupKeyAttribute']) }}</th>
                    @endif
                    @php
                        // Get columns based on grouped or ungrouped data
                        if ($data['isGrouped']) {
                            $firstGroup = collect($data['data'])->first();
                            $firstRow = is_array($firstGroup) ? collect($firstGroup)->first() : [];
                        } else {
                            $firstRow = collect($data['data'])->first() ?? [];
                        }
                        $columns = is_array($firstRow) ? array_keys($firstRow) : [];
                    @endphp
                    @foreach($columns as $column)
                        <th>{{ ucfirst($column) }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @if($data['isGrouped'])
                    {{-- Grouped Report with Rowspan --}}
                    @forelse($data['data'] as $groupName => $rows)
                        @foreach($rows as $index => $row)
                            <tr>
                                @if($index === 0)
                                    <td rowspan="{{ count($rows) }}" class="align-middle fw-bold">
                                        {{ $groupName }}
                                    </td>
                                @endif
                                @foreach($row as $value)
                                    <td>{{ $value }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="100%" class="text-center">No data available</td>
                        </tr>
                    @endforelse
                @else
                    {{-- Ungrouped Report - Simple Table --}}
                    @forelse($data['data'] as $row)
                        <tr>
                            @foreach($row as $value)
                                <td>{{ $value }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="text-center">No data available</td>
                        </tr>
                    @endforelse
                @endif
                </tbody>
            </table>
        </div>
    @endif
</div>
<script>
    $(document).ready(function () {
        $.fn.dataTable.ext.errMode = 'none';
        $('#reports-table').DataTable({
            dom: '<"row"<"col-12 mb-2"tr><"col-5 text-center"i><"col-7"p>>',
            paging: false,
            ordering: false
        });
    });
</script>
