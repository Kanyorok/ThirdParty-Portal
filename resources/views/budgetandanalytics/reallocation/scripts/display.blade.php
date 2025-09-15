<script>

    document.getElementById('reallocationType').addEventListener('change', function() {
        let type = this.value;
        let deptSection = document.getElementById('departmentSection');
        let toDeptSection = document.getElementById('toDepartmentSection');

        deptSection.style.display = 'none';
        toDeptSection.style.display = 'none';

        // Reset classes first
        deptSection.classList.remove('col-md-6', 'col-12');

        if (type === 'Department') {
            // Show full width
            deptSection.style.display = 'block';
            deptSection.classList.add('col-12');
        } else if (type === 'Cross-Department') {
            // Show half width for both
            deptSection.style.display = 'block';
            deptSection.classList.add('col-md-6');
            toDeptSection.style.display = 'block';
        }
    });


    // Toggle branch/department fields
    document.getElementById('reallocationType').addEventListener('change', function() {
        let type = this.value;
        document.getElementById('branchSection').style.display = 'none';
        document.getElementById('departmentSection').style.display = 'none';
        document.getElementById('toDepartmentSection').style.display = 'none';

        if (type === 'Branch') {
            document.getElementById('branchSection').style.display = 'block';
        } else if (type === 'Department') {
            document.getElementById('departmentSection').style.display = 'block';
        } else if (type === 'Cross-Department') {
            document.getElementById('departmentSection').style.display = 'block';
            document.getElementById('toDepartmentSection').style.display = 'block';
        }
    });

    // Fetch budget lines dynamically
    document.getElementById('departmentSelect')?.addEventListener('change', function() {
        let deptId = this.value;
        if (deptId) {
            fetch(`/budgetandanalytics/reallocation/budget-lines/${deptId}`)
                .then(res => res.json())
                .then(data => {
                    let fromSelect = document.getElementById('fromLine');
                    fromSelect.innerHTML = '<option value="">-- Select Line --</option>';
                    data.forEach(line => {
                        fromSelect.innerHTML += `<option value="${line.Id}">${line.LineName}</option>`;
                    });
                });
        }
    });

    document.getElementById('toDepartmentSelect')?.addEventListener('change', function() {
        let deptId = this.value;
        if (deptId) {
            fetch(`/budgetandanalytics/reallocation/budget-lines/${deptId}`)
                .then(res => res.json())
                .then(data => {
                    let toSelect = document.getElementById('toLine');
                    toSelect.innerHTML = '<option value="">-- Select Line --</option>';
                    data.forEach(line => {
                        toSelect.innerHTML += `<option value="${line.Id}">${line.LineName}</option>`;
                    });
                });
        }
    });

    // Fetch line details
    document.getElementById('fromLine')?.addEventListener('change', function() {
        let lineId = this.value;
        let branchId = document.querySelector('[name="BranchID"]')?.value || '';
        if (lineId) {
            fetch(`/budgetandanalytics/reallocation/budget-line/${lineId}/details/${branchId}`)
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    if (data.type === 'activity') {
                        html = `<h6 class="mt-3">Activity Driven Line</h6>
                    <table class="table table-bordered table-sm">
                      <thead><tr><th>Activity</th><th>Description</th><th>Allocations</th></tr></thead>
                      <tbody>`;
                        data.activities.forEach(a => {
                            let allocHtml = '<ul class="mb-0">';
                            a.monthly_allocations.forEach(m => {
                                allocHtml += `<li>Month ${m.month}: ${m.amount}</li>`;
                            });
                            allocHtml += '</ul>';
                            html += `<tr><td>${a.name}</td><td>${a.description || ''}</td><td>${allocHtml}</td></tr>`;
                        });
                        html += '</tbody></table>';
                    } else if (data.type === 'manual') {
                        html = `<h6 class="mt-3">Manual Allocations</h6>
                    <table class="table table-bordered table-sm">
                      <thead><tr><th>Month</th><th>Allocation</th></tr></thead>
                      <tbody>`;
                        data.entries.forEach(e => {
                            e.monthly_allocations.forEach(m => {
                                html += `<tr><td>${m.month}</td><td>${m.amount}</td></tr>`;
                            });
                        });
                        html += '</tbody></table>';
                    }
                    document.getElementById('lineDetailsSection').innerHTML = html;
                });
        }
    });

</script>
