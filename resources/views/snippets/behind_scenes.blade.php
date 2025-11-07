@php use App\Models\Auth\User; @endphp

<p class="mb-0 h5">Other Details</p>
<hr class="mt-0">
<div class="mb-2">
    <table class="table-responsive w-100 border-0">
        <tr class="border-bottom">
            <th>Creation</th>
            <td class="float-end">
                {{ $model->CreatedOn?->format('d M, Y H:i') }} <br>
                @if($model->creator instanceof User)
                    <details>
                        <summary>{{ $model->creator->UserID }}</summary>
                        <p>{{ $model->creator->Name }}</p>
                    </details>
                @else
                    {{ $model->CreatedBy }}
                @endif
            </td>
        </tr>

        @if($model->CreatedOn && $model->ModifiedOn && !$model->CreatedOn->eq($model->ModifiedOn))
        <tr class="border-bottom">
            <th>Modified</th>
            <td class="float-end">
                {{ $model->ModifiedOn->format('d M, Y H:i') }} <br>
                @if($model->modified instanceof User)
                    <details>
                        <summary>{{ $model->modified->UserID }}</summary>
                        <p>{{ $model->modified->Name }}</p>
                    </details>
                @else
                    {{ $model->CreatedBy }}
                @endif
            </td>
        </tr>
        @endif
    </table>
</div>
