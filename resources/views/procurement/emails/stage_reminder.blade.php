<h2>Reminder: Upcoming Tender Stage</h2>
<p><strong>Tender:</strong> {{ $tender->TenderNumber }} - {{ $tender->Title }}</p>
<p><strong>Stage:</strong> {{ $stage->Stage }}</p>
<p><strong>Deadline:</strong> {{ \Carbon\Carbon::parse($stage->EndDate)->format('d M Y') }}</p>

<p>This stage is due in 2 days. Please prepare accordingly.</p>
