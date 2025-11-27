<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; text-align: center; padding: 50px; }
        .title { font-size: 28px; font-weight: bold; margin-bottom: 20px; }
        .cert-body { font-size: 18px; margin-top: 40px; }
        .footer { margin-top: 60px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="title">Certificate of Completion</div>
    <div class="cert-body">
        This certifies that<br><br>
        <strong>{{ $participant->UserID }}</strong><br><br>
        has successfully completed the training<br><br>
        <strong>{{ $training->Topic }}</strong><br>
        held on <strong>{{ \Carbon\Carbon::parse($training->SessionDate)->format('F j, Y') }}</strong>.
    </div>
    <div class="footer">
        Issued on {{ \Carbon\Carbon::parse($certification->IssueDate)->format('F j, Y') }}<br>
        {{ config('app.name') }} Compliance Department
    </div>
</body>
</html>
