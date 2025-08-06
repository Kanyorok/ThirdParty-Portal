@extends('layouts.app')
@section('title', 'Add GL Mapping')
 
@section('content')
<div class="card shadow p-4 rounded-4">
  <h4 class="mb-4">➕ Add GL Mapping</h4>
 
  @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
    @endif
    
  <form method="POST" action="{{ route('glpostingmap.store') }}">
    @csrf
 
    <div class="row mb-3">
      <!-- Module -->
      <div class="col-md-6">
        <label for="ModuleID" class="form-label">Module</label>
        <select name="ModuleID" id="ModuleID" class="form-select" required>
          <option value="">-- Select Module --</option>
          @foreach($modules as $module)
            <option value="{{ $module->ModuleID }}">{{ $module->Name }}</option>
          @endforeach
        </select>
      </div>
 
      <!-- Transaction Type -->
      <div class="col-md-6">
        <label for="TransactionType" class="form-label">Transaction Type</label>
        <select name="TransactionType" id="TransactionType" class="form-select" required disabled>
          <option selected disabled value="">-- Select Transaction Type --</option>
        </select>
      </div>
    </div>
 
    <div class="row mb-3">
      <!-- Debit GL -->
      <div class="col-md-6">
        <label for="DebitGLAccountID" class="form-label">Debit GL Account</label>
        <select class="form-select" name="DebitGLAccountID" id="DebitGLAccountID" required>
          <option selected disabled value="">--Select Debit Account--</option>
        </select>
      </div>
 
      <!-- Credit GL -->
      <div class="col-md-6">
        <label for="CreditGLAccountID" class="form-label">Credit GL Account</label>
        <select class="form-select" name="CreditGLAccountID" id="CreditGLAccountID" required>
          <option selected disabled value="">--Select Credit Account--</option>
        </select>
      </div>
    </div>
 
    <!-- Additional mapping fields like SubType, CostCenter, etc. here -->
 
    <div class="mt-3 text-end">
      <a href="{{ route('glpostingmap.index') }}" class="btn btn-secondary">Back</a>
      <button type="submit" class="btn btn-primary"onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='💾Saving...'; this.form.submit();}">💾Save Mapping</button>
    </div>
  </form>
</div>
@endsection
 
@section('scripts')
<script>
  const Transaction = document.getElementById('TransactionType');
  const modules = document.getElementById('ModuleID');

  modules.addEventListener('change', function(){
    const selectedModule = modules.options[modules.selectedIndex].value;
    Transaction.disabled = !selectedModule

    Transaction.innerHTML = '<option selected disabled value="">Loading....</option>';

    if(selectedModule){
        fetch(`/finance/finance/transactions/${selectedModule}`)
        .then(response => response.json())
        .then(data => {
            console.log(data);

            Transaction.innerHTML = '<option selected disabled value="">--Select Transaction Type--</option>';
            data.forEach(function(mod){
                console.log("Data1",mod.Id);
                
                Transaction.innerHTML += `<option value="${mod.Id}">${mod.transactions.Name}</option>`;
            });
            Transaction.disabled = false;
        });
    }
  });

  const debitGL = document.getElementById('DebitGLAccountID');
  const creditGL = document.getElementById('CreditGLAccountID');
  let glData = [];

        //fetching GLAccounts
        async function loadAccounts() {
          try{
            const resp = await fetch("{{ route('glpostingmap.list') }}", {headers:{'Accept': 'application/json'}});
            if(!resp.ok)throw new Error('HTTP error ' + resp.status);
            glData = await resp.json();
            populateAccounts();
          } catch (err) {
                console.error("Failed to load GL accounts:", err);
            }
        }

  function populateAccounts(){

        debitGL.innerHTML = '<option selected disabled value="">--Select Debit Account--</option>'
        creditGL.innerHTML = '<option selected disabled value="">--Select Credit Account--</option>'

        glData.forEach(item => {
        debitGL.innerHTML += `<option value="${item.Id}">${item.GLName}</option>`;
        creditGL.innerHTML += `<option value="${item.Id}">${item.GLName}</option>`;
    });
  }

  //handling duplications
  function updateOptions(source, target){
      const selectedDebitGL = source.value;
      [...target.options].forEach(option =>{
        option.disabled = option.value && option.value === selectedDebitGL;
      });
  }

    debitGL.addEventListener('change', ()=>{
      updateOptions(debitGL, creditGL)
    });

    creditGL.addEventListener('change', ()=>{
      updateOptions(creditGL, debitGL)
    });

    loadAccounts();
</script>
@endsection