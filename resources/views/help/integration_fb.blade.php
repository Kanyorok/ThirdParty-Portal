<div>
    <p>Follow these steps to integrate Facebook with your CRM system:</p>

    <div class="mb-1 border-bottom border-1">
        <h2 class="text-primary">Step 1: Create a Facebook App</h2>
        <ol class="ms-1">
            <li>Go to <a href="https://developers.facebook.com/apps/creation/" target="_blank">Facebook Developers</a>.
            </li>
            <li>Select <strong>Other</strong> for the type and make sure to select the <strong>Page</strong> option.</li>
            <li>Once created, the <strong>APP ID</strong> will be located at the top right menu.</li>
            <li>Under <strong>App Settings > Basic</strong>, copy the <strong>App Secret</strong>.</li>
        </ol>
    </div>

    <div class="mb-1 border-bottom border-1">
        <h2 class="text-primary">Step 2: Enable Marketing API</h2>
        <ol class="ms-1">
            <li>Enable the <strong>Marketing API</strong> in the app settings.</li>
            <li>Generate an <strong>Access Token</strong> and check all permissions granted.</li>
            <li>Copy the generated token for later use.</li>
        </ol>
    </div>
    <div class="mb-1 border-bottom border-1">
        <h2 class="text-primary">Step 3: Generate Page Token</h2>
        <ol class="ms-1">
            <li>Go to <a href="https://developers.facebook.com/tools/explorer/" target="_blank">Facebook Graph API
                    Explorer</a>.
            </li>
            <li>The generated access token from Step 3 should already be there. If not, paste and click
                <strong>Submit</strong> to test.
            </li>
            <li>Make sure to select permissions:
                <code>{{ implode(', ', \App\Services\ThirdParty\FacebookService::PAGE_PERMISSIONS) }}</code></li>
            <li>Select the app created above, then choose either <strong>User</strong> or <strong>Page</strong>.</li>
            <li>Click <strong>Get Page Access Token</strong>.</li>
        </ol>
    </div>

    <div class="mb-1 border-bottom border-1">
        <h2 class="text-primary">Step 4: Verify Token</h2>
        <ol class="ms-1">
            <li>Verify the token on <a href="https://developers.facebook.com/tools/debug/accesstoken" target="_blank">Facebook
                    Access Token Debugger</a>.
            </li>
            <li>Verify permissions above.</li>
            <li>From this link, you will also get the <strong>Page ID</strong>.</li>
        </ol>
    </div>

    <div class="mb-1 border-bottom border-1">
        <h2 class="text-primary">Required Fields</h2>
        <p>The following fields are required:
            <strong>APP ID</strong>, <strong>APP Secret</strong>, <strong>Page ID</strong>,<strong>Page Token</strong>
        </p>
    </div>

    <p>If you have any issues, contact us.</p>

</div>
