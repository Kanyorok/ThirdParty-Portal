<html>
<head>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { padding: 4px 6px; border: 1px solid #ddd; word-wrap: break-word; }
        thead th { background: #f5f5f5; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
    </style>
    <title>Budget Consolidation Export</title>
</head>
<body>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
    <div style="display:flex; align-items:center; gap:10px;">
        <img src="{{ public_path('assets/img/CBT-Logo.jpg') }}" alt="Logo" style="height:42px;">
        <div>
            <div style="font-weight:700; font-size:16px;">Budget Consolidation</div>
            @php $bn = request()->input('budgetName',''); @endphp
            <div style="color:#666; font-size:12px;">{{ $bn }}</div>
        </div>
    </div>
    <div style="text-align:right; font-size:11px; color:#666;">
        <div>Printed: {{ now()->format('d M Y, H:i') }}</div>
        <div>By: {{ optional(auth()->user())->Name ?? optional(auth()->user())->UserID ?? '—' }}</div>
    </div>
    </div>
<table>
    <thead>
    <tr>
        @foreach(($rows[0] ?? []) as $h)
            <th>{{ $h }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach(array_slice($rows,1) as $r)
        <tr>
            @foreach($r as $c)
                <td>{{ $c }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>

