<div>
    <p>Follow these steps to integrate Google Maps for <strong>geocoding</strong> (address → coordinates) and <strong>reverse geocoding</strong> (coordinates → address):</p>

    <div class="mb-1 border-bottom border-1">
        <h5 class="text-primary">Step 1: Create a Google Cloud project</h5>
        <ol class="ms-1">
            <li>Go to <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>.</li>
            <li>Create a new project (or select an existing one).</li>
        </ol>
    </div>

    <div class="mb-1 border-bottom border-1">
        <h5 class="text-primary">Step 2: Enable Geocoding API</h5>
        <ol class="ms-1">
            <li>Open <a href="https://console.cloud.google.com/apis/library" target="_blank">API Library</a>.</li>
            <li>Search for <strong>Geocoding API</strong> and click <strong>Enable</strong>.</li>
            <li>Make sure <strong>Billing</strong> is enabled for the project (required by Google for most usage).</li>
        </ol>
    </div>

    <div class="mb-1 border-bottom border-1">
        <h5 class="text-primary">Step 3: Create an API key</h5>
        <ol class="ms-1">
            <li>Go to <a href="https://console.cloud.google.com/apis/credentials" target="_blank">APIs &amp; Services → Credentials</a>.</li>
            <li>Click <strong>Create credentials</strong> → <strong>API key</strong>.</li>
            <li>Copy the generated key and keep it safe.</li>
        </ol>
    </div>

    <div class="mb-1 border-bottom border-1">
        <h5 class="text-primary">Step 4: (Recommended) Restrict the key</h5>
        <ol class="ms-1">
            <li>On the same key screen, set <strong>Application restrictions</strong> (HTTP referrers / IP addresses) based on your environment.</li>
            <li>Set <strong>API restrictions</strong> to allow <strong>Geocoding API</strong> only.</li>
            <li>Save changes.</li>
        </ol>
    </div>

    <div class="mb-1 border-bottom border-1">
        <h5 class="text-primary">Required Fields</h5>
        <p class="mb-0">The following field is required:</p>
        <p><strong>Google Maps API Key</strong> (Geocoding API enabled)</p>
    </div>

    <div class="mb-1 border-bottom border-1">
        <h5 class="text-primary">Troubleshooting</h5>
        <ul class="ms-1">
            <li><strong>REQUEST_DENIED</strong>: billing not enabled, API not enabled, or key restrictions are too strict.</li>
            <li><strong>OVER_DAILY_LIMIT</strong>: quota exceeded or billing issue.</li>
            <li><strong>Invalid key</strong>: double-check you copied the full API key and saved the correct value in the integration settings.</li>
        </ul>
    </div>

    <p>If you have any issues, contact us.</p>

</div>
