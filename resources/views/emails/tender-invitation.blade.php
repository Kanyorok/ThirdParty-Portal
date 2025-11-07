<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tender Invitation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
            border-left: 4px solid #007bff;
        }

        .content {
            background-color: #fff;
            padding: 30px;
            border: 1px solid #e9ecef;
            border-radius: 0 0 8px 8px;
        }

        .tender-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .cta-button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }

        .cta-button:hover {
            background-color: #0056b3;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 0.9em;
        }

        .important {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>🎯 Tender Invitation</h1>
    <p><strong>{{ $tender->TenderNo }}</strong></p>
</div>

<div class="content">
    <p>Dear <strong>{{ $supplierName }}</strong>,</p>

    <p>We are pleased to invite you to participate in the following tender opportunity:</p>

    <div class="tender-details">
        <h3>📋 Tender Details</h3>
        <ul>
            <li><strong>Tender No:</strong> {{ $tender->TenderNo }}</li>
            <li><strong>Title:</strong> {{ $tender->Title }}</li>
            <li><strong>Type:</strong> Restricted Tender</li>
            <li><strong>Submission Deadline:</strong> <span
                    class="important">{{ $submissionDeadline->format('D, M j, Y - g:i A') }}</span></li>
            @if($tender->OpeningDate)
                <li><strong>Opening Date:</strong> {{ $tender->OpeningDate->format('D, M j, Y - g:i A') }}</li>
            @endif
        </ul>
    </div>

    @if($tender->ScopeOfWork)
        <div class="tender-details">
            <h3>🔍 Scope of Work</h3>
            <p>{{ $tender->ScopeOfWork }}</p>
        </div>
    @endif

    @if($tender->Instructions)
        <div class="tender-details">
            <h3>📝 Special Instructions</h3>
            <p>{{ $tender->Instructions }}</p>
        </div>
    @endif

    <p><strong>Next Steps:</strong></p>
    <ol>
        <li>Log into the procurement portal using the link below</li>
        <li>Review the complete tender documents and specifications</li>
        <li>Prepare and submit your proposal before the deadline</li>
        <li>Confirm your participation or decline with reasons</li>
    </ol>

    <div style="text-align: center;">
        <a href="{{ $portalUrl }}" class="cta-button">
            📱 Access Tender Portal
        </a>
    </div>

    <div class="tender-details">
        <p><strong>⚠️ Important Reminders:</strong></p>
        <ul>
            <li>Late submissions will <strong>NOT</strong> be accepted</li>
            <li>All mandatory documents must be included</li>
            <li>Please confirm your participation in the portal</li>
            <li>Contact us if you need clarification on any requirements</li>
        </ul>
    </div>

    <p>We look forward to receiving your competitive proposal.</p>

    <p>Best regards,<br>
        <strong>Procurement Department</strong><br>
        {{ config('app.name') }}</p>
</div>

<div class="footer">
    <p><small>This is an automated invitation. Please do not reply directly to this email.
            Use the procurement portal for all communications regarding this tender.</small></p>

    <p><small>📧 Portal URL: <a href="{{ $portalUrl }}">{{ $portalUrl }}</a></small></p>

    <p><small>⏰ Deadline: {{ $submissionDeadline->format('D, M j, Y - g:i A') }}</small></p>
</div>
</body>
</html>
