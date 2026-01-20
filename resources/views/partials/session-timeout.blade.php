<div class="modal fade" id="sessionTimeoutModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="sessionTimeoutLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark" id="sessionTimeoutLabel">
                    <i class="fas fa-exclamation-triangle"></i> Session Timeout Warning
                </h5>
            </div>
            <div class="modal-body">
                <p>Your session will expire in <span id="sessionTimeoutTimer" class="fw-bold text-danger"></span>.</p>
                <p>Please click <strong>Stay Logged In</strong> to continue your session.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('logout-form').submit();">Log Out</button>
                <button type="button" class="btn btn-primary" id="stayLoggedInBtn">Stay Logged In</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Configuration
    const sessionLifetimeMinutes = {{ config('session.lifetime', 30) }};
    const warningMinutes = 5; 
    // Convert to milliseconds
    const sessionLifetimeMs = sessionLifetimeMinutes * 60 * 1000;
    const warningThresholdMs = (sessionLifetimeMinutes - warningMinutes) * 60 * 1000;
    
    let warningTimer;
    let logoutTimer;
    const modalElement = document.getElementById('sessionTimeoutModal');
    let modal;

    if (modalElement && typeof bootstrap !== 'undefined') {
        modal = new bootstrap.Modal(modalElement);
    }

    function startTimers() {
        clearTimeout(warningTimer);
        clearTimeout(logoutTimer);

        // Timer to show warning modal
        warningTimer = setTimeout(() => {
            if (modal) {
                modal.show();
                startCountdown(warningMinutes * 60);
            }
        }, warningThresholdMs);

        // Timer to auto-logout (if ignored)
        logoutTimer = setTimeout(() => {
            window.location.reload(); // Will verify auth and redirect to login
        }, sessionLifetimeMs);
    }

    function startCountdown(seconds) {
        const timerSpan = document.getElementById('sessionTimeoutTimer');
        let remaining = seconds;
        
        const interval = setInterval(() => {
            remaining--;
            const mins = Math.floor(remaining / 60);
            const secs = remaining % 60;
            timerSpan.textContent = `${mins}m ${secs}s`;

            if (remaining <= 0) {
                clearInterval(interval);
            }
            // If modal is hidden, stop countdown
            if (!modalElement.classList.contains('show')) {
                 clearInterval(interval);
            }
        }, 1000);
    }

    // Reset timers on user activity (optional - if you want activity to extend session client-side 
    // BUT strictly speaking session is server-side. We should only reset when we hit the server.)
    // For security in ERP, valid inactivity timeout is good.
    // So we ONLY reset when they interact with the "Stay Logged In" button.

    document.getElementById('stayLoggedInBtn').addEventListener('click', function () {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Renewing...';

        fetch("{{ route('session.heartbeat') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                if (modal) modal.hide();
                startTimers(); // Restart local timers
            } else {
                location.reload(); // If Ping fails, likely already logged out
            }
        })
        .catch(err => {
            console.error('Session renew failed', err);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Stay Logged In';
        });
    });

    // Start timers on load
    startTimers();
});
</script>
