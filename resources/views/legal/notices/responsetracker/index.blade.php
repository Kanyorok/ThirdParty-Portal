@extends('layouts.app')
@section('title','Response Tracker')
@section('content')

<div class="container my-5">

    <!-- Page Header -->
    <div class="text-center mb-4">
        <h2 class="text-primary fw-bold">Response Tracker</h2>
        <p class="text-muted">Maintaining history of replies and legal opinions</p>
    </div>

    <!-- PHP Section (All logic lives inside this div) -->
    <div class="response-list">
        <?php
        // Sample data
        $responses = [
            ['type' => 'Reply', 'subject' => 'IP Violation Notice', 'respondent' => 'Legal Team', 'date' => '2025-04-18', 'ref' => 'REP-2025-003', 'content' => 'Our response to the IP Violation claim received from XYZ Corp.', 'status' => 'Resolved', 'remarks' => 'No further action required.'],
            ['type' => 'Legal Opinion', 'subject' => 'Trademark Conflict', 'respondent' => 'External Counsel', 'date' => '2025-03-22', 'ref' => 'LOP-2025-014', 'content' => 'Opinion on the trademark conflict between ABC and DEF brands.', 'status' => 'Pending Review', 'remarks' => 'Awaiting additional information from ABC Corp.'],
            ['type' => 'Reply', 'subject' => 'Patent Extension Query', 'respondent' => 'Patents Dept', 'date' => '2025-05-03', 'ref' => 'REP-2025-010', 'content' => 'Our response regarding the patent extension request for product ABC123.', 'status' => 'In Progress', 'remarks' => 'Under internal review.'],
            ['type' => 'Reply', 'subject' => 'Privacy Policy Update', 'respondent' => 'Legal Team', 'date' => '2025-02-14', 'ref' => 'REP-2025-001', 'content' => 'Reply regarding updates needed to the Privacy Policy based on new GDPR rules.', 'status' => 'Completed', 'remarks' => 'Implemented changes, awaiting final approval.'],
            ['type' => 'Legal Opinion', 'subject' => 'Mergers and Acquisitions', 'respondent' => 'External Counsel', 'date' => '2025-01-11', 'ref' => 'LOP-2025-009', 'content' => 'Legal opinion on the merger between XYZ Ltd and ABC Co.', 'status' => 'Reviewed', 'remarks' => 'Client agreed to proceed as per the opinion.'],
        ];

        // Check if viewing a single response
        $viewResponse = null;
        if (isset($_GET['ref'])) {
            foreach ($responses as $res) {
                if ($res['ref'] === $_GET['ref']) {
                    $viewResponse = $res;
                    break;
                }
            }
        }

        // Filter form - show only when not in view mode
        if (!$viewResponse): ?>
            <form method="get" class="mb-4">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <input type="date" name="filter_date" class="form-control"
                               value="<?php echo htmlspecialchars($_GET['filter_date'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        <?php endif;

        // Display the card view if a single response is selected
        if ($viewResponse): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($viewResponse['type']); ?> - <?php echo htmlspecialchars($viewResponse['subject']); ?></h5>
                    <p><strong>Respondent:</strong> <?php echo htmlspecialchars($viewResponse['respondent']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($viewResponse['date']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($viewResponse['status']); ?></p>
                    <p><strong>Content:</strong> <?php echo htmlspecialchars($viewResponse['content']); ?></p>
                    <p><strong>Remarks:</strong> <?php echo htmlspecialchars($viewResponse['remarks']); ?></p>
                    <p><strong>Reference ID:</strong> <?php echo htmlspecialchars($viewResponse['ref']); ?></p>
                    <a href="?" class="btn btn-outline-primary mt-3">Back to List</a>
                </div>
            </div>
        <?php
        else:
            // If not viewing a response, display filtered list
            $filterDate = $_GET['filter_date'] ?? '';
            $filteredResponses = array_filter($responses, function ($res) use ($filterDate) {
                return !$filterDate || $res['date'] === $filterDate;
            });

            if (empty($filteredResponses)) {
                echo '<div class="alert alert-warning text-center">No responses found for selected date.</div>';
            } else {
                echo '<table class="table table-bordered table-striped">';
                echo '<thead><tr><th>Type</th><th>Subject</th><th>Respondent</th><th>Date</th><th>Status</th><th>Reference</th><th>Action</th></tr></thead><tbody>';
                foreach ($filteredResponses as $res) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($res['type']) . '</td>';
                    echo '<td>' . htmlspecialchars($res['subject']) . '</td>';
                    echo '<td>' . htmlspecialchars($res['respondent']) . '</td>';
                    echo '<td>' . htmlspecialchars($res['date']) . '</td>';
                    echo '<td>' . htmlspecialchars($res['status']) . '</td>';
                    echo '<td>' . htmlspecialchars($res['ref']) . '</td>';
                    echo '<td><a href="?ref=' . urlencode($res['ref']) . '" class="btn btn-sm btn-outline-secondary">View</a></td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
        endif;
        ?>
    </div>

    <!-- Footer -->
    <footer class="text-center text-muted mt-5">
        <hr>
        <p>&copy; <?php echo date("Y"); ?> Legal Records Office. All rights reserved.</p>
    </footer>

</div>

@endsection