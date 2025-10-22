@extends('layouts.app')
@section('title', 'RFQ Clarifications')
@section('content')
<div class="container mt-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>RFQ Clarifications</h4>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">RFQ</label>
          <select id="f-rfq" class="form-select">
            <option value="">All Approved RFQs</option>
            @foreach(($rfqs ?? []) as $r)
              <option value="{{ $r->Id }}">{{ $r->RFQNumber }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Answered</label>
          <select id="f-answered" class="form-select">
            <option value="">All</option>
            <option value="no">Unanswered</option>
            <option value="yes">Answered</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Search</label>
          <input id="f-q" class="form-control" placeholder="Question/Answer contains…" />
        </div>
        <div class="col-md-2">
          <button class="btn btn-primary w-100" id="btn-apply">Apply</button>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="clar-table">
          <thead>
            <tr>
              <th>#</th>
              <th>RFQ</th>
              <th>Supplier</th>
              <th>Item</th>
              <th>Question</th>
              <th>Answer</th>
              <th>Respond</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  async function loadClarifications(){
    const rfqId = document.getElementById('f-rfq').value;
    const answered = document.getElementById('f-answered').value;
    const q = document.getElementById('f-q').value.trim();
    const params = new URLSearchParams();
    if (rfqId) params.set('rfqId', rfqId);
    if (answered) params.set('answered', answered);
    if (q) params.set('q', q);
    const res = await fetch(`/procurement/rfq-clarifications/list?${params.toString()}`);
    const data = await res.json();
    const tbody = document.querySelector('#clar-table tbody');
    tbody.innerHTML = '';
    (data.data || []).forEach((r, idx) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${idx+1}</td>
        <td>${r.RFQNumber ?? r.RFQId}</td>
        <td>${r.SupplierName ?? r.SupplierId}</td>
        <td>${r.ItemName ?? ''}</td>
        <td>${r.Question ?? ''}</td>
        <td>${r.Answer ?? ''}</td>
        <td>
          <div class="input-group input-group-sm">
            <input type="text" class="form-control" value="${r.Answer ?? ''}" data-clar-id="${r.Id}" placeholder="Type answer…">
            <button class="btn btn-primary">Send</button>
          </div>
        </td>`;
      const btn = tr.querySelector('button');
      btn.addEventListener('click', () => postAnswer(tr));
      tbody.appendChild(tr);
    });
  }

  async function postAnswer(tr){
    const input = tr.querySelector('input[data-clar-id]');
    const id = input.getAttribute('data-clar-id');
    const answer = input.value.trim();
    if (!answer) { input.focus(); return; }
    const btn = tr.querySelector('button');
    btn.disabled = true; btn.textContent = 'Saving…';
    try{
      const res = await fetch(`{{ route('rfqclarifications.respond') }}`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
        body: JSON.stringify({ id, answer })
      });
      if(!res.ok) throw new Error();
      btn.textContent = 'Saved';
      setTimeout(()=>{ btn.textContent = 'Send'; btn.disabled = false; }, 800);
    }catch(e){
      btn.textContent = 'Error';
      setTimeout(()=>{ btn.textContent = 'Send'; btn.disabled = false; }, 800);
    }
  }

  document.getElementById('btn-apply').addEventListener('click', loadClarifications);
  // initial
  loadClarifications();
})();
</script>
@endsection