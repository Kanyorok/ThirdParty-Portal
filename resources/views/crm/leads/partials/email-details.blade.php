<div class="card mb-3">
    <div class="card-body">

        {{-- To --}}
        @php
            $toList = collect($email->To)->map(function ($entry) {
                return is_array($entry)
                    ? collect($entry)->map(fn($v, $k) => "$k: $v")->implode(', ')
                    : $entry;
            })->implode(', ');
        @endphp
        <p><strong>To:</strong> {{ $toList ?: 'N/A' }}</p>

        {{-- CC --}}
        @php
            $ccList = collect($email->CC)->map(function ($entry) {
                return is_array($entry)
                    ? collect($entry)->map(fn($v, $k) => "$k: $v")->implode(', ')
                    : $entry;
            })->implode(', ');
        @endphp
        @if($ccList)
            <p><strong>CC:</strong> {{ $ccList }}</p>
        @endif

        {{-- Subject --}}
        <p><strong>Subject:</strong> {{ $email->Subject ?: 'N/A' }}</p>

        {{-- Body --}}
        <div class="mt-3">
            <strong>Content:</strong>
            <div class="border p-3 rounded mt-2" style="background: #f9f9f9;">
                {!! $email->Body !!}
            </div>
        </div>

    </div>
</div>
