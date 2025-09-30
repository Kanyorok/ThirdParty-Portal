@extends('layouts.app')
@section('title','Active User Sessions')

@section('content')
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Users with Multiple Active Sessions</h5>
      <div class="text-muted small">Session table: {{ $sessionTable }}</div>
    </div>
    <div class="card-body p-0">
      @if (empty($groups))
        <div class="p-4">No users with multiple sessions found.</div>
      @else
        @foreach ($groups as $userId => $group)
          <div class="border-bottom p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="fw-semibold">{{ $group['user']->UserID }} — {{ $group['user']->Name }}</div>
                <div class="text-muted small">{{ $group['user']->Email }}</div>
              </div>
              <form method="POST" action="{{ route('settings.user-sessions.revoke-others', ['userId' => $userId]) }}">
                @csrf
                <input type="hidden" name="keep_id" value="{{ $group['user']->current_session_id }}">
                <button class="btn btn-sm btn-warning" type="submit" data-confirm="Revoke all other sessions?">Revoke Others</button>
              </form>
            </div>

            <div class="table-responsive mt-3">
              <table class="table table-sm align-middle mb-0">
                <thead>
                  <tr>
                    <th class="text-nowrap">Session ID</th>
                    <th class="text-nowrap">IP</th>
                    <th class="text-nowrap">Agent</th>
                    <th class="text-nowrap">Last Activity</th>
                    <th class="text-end text-nowrap">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($group['sessions'] as $s)
                    <tr @class(['table-success' => $group['user']->current_session_id === $s->id])>
                      <td class="small" style="max-width: 460px; word-break: break-all;">{{ $s->id }}</td>
                      <td class="small">{{ $s->ip_address }}</td>
                      <td class="small" style="max-width: 460px; word-break: break-all;">{{ Str::limit($s->user_agent, 120) }}</td>
                      <td class="small">{{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans() }}</td>
                      <td class="text-end">
                        <form method="POST" action="{{ route('settings.user-sessions.revoke', ['id' => $s->id]) }}" class="d-inline">
                          @csrf
                          <button class="btn btn-sm btn-outline-danger" type="submit">Revoke</button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @endforeach
      @endif
    </div>
  </div>

  @push('scripts')
    <script>
      document.addEventListener('click', function(e){
        if(e.target && e.target.matches('[data-confirm]')){
          if(!confirm(e.target.getAttribute('data-confirm'))){ e.preventDefault(); }
        }
      });
    </script>
  @endpush
@endsection


