@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
    <div class="container mt-4">
        <h4 class="fw-bold mb-3">📆 Lease Schedule Generator</h4>

        <form action="{{ route('schedulelease.store') }}" method="POST">
            @csrf
            <div class="card shadow">
                <div class="card-header bg-light fw-bold">🧮 Generate Billing Periods</div>
                <div class="card-body">
                    <!-- Lease Selection -->
                    <div class="row g-3 mb-3">
                      <div class="col-md-6">
                            <label class="form-label">Select Tenant</label>
                        <select name="TenantId" id="tenant-select" class="form-select" required>
                            <option value="">--Select the tenant--</option>
                            @foreach ($newleases as $newlease)
                                <option value="{{ $newlease->TenantId }}">
                                    {{ $newlease->tenant->TenantName ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                     </div>
                         <div class="col-md-6">
                            <label class="form-label">Select property Leased </label>
                            <select name="PropertyId" id="property-select" class="form-select" required>
                                         <option value="">-- Select Property --</option>
                            </select>
                        </div>
                      <div class="col-md-6">
                            <label class="form-label">Select Lease Number</label>
                            <select name="LeaseNumber" id="lease-select" class="form-select" required>
                                    <option value="">-- Select Lease --</option>
                            </select>
                        </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Frequency</label>
                        <select class="form-select" name="PaymentFrequency" required>
                        <option value="">-- Select Frequency --</option>
                        @foreach ($codes as $code)
                            <option value="{{ $code->ID }}">{{ $code->Description }}</option>
                        @endforeach
                        </select>
                    </div>

                    <!-- Financial Parameters -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" value="2025-05-01" name="StartDate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" value="2026-04-30" name="EndDate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Base Rent per Period (KES)</label>
                            <input type="float" class="form-control" value="25000.00" name="BaseRent">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Service Charge (KES)</label>
                            <input type="float" class="form-control" value="15000.00" name="ServiceCharge">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Parking Fee (KES)</label>
                            <input type="float" class="form-control" value="2000.00" name="ParkingFee">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Other Charges (KES)</label>
                            <input type="float" class="form-control" value="0.00" name="OtherCharges">
                        </div>
                    </div>
                    <button class="btn btn-success">🧾 Generate Schedule</button>
        </form>
                </div>
            </div>
@endsection

<script>
  // Define routes with placeholders
  const routes = {
    getProperties: "{{ route('getpropertybytenant', ['Id' => '__ID__']) }}",
    getLeases: "{{ route('getleasebyproperty', ['Id' => '__ID__']) }}"
  };

  document.addEventListener('DOMContentLoaded', function () {
    const tenantSelect = document.getElementById('tenant-select');
    const propertySelect = document.getElementById('property-select');
    const leaseSelect = document.getElementById('lease-select');

    // Tenant → Property
    tenantSelect.addEventListener('change', function () {
      const tenantId = this.value;
      propertySelect.innerHTML = '<option value="">-- Select a Property --</option>';
      leaseSelect.innerHTML = '<option value="">-- Select a Lease --</option>';

      if (tenantId) {
        fetch(routes.getProperties.replace('__ID__', tenantId))
          .then(res => res.json())
          .then(data => {
            data.forEach(property => {
              const option = document.createElement('option');
              option.value = property.Id;
              option.textContent = property.PropertyName;
              propertySelect.appendChild(option);
            });
          })
          .catch(err => {
            console.error('Error loading properties:', err);
            alert('Failed to load properties.');
          });
      }
    });

    // Property → Lease
    propertySelect.addEventListener('change', function () {
      const propertyId = this.value;
      leaseSelect.innerHTML = '<option value="">-- Select Lease --</option>';

      if (propertyId) {
        fetch(routes.getLeases.replace('__ID__', propertyId))
          .then(res => res.json())
          .then(data => {
            data.forEach(lease => {
              const option = document.createElement('option');
              option.value = lease.Id;
              option.textContent = lease.LeaseNumber;
              leaseSelect.appendChild(option);
            });
          })
          .catch(err => {
            console.error('Error loading leases:', err);
            alert('Failed to load leases.');
          });
      }
    });
  });
</script>