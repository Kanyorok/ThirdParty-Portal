@extends('layouts.app')
@section('title', 'New Budget Activity')
@section('content')

    <div class="container mt-4">
        <div class="card p-4">
            <h5>➕ New Budget Activity</h5>
            <form>
                <!-- Budget Line and Activity Info -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Budget Line</label>
                        <select class="form-select">
                            <option value="">-- Select Budget Line --</option>
                            <option>Marketing</option>
                            <option>Training & Development</option>
                            <option>Travel</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Activity Name</label>
                        <input type="text" class="form-control" placeholder="e.g. Q1 Campaign">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="2" placeholder="Brief description..."></textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cost Center</label>
                        <select class="form-select">
                            <option>-- Optional --</option>
                            <option>Head Office</option>
                            <option>Branch - West</option>
                            <option>Marketing Dept</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Activity Owner</label>
                        <select class="form-select">
                            <option>-- Optional --</option>
                            <option>Jane Mwangi</option>
                            <option>John Otieno</option>
                        </select>
                    </div>
                </div>

                <hr>

                <!-- Static Monthly Allocation Fields -->
                <h6>Monthly Allocation (KES)</h6>
                <div class="row">
                    <div class="col-md-3 mb-2"><label>January</label><input type="number" class="form-control"
                                                                            placeholder="0.00"></div>
                    <div class="col-md-3 mb-2"><label>February</label><input type="number" class="form-control"
                                                                             placeholder="0.00"></div>
                    <div class="col-md-3 mb-2"><label>March</label><input type="number" class="form-control"
                                                                          placeholder="0.00"></div>
                    <div class="col-md-3 mb-2"><label>April</label><input type="number" class="form-control"
                                                                          placeholder="0.00"></div>
                    <div class="col-md-3 mb-2"><label>May</label><input type="number" class="form-control"
                                                                        placeholder="0.00"></div>
                    <div class="col-md-3 mb-2"><label>June</label><input type="number" class="form-control"
                                                                         placeholder="0.00"></div>
                    <div class="col-md-3 mb-2"><label>July</label><input type="number" class="form-control"
                                                                         placeholder="0.00"></div>
                    <div class="col-md-3 mb-2"><label>August</label><input type="number" class="form-control"
                                                                           placeholder="0.00"></div>
                    <div class="col-md-3 mb-2"><label>September</label><input type="number" class="form-control"
                                                                              placeholder="0.00"></div>
                    <div class="col-md-3 mb-2"><label>October</label><input type="number" class="form-control"
                                                                            placeholder="0.00"></div>
                    <div class="col-md-3 mb-2"><label>November</label><input type="number" class="form-control"
                                                                             placeholder="0.00"></div>
                    <div class="col-md-3 mb-2"><label>December</label><input type="number" class="form-control"
                                                                             placeholder="0.00"></div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">💾 Save Activity</button>
                </div>
            </form>
        </div>
    </div>

@endsection
