@extends('layouts.app')
@section('title', 'Tender & RFQ Awards Overview')
@section('content')

    <div class="container mt-4">
        <h4 class="mb-3">🏆 Tender & RFQ Awards Overview</h4>

        <!-- Filters -->
        <form class="row g-3 mb-3">
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>All Types</option>
                    <option value="Tender">Tender</option>
                    <option value="RFQ">RFQ</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>All Status</option>
                    <option value="Awarded">Awarded</option>
                    <option value="Pending">Pending</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Search Ref / Title">
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Awards Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Ref No.</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Winning Bidder</th>
                            <th>Date Awarded</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>1</td>
                            <td>TENDER/ICT/2025/004</td>
                            <td>Procurement of ICT Equipment</td>
                            <td>Tender</td>
                            <td><span class="badge bg-success">Awarded</span></td>
                            <td>CompTech Solutions Ltd</td>
                            <td>2025-06-25</td>
                            <td class="text-center">
                                <a href="/awards-tender/view/1" class="btn btn-sm btn-outline-secondary">View</a>
                                <a href="/awards/letter/1" class="btn btn-sm btn-outline-primary">Letter</a>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>RFQ/ADMIN/2025/022</td>
                            <td>Supply of Office Chairs</td>
                            <td>RFQ</td>
                            <td><span class="badge bg-warning text-dark">Pending</span></td>
                            <td>--</td>
                            <td>--</td>
                            <td class="text-center">
                                <a href="/awards-rfq/view/1" class="btn btn-sm btn-outline-secondary">View</a>
                                <a href="/awards/recommend/2" class="btn btn-sm btn-success">Award Now</a>
                            </td>
                        </tr>
                        <!-- More rows dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
