@extends('layouts.app')
@section('title', 'Decrypt Bid Submission')
@section('content')
<div class="modal fade" id="decryptModal" tabindex="-1" aria-labelledby="decryptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form>
        <div class="modal-header">
          <h5 class="modal-title" id="decryptModalLabel">🔐 Decrypt Bid Submission</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <p>
            <strong>Tender:</strong> TND/PROC/2025/001<br>
            <strong>Supplier:</strong> Supplier A Ltd.
          </p>

          <div class="mb-3">
            <label for="otpOrKey" class="form-label">Enter OTP or Decryption Key</label>
            <input type="password" class="form-control" id="otpOrKey" placeholder="Enter secure OTP or key..." required>
          </div>

          <div class="form-text text-muted">
            This key is vendor-issued and required to unlock submitted bid documents.
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Decrypt Now</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection