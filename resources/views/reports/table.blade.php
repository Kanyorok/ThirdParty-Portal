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

    @if($data['error'] || empty($data['data']))
        @include('snippets.errors')
    @else
            @php
                $hierarchyDepth = $data['hierarchyDepth'] ?? 0;
                $groupLevels = $data['groupLevels'] ?? [];
                $columns = $data['columns'] ?? [];
                $rows = $data['data'] ?? [];

                // Helper function to check if group changed at a specific level
                $groupChanged = function($rows, $rowIndex, $groupIndex) {
                    if ($rowIndex === 0) return true;

                    $currentGroups = $rows[$rowIndex]['_groups'] ?? [];
                    $prevGroups = $rows[$rowIndex - 1]['_groups'] ?? [];

                    // Check if any parent group changed
                    for ($i = 0; $i < $groupIndex; $i++) {
                        $currentVal = $currentGroups[$i]['value'] ?? '';
                        $prevVal = $prevGroups[$i]['value'] ?? '';
                        if ($currentVal !== $prevVal) {
                            return true;
                        }
                    }

                    // Check if this group changed
                    $currentVal = $currentGroups[$groupIndex]['value'] ?? '';
                    $prevVal = $prevGroups[$groupIndex]['value'] ?? '';
                    return $currentVal !== $prevVal;
                };

                // Pre-calculate rowspans for each group at each row
                $rowspans = [];
                foreach ($rows as $rowIndex => $row) {
                    $groups = $row['_groups'] ?? [];
                    $rowspans[$rowIndex] = [];

                    foreach ($groups as $groupIndex => $group) {
                        $groupValue = $group['value'] ?? '';
                        $rowspans[$rowIndex][$groupIndex] = 0;

                        // Count how many consecutive rows have the same group value at this level
                        // (and all parent levels must also match)
                        if ($groupChanged($rows, $rowIndex, $groupIndex)) {
                            $count = 1;
                            for ($nextRow = $rowIndex + 1; $nextRow < count($rows); $nextRow++) {
                                $nextGroups = $rows[$nextRow]['_groups'] ?? [];
                                $nextGroupValue = $nextGroups[$groupIndex]['value'] ?? '';

                                // Check if all parent groups also match
                                $parentMatch = true;
                                for ($parentIndex = 0; $parentIndex < $groupIndex; $parentIndex++) {
                                    $currentParentValue = $groups[$parentIndex]['value'] ?? '';
                                    $nextParentValue = $nextGroups[$parentIndex]['value'] ?? '';
                                    if ($currentParentValue !== $nextParentValue) {
                                        $parentMatch = false;
                                        break;
                                    }
                                }

                                if ($parentMatch && $groupValue === $nextGroupValue) {
                                    $count++;
                                } else {
                                    break;
                                }
                            }
                            $rowspans[$rowIndex][$groupIndex] = $count;
                        }
                    }
                }
            @endphp

        <div class="table-responsive">
            <table class="table table-bordered w-100" id="reports-table">
                <thead>
                <tr>
                    @foreach($groupLevels as $level)
                        <th class="bg-light">{{ ucfirst(str_replace(['_', '1', '2', '3'], [' ', '', '', ''], $level['attribute'])) }}</th>
                    @endforeach
                    @foreach($columns as $column)
                        <th>{{ ucfirst(str_replace('_', ' ', $column)) }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @php $displayedGroups = []; @endphp
                @forelse($rows as $rowIndex => $row)
                    @php
                        $groups = $row['_groups'] ?? [];
                    @endphp
                    <tr>
                        {{-- Render group columns with rowspan --}}
                        @foreach($groups as $groupIndex => $group)
                            @php
                                $groupKey = '';
                                for ($k = 0; $k <= $groupIndex; $k++) {
                                    $groupKey .= ($groups[$k]['value'] ?? '') . '|';
                                }
                            @endphp
                            @if(!isset($displayedGroups[$groupIndex][$groupKey]))
                                @php $displayedGroups[$groupIndex][$groupKey] = true; @endphp
                                <td class="align-middle fw-bold bg-light"
                                    @if($rowspans[$rowIndex][$groupIndex] > 1) rowspan="{{ $rowspans[$rowIndex][$groupIndex] }}" @endif>
                                    {{ $group['value'] }}
                                </td>
                            @endif
                        @endforeach

                        {{-- Render data columns --}}
                        @foreach($columns as $column)
                            <td>{{ $row[$column] ?? '' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($groupLevels) + count($columns) }}" class="text-center">No data
                            available
                        </td>
                    </tr>
                @endforelse
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
