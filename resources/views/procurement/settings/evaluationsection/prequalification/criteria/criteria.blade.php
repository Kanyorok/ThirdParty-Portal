@extends('layouts.app')
@section('title', 'Setup Evaluation Criteria')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')

    <div class="card">
        <div class="card-header bg-info text-white">
            Setup Criteria for <strong>{{ $section->SectionName }}</strong> — Round:
            <strong>{{ $round->Title }}</strong>
        </div>

        <div class="card-body">
            <form method="POST" action="#">
                <table id="criteria" class="table table-bordered">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Criterion</th>
                        <th>Description</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($criterias as $criteria)
                        <tr>
                            <td>{{$loop->iteration }}</td>
                            <td>{{ $criteria->CriteriaName }}</td>
                            <td>{{ $criteria->Description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No criteria found for this section.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('preqcriteria.index') }}" class="btn btn-secondary">← Back to Sections</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#criteria').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
