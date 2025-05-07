@extends('layouts.app')
@section('title', 'Compliance And Documentation')
@section('content')

<div class="container-fluid px-4 py-4">
  <h1 class="h2 mb-4">Compliance Dashboard</h1>

  <!-- Add a separate link for "View Policy Documentation" with a customizable route -->
  <div class="mb-4">
    <!-- Modify the href with your desired route (for example, '/documentation' or 'your-custom-route') -->
    <a href="{{route('complianceanddocumentation.create')}} " id="viewDocLink" class="btn btn-primary">View Policy Documentation</a>
  </div>

  <!-- Add Documentation Form -->
  <div class="mb-4">
    <h5>Add New Policy Documentation</h5>
    <form id="documentationForm">
      <div class="row mb-3">
        <div class="col-md-3">
          <input type="text" id="docTitle" class="form-control" placeholder="Title (e.g., ISO 27001)" required>
        </div>
        <div class="col-md-3">
          <textarea id="docContent" class="form-control" placeholder="Document content" rows="4" required></textarea>
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-success">Add Documentation</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Documentation Section (initially hidden) -->
  <div class="card mb-4" id="docSection">
    <div class="card-header">
      <h5>Policy Documentation</h5>
    </div>
    <div class="card-body" id="docContentSection">
      <!-- Dynamically added content will be inserted here -->
    </div>
  </div>

  <!-- Compliance Form -->
  <div class="card mb-4">
    <div class="card-header">
      <h5>Add New Compliance Item</h5>
    </div>
    <div class="card-body">
      <form id="complianceForm">
        <div class="row mb-3">
          <div class="col-md-3">
            <input type="text" name="standard" class="form-control" placeholder="Standard (e.g., ISO 27001)" required>
          </div>
          <div class="col-md-2">
            <select name="status" class="form-control" required>
              <option value="">Select Status</option>
              <option value="Compliant">Compliant</option>
              <option value="Partial">Partial</option>
              <option value="Non-Compliant">Non-Compliant</option>
            </select>
          </div>
          <div class="col-md-2">
            <input type="date" name="last_audit" class="form-control" required>
          </div>
          <div class="col-md-2">
            <input type="date" name="next_due" class="form-control" required>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-success">Add Compliance</button>
          </div>
        </div>
      </form>
      <div id="messageBox" class="mt-2"></div>
    </div>
  </div>

  <!-- Compliance Table -->
  <div class="card">
    <div class="card-header">
      <h5>Compliance Overview</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="complianceTable">
          <thead class="table-light">
            <tr>
              <th>Standard</th>
              <th>Status</th>
              <th>Last Audit</th>
              <th>Next Due</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal for reviewing compliance details -->
  <div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="reviewModalLabel">Compliance Item Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modalContent">
          <!-- Detailed content will be displayed here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Script placed in div -->
  <div style="display:none;">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
      const complianceItems = [
        {
          standard: 'ISO 27001',
          status: 'Compliant',
          last_audit: '2024-11-20',
          next_due: '2025-11-20',
          details: 'The organization has been audited for ISO 27001 compliance and passed all requirements.'
        },
        {
          standard: 'GDPR',
          status: 'Partial',
          last_audit: '2024-08-15',
          next_due: '2025-08-15',
          details: 'Partial compliance due to pending data processing agreement updates.'
        },
        {
          standard: 'HIPAA',
          status: 'Non-Compliant',
          last_audit: '2024-05-01',
          next_due: '2024-11-01',
          details: 'Non-compliant, needs updates to data storage policies.'
        }
      ];

      const form = document.getElementById('complianceForm');
      const tableBody = document.querySelector('#complianceTable tbody');
      const messageBox = document.getElementById('messageBox');
      const docToggleBtn = document.getElementById('viewDocLink');
      const docSection = document.getElementById('docSection');
      const docContentSection = document.getElementById('docContentSection');
      const docForm = document.getElementById('documentationForm');
      const docTitleInput = document.getElementById('docTitle');
      const docContentInput = document.getElementById('docContent');

      function renderTable() {
        tableBody.innerHTML = '';
        complianceItems.forEach((item, index) => {
          const badge = item.status === 'Compliant' ? 'badge bg-success' :
                        item.status === 'Partial' ? 'badge bg-warning text-dark' :
                        'badge bg-danger';

          const row = `
            <tr>
              <td>${item.standard}</td>
              <td><span class="${badge}">${item.status}</span></td>
              <td>${item.last_audit}</td>
              <td>${item.next_due}</td>
              <td><button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#reviewModal" data-index="${index}">Review</button></td>
            </tr>
          `;
          tableBody.insertAdjacentHTML('beforeend', row);
        });
      }

      function renderDocumentation() {
        docContentSection.innerHTML = '';
        if (documentationItems.length > 0) {
          documentationItems.forEach(doc => {
            const docHTML = `
              <div class="doc-item">
                <h6>${doc.title}</h6>
                <p>${doc.content}</p>
              </div>
            `;
            docContentSection.insertAdjacentHTML('beforeend', docHTML);
          });
        } else {
          docContentSection.innerHTML = '<p>No documentation added yet.</p>';
        }
      }

      // Add Documentation
      let documentationItems = [];

      docForm.addEventListener('submit', e => {
        e.preventDefault();
        const title = docTitleInput.value.trim();
        const content = docContentInput.value.trim();

        if (title && content) {
          documentationItems.push({ title, content });
          renderDocumentation();
          docForm.reset();
        }
      });

      // Show Documentation section when "View Policy Documentation" is clicked
      docToggleBtn.addEventListener('click', function (e) {
        e.preventDefault();
        docSection.style.display = docSection.style.display === 'none' ? 'block' : 'none';
        this.textContent = docSection.style.display === 'block'
          ? 'Hide Policy Documentation'
          : 'View Policy Documentation';
      });

      // Handle Compliance Form submission
      form.addEventListener('submit', e => {
        e.preventDefault();
        const data = new FormData(form);
        complianceItems.push({
          standard: data.get('standard'),
          status: data.get('status'),
          last_audit: data.get('last_audit'),
          next_due: data.get('next_due')
        });
        renderTable();
        form.reset();
        messageBox.innerHTML = '<div class="alert alert-success">Compliance item added successfully.</div>';
      });

      // Show detailed view in modal on Review button click
      $('#reviewModal').on('show.bs.modal', function (e) {
        const button = $(e.relatedTarget); // Button that triggered the modal
        const index = button.data('index'); // Extract index from data-* attributes
        const item = complianceItems[index]; // Get the selected compliance item

        // Update modal content
        const modalContent = `
          <h5>Standard: ${item.standard}</h5>
          <p><strong>Status:</strong> ${item.status}</p>
          <p><strong>Last Audit:</strong> ${item.last_audit}</p>
          <p><strong>Next Due:</strong> ${item.next_due}</p>
          <p><strong>Details:</strong> ${item.details}</p>
        `;
        $('#modalContent').html(modalContent);
      });

      renderTable();
    </script>
  </div>
</div>

@endsection