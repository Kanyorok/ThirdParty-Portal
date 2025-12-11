@extends('layouts.app')
@section('title', 'Attendance Management')
@section('content')

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Attendance Records</h4>
            <a href="{{route('attendancemanagement.create')}}" class="btn btn-light btn-sm">➕ Add Attendance</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center">
                    <thead class="table-secondary">
                        <tr>
                            <th>Employee_ID</th>
                            <th>Date</th>
                            <th>Full Name</th>
                            <th>ID Number</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $filename = "attendance_data.txt";
                        $count = 1;

                        if (file_exists($filename) && filesize($filename) > 0) {
                            $lines = file($filename, FILE_IGNORE_NEW_LINES);
                            foreach ($lines as $line) {
                                $parts = explode(" | ", $line);
                                if (count($parts) === 4) {
                                    echo "<tr>
                                            <td>{$count}</td>
                                            <td>" . htmlspecialchars($parts[0]) . "</td>
                                            <td>" . htmlspecialchars($parts[1]) . "</td>
                                            <td>" . htmlspecialchars($parts[2]) . "</td>
                                            <td>" . htmlspecialchars($parts[3]) . "</td>
                                          </tr>";
                                    $count++;
                                }
                            }
                        } else {
                            // Display static sample data
                            $staticData = [
                                ["2025-05-01", "Felix Mwai", "KU1234", "Present"],
                                ["2025-05-02", "Grace Wanjiru", "KU5678", "Absent"],
                                ["2025-05-03", "John Otieno", "KU3456", "Late"]
                            ];
                            foreach ($staticData as $row) {
                                echo "<tr>
                                        <td>{$count}</td>
                                        <td>{$row[0]}</td>
                                        <td>{$row[1]}</td>
                                        <td>{$row[2]}</td>
                                        <td>{$row[3]}</td>
                                      </tr>";
                                $count++;
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection