@extends('layouts.app')
@section('title','Trademark Register')
@section('content')

<div class="container my-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-success">Trademark Register</h2>
        <p class="text-muted">Manage company trademarks, registration dates, application dates, renewal deadlines, and registration status</p>
        <a href="{{ route('trademarkregister.create') }}" class="btn btn-outline-success">➕ Add New Trademark</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-success">
                        <tr>
                            <th scope="col">Trademark Name</th>
                            <th scope="col">Company</th>
                            <th scope="col">Registration Date</th>
                            <th scope="col">Application Date</th>
                            <th scope="col">Renewal Due</th>
                            <th scope="col">Registration Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $trademarks = [
                            ['id' => 1, 'name' => 'TechMark', 'company' => 'TechCorp Ltd', 'reg' => '2021-08-15', 'app' => '2021-06-20', 'renewal' => '2031-08-15', 'status' => 'Registered'],
                            ['id' => 2, 'name' => 'AquaSafe', 'company' => 'BlueWaters Inc', 'reg' => '2019-05-10', 'app' => '2019-03-25', 'renewal' => '2029-05-10', 'status' => 'Registered'],
                            ['id' => 3, 'name' => 'GreenGrow', 'company' => 'AgriNova Co.', 'reg' => '2020-02-01', 'app' => '2019-12-15', 'renewal' => '2030-02-01', 'status' => 'Registered'],
                            ['id' => 4, 'name' => 'SwiftPay', 'company' => 'FinSwift Ltd', 'reg' => '2022-01-20', 'app' => '2021-12-05', 'renewal' => '2032-01-20', 'status' => 'Registered']
                        ];

                        foreach ($trademarks as $tm) {
                            // Replace with real Laravel routes or PHP links
                            $editUrl = "edit.php?id={$tm['id']}";
                            $deleteUrl = "delete.php?id={$tm['id']}";

                            echo "<tr>
                                <td>{$tm['name']}</td>
                                <td>{$tm['company']}</td>
                                <td>{$tm['reg']}</td>
                                <td>{$tm['app']}</td>
                                <td>{$tm['renewal']}</td>
                                <td>{$tm['status']}</td>
                                <td>
                                    <a href='{$editUrl}' class='btn btn-sm btn-outline-primary me-2'>✏️ Edit</a>
                                    <a href='{$deleteUrl}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Are you sure you want to delete this trademark?\")'>🗑️ Delete</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="text-center text-muted mt-5">
        <hr>
        <p>&copy; <?php echo date("Y"); ?> Your Company. All rights reserved.</p>
    </footer>
</div>
@endsection