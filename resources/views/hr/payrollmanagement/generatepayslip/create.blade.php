@extends('layouts.app')
@section('title', 'Generate Pay Slip')
@section('content')

<div class="container mt-5">
    <h2 class="text-center mb-4">Generate Payslip</h2>

    <!-- Select Employee -->
    <div class="mb-4">
        <label for="employee" class="form-label fw-bold">Select Employee</label>
        <select class="form-select" id="employee" onchange="updatePayslip()">
            <option value="jane">Jane Doe (EMP12345)</option>
            <option value="john">John Mwangi (EMP67890)</option>
            <option value="alice">Alice Kimani (EMP54321)</option>
        </select>
    </div>

    <!-- Earnings & Deductions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h5 class="bg-light p-2">Earnings</h5>
            <ul class="list-group" id="earningsList">
                <!-- JS inserts here -->
            </ul>
        </div>

        <div class="col-md-6">
            <h5 class="bg-light p-2">Deductions</h5>
            <ul class="list-group" id="deductionsList">
                <!-- JS inserts here -->
            </ul>
        </div>
    </div>

    <!-- Payslip Preview -->
    <div class="mt-5 mb-4">
        <h5 class="bg-light p-2">Payslip Preview</h5>
        <table class="table table-bordered text-center">
            <thead class="table-secondary">
                <tr>
                    <th>Employee</th>
                    <th>Total Earnings</th>
                    <th>Total Deductions</th>
                    <th>Net Pay</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="previewName">Jane Doe</td>
                    <td id="previewEarnings">$4,750</td>
                    <td id="previewDeductions">$250</td>
                    <td id="previewNetPay">$4,500</td>
                    <td>Generated</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    const employees = {
        jane: {
            name: "Jane Doe",
            earnings: {
                "Base Pay": 4500,
                "Bonus": 250
            },
            deductions: {
                "Health Insurance": 100,
                "Pension": 50,
                "Income Tax": 100
            }
        },
        john: {
            name: "John Mwangi",
            earnings: {
                "Base Pay": 5000,
                "Bonus": 400
            },
            deductions: {
                "Health Insurance": 120,
                "Pension": 80,
                "Income Tax": 180
            }
        },
        alice: {
            name: "Alice Kimani",
            earnings: {
                "Base Pay": 4800,
                "Bonus": 300
            },
            deductions: {
                "Health Insurance": 90,
                "Pension": 60,
                "Income Tax": 150
            }
        }
    };

    function formatCurrency(amount) {
        return "$" + amount.toLocaleString();
    }

    function updatePayslip() {
        const key = document.getElementById("employee").value;
        const emp = employees[key];

        let earningsHtml = "", totalEarnings = 0;
        for (let [label, amount] of Object.entries(emp.earnings)) {
            earningsHtml += `<li class="list-group-item d-flex justify-content-between">
                                <span>${label}</span><span>${formatCurrency(amount)}</span>
                             </li>`;
            totalEarnings += amount;
        }
        earningsHtml += `<li class="list-group-item d-flex justify-content-between fw-bold">
                            <span>Total Earnings</span><span>${formatCurrency(totalEarnings)}</span>
                         </li>`;
        document.getElementById("earningsList").innerHTML = earningsHtml;

        let deductionsHtml = "", totalDeductions = 0;
        for (let [label, amount] of Object.entries(emp.deductions)) {
            deductionsHtml += `<li class="list-group-item d-flex justify-content-between">
                                  <span>${label}</span><span>${formatCurrency(amount)}</span>
                               </li>`;
            totalDeductions += amount;
        }
        deductionsHtml += `<li class="list-group-item d-flex justify-content-between fw-bold">
                              <span>Total Deductions</span><span>${formatCurrency(totalDeductions)}</span>
                           </li>`;
        document.getElementById("deductionsList").innerHTML = deductionsHtml;

        const netPay = totalEarnings - totalDeductions;

        // Update preview
        document.getElementById("previewName").innerText = emp.name;
        document.getElementById("previewEarnings").innerText = formatCurrency(totalEarnings);
        document.getElementById("previewDeductions").innerText = formatCurrency(totalDeductions);
        document.getElementById("previewNetPay").innerText = formatCurrency(netPay);
    }

    // Load default employee on page load
    window.onload = updatePayslip;
</script>

@endsection