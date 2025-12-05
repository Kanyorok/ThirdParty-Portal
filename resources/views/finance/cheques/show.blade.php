@extends('layouts.app')

@section('title', 'Cheque Details')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- Top Navigation & Title --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <!-- <h4 class="mb-0 fw-bold text-dark">Cheque Details</h4>
                    <span class="text-muted small">Manage and track cheque status</span> -->
                </div>
                <a href="{{ route('finance.cheques.index',['dir'=>$row->Direction]) }}" class="btn btn-outline-secondary shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success shadow-sm border-0 border-start border-success border-4">
                    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger shadow-sm border-0 border-start border-danger border-4">
                    <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                </div>
            @endif

            <div class="card shadow-sm rounded-3 mb-4 border-0">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-light rounded p-2 text-primary">
                                <i class="fas fa-money-check fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">#{{ $row->ChequeNumber }}</h5>
                                <small class="text-muted">{{ $row->Direction }} Cheque</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @php
                                $badgeClass = match($row->Status) {
                                    'Cleared' => 'success',
                                    'Bounced' => 'danger',
                                    'Deposited' => 'info',
                                    'OnHand' => 'warning text-dark',
                                    'Issued' => 'primary',
                                    'Cancelled' => 'secondary',
                                    default => 'dark'
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeClass }} fs-6 px-3 py-2 rounded-pill">{{ $row->Status }}</span>
                            
                            {{-- Action Buttons Dropdown or Group --}}
                            @php
                                $currentStatus = $row->Status;
                                
                                // Check if cheque is in an actionable state (not Cleared, Bounced, Cancelled, Void, Spoiled)
                                $nonActionableStatuses = ['Cleared', 'Bounced', 'Cancelled', 'Void', 'Spoiled', 'C', 'B', 'V', 'S', 'Ca'];
                                $isActionable = !in_array($currentStatus, $nonActionableStatuses);
                            @endphp
                            
                            @if($isActionable)
                                <div class="dropdown ms-2">
                                    <button class="btn btn-primary dropdown-toggle" type="button" id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="actionDropdown">
                                        {{-- Deposit (For RECEIVED cheques that are OnHand/Available) --}}
                                        @if($row->Direction==='RECEIVED' && in_array($currentStatus, ['OnHand', 'A', 'Available']))
                                            <li>
                                                <button class="dropdown-item text-info" data-bs-toggle="modal" data-bs-target="#depositModal">
                                                    <i class="fas fa-piggy-bank me-2"></i> Deposit
                                                </button>
                                            </li>
                                        @endif

                                        {{-- Clear (For ISSUED/Issued or RECEIVED/Deposited) --}}
                                        @if( ($row->Direction==='ISSUED' && in_array($currentStatus, ['Issued', 'I'])) || 
                                             ($row->Direction==='RECEIVED' && in_array($currentStatus, ['Deposited', 'PD', 'Post-Dated Cheque', 'P', 'Posted'])) )
                                            <li>
                                                <button class="dropdown-item text-success" data-bs-toggle="modal" data-bs-target="#clearModal">
                                                    <i class="fas fa-check-double me-2"></i> Mark Cleared
                                                </button>
                                            </li>
                                        @endif

                                        {{-- Bounce (For RECEIVED/Deposited or ISSUED/Issued) --}}
                                        @if( ($row->Direction==='RECEIVED' && in_array($currentStatus, ['Deposited', 'PD', 'Post-Dated Cheque', 'P', 'Posted'])) || 
                                             ($row->Direction==='ISSUED' && in_array($currentStatus, ['Issued', 'I'])) )
                                            <li>
                                                <button class="dropdown-item text-warning" data-bs-toggle="modal" data-bs-target="#bounceModal">
                                                    <i class="fas fa-exclamation-triangle me-2"></i> Mark Bounced
                                                </button>
                                            </li>
                                        @endif

                                        {{-- Show divider if there are actions above --}}
                                        @if( ($row->Direction==='RECEIVED' && in_array($currentStatus, ['OnHand', 'A', 'Available', 'Deposited', 'PD', 'Post-Dated Cheque', 'P', 'Posted'])) || 
                                             ($row->Direction==='ISSUED' && in_array($currentStatus, ['Issued', 'I', 'Draft'])) )
                                            <li><hr class="dropdown-divider"></li>
                                        @endif

                                        {{-- Cancel (For Draft, Issued, or OnHand) --}}
                                        @if(in_array($currentStatus, ['Draft', 'Issued', 'OnHand', 'I', 'A', 'Available', 'U', 'Used']))
                                            <li>
                                                <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                                    <i class="fas fa-ban me-2"></i> Cancel Cheque
                                                </button>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    {{-- Row 1: Amount, Dates, Party, Bank --}}
                    <div class="row g-4">
                        {{-- Amount --}}
                        <div class="col-md-3 border-end">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Amount</label>
                            <div class="d-flex align-items-baseline">
                                <h4 class="mb-0 fw-bold text-dark me-1">{{ number_format($row->Amount, 2) }}</h4>
                                <span class="text-muted fw-bold">{{ $row->currency?->Code }}</span>
                            </div>
                        </div>

                        {{-- Dates --}}
                        <div class="col-md-3 border-end">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Dates</label>
                            <div class="d-flex flex-column">
                                <div class="mb-1">
                                    <span class="text-muted small w-50 d-inline-block">Cheque:</span>
                                    <span class="fw-bold text-dark">{{ $row->ChequeDate ? \Illuminate\Support\Carbon::parse($row->ChequeDate)->format('d M, Y') : '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-muted small w-50 d-inline-block">Due:</span>
                                    <span class="fw-bold text-dark">{{ $row->DueDate ? \Illuminate\Support\Carbon::parse($row->DueDate)->format('d M, Y') : '-' }}</span>
                                    @if($row->IsPostDated)
                                        <span class="badge bg-light text-danger border border-danger ms-1" style="font-size: 0.65rem;">PDC</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Party Details --}}
                        <div class="col-md-3 border-end">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Party Details</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 me-2 text-secondary" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="text-truncate">
                                    <div class="fw-bold text-dark text-truncate" title="{{ $row->PartyName }}">{{ $row->PartyName ?: 'N/A' }}</div>
                                    <div class="small text-muted">{{ $row->PartyType }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Bank Details --}}
                        <div class="col-md-3">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Bank Details</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 me-2 text-secondary" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-university"></i>
                                </div>
                                <div class="text-truncate">
                                    <div class="fw-bold text-dark text-truncate" title="{{ optional($row->bankAccount?->bank)->BankName ?? $row->Reference }}">
                                        {{ optional($row->bankAccount?->bank)->BankName ?? $row->Reference ?? 'N/A' }}
                                    </div>
                                    <div class="small text-muted text-truncate">{{ $row->bankAccount?->AccountNumber ?? 'Ref: ' . ($row->Reference ?? '-') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-10">

                    {{-- Row 2: Narration --}}
                    <div class="row">
                        <div class="col-12">
                            <label class="text-muted small fw-bold text-uppercase mb-1">Narration</label>
                            <div class="bg-light rounded p-3 text-dark">
                                <i class="fas fa-quote-left text-muted me-2 opacity-50"></i>
                                {{ $row->Narration ?: 'No narration provided.' }}
                            </div>
                        </div>
                    </div>

                    {{-- Timeline --}}
                    @if($row->Direction==='RECEIVED' || $row->Status !== 'Issued')
                        <div class="row mt-4">
                            <div class="col-12">
                                <label class="text-muted small fw-bold text-uppercase mb-2">Status Timeline</label>
                                <div class="d-flex justify-content-between text-center bg-white border rounded p-3">
                                    @if($row->Direction==='RECEIVED')
                                        <div class="flex-fill">
                                            <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Received</small>
                                            <strong class="text-dark">{{ $row->ReceivedDate ? \Illuminate\Support\Carbon::parse($row->ReceivedDate)->format('d M, Y') : '-' }}</strong>
                                        </div>
                                        <div class="vr mx-2 text-muted opacity-25"></div>
                                        <div class="flex-fill">
                                            <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Deposited</small>
                                            <strong class="text-info">{{ $row->DepositDate ? \Illuminate\Support\Carbon::parse($row->DepositDate)->format('d M, Y') : '-' }}</strong>
                                        </div>
                                        <div class="vr mx-2 text-muted opacity-25"></div>
                                    @endif
                                    <div class="flex-fill">
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Cleared</small>
                                        <strong class="text-success">{{ $row->ClearDate ? \Illuminate\Support\Carbon::parse($row->ClearDate)->format('d M, Y') : '-' }}</strong>
                                    </div>
                                    <div class="vr mx-2 text-muted opacity-25"></div>
                                    <div class="flex-fill">
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Bounced</small>
                                        <strong class="text-danger">{{ $row->BounceDate ? \Illuminate\Support\Carbon::parse($row->BounceDate)->format('d M, Y') : '-' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Cheque Preview --}}
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm rounded-3 mb-4 border-0">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-eye me-2 text-primary"></i>Cheque Preview</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="printCheque()">
                        <i class="fas fa-download me-1"></i> Download / Print
                    </button>
                </div>
                <div class="card-body p-4 d-flex justify-content-center" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                    <div id="cheque-preview" class="position-relative shadow" style="width: 850px; height: 380px; background: linear-gradient(to bottom, #ffffff 0%, #fefefe 100%); border: 2px solid #2c3e50; border-radius: 8px; overflow: hidden;">
                        
                        {{-- Watermark - Behind Content --}}
                        <div class="position-absolute w-100 h-100" style="z-index: 0; overflow: hidden;">
                            <div class="position-absolute top-50 start-50 text-muted fw-bold" 
                                 style="font-size: 6rem; 
                                        opacity: 0.04; 
                                        transform: translate(-50%, -50%) rotate(-35deg);
                                        pointer-events: none; 
                                        user-select: none;
                                        letter-spacing: 8px;">
                                {{ strtoupper($row->Status) }}
                            </div>
                        </div>

                        {{-- Security Pattern Background --}}
                        <div class="position-absolute w-100 h-100" style="z-index: 0; background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(200,200,200,.03) 10px, rgba(200,200,200,.03) 20px);"></div>

                        {{-- Main Content --}}
                        <div class="position-relative" style="z-index: 1;">
                            {{-- Top Section with Bank Details --}}
                            <div class="d-flex justify-content-between align-items-start p-4 border-bottom" style="border-bottom: 2px solid #e9ecef !important;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-landmark text-white fs-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0 fw-bold text-primary" style="letter-spacing: 1px;">
                                            {{ optional($row->bankAccount?->bank)->BankName ?? 'BANK NAME' }}
                                        </h4>
                                        <small class="text-muted">Account: {{ $row->bankAccount?->AccountNumber ?? '0000000000' }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="badge bg-dark px-3 py-2 mb-2">
                                        <small class="text-uppercase fw-bold">Cheque No.</small>
                                        <div class="fs-5 fw-bold font-monospace">{{ $row->ChequeNumber }}</div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-end gap-2 mt-1">
                                        <small class="text-muted fw-bold">Date:</small>
                                        <div class="border border-dark px-2 py-1 bg-white font-monospace" style="border-radius: 4px; letter-spacing: 1px;">
                                            {{ $row->ChequeDate ? \Illuminate\Support\Carbon::parse($row->ChequeDate)->format('d/m/Y') : '__/__/____' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Pay To Section --}}
                            <div class="px-4 pt-4 pb-3">
                                <div class="mb-3">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="text-uppercase fw-bold small text-muted" style="min-width: 140px;">Pay to the Order of</span>
                                        <div class="flex-grow-1 position-relative">
                                            <div class="border-bottom border-2 border-dark pb-1 px-2 fw-bold" style="font-size: 1.1rem; color: #2c3e50;">
                                                {{ $row->PartyName ?: '___________________________________________' }}
                                            </div>
                                        </div>
                                        <div class="ms-2 px-3 py-2 bg-light border border-2 border-dark fw-bold" style="border-radius: 4px; min-width: 140px; text-align: center;">
                                            <div style="font-size: 1.2rem; color: #2c3e50;">{{ $row->currency?->Code }} {{ number_format($row->Amount, 2) }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex align-items-start gap-2">
                                        <span class="text-uppercase fw-bold small text-muted pt-1" style="min-width: 140px;">Amount in Words</span>
                                        <div class="flex-grow-1">
                                            <div class="border-bottom border-2 border-dark pb-1 px-2 text-capitalize fst-italic fw-bold" style="color: #2c3e50; min-height: 30px;">
                                                {{ $amountInWords ?? '***' }} only
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <div class="d-flex align-items-start gap-2">
                                        <span class="text-uppercase fw-bold small text-muted pt-1" style="min-width: 140px;">Memo / Reference</span>
                                        <div class="flex-grow-1">
                                            <div class="border-bottom border-dark pb-1 px-2 small text-muted">
                                                {{ $row->Reference ?? $row->Narration ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Signature Section --}}
                            <div class="px-4 pb-2">
                                <div class="d-flex justify-content-end">
                                    <div class="text-center" style="width: 220px;">
                                        <div class="border-top border-2 border-dark pt-1 mt-3">
                                            <small class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem; letter-spacing: 1px;">Authorized Signatory</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- MICR Code Line at Bottom --}}
                            <div class="position-absolute bottom-0 w-100 px-4 py-2" style="background: linear-gradient(to right, #f8f9fa, #e9ecef, #f8f9fa); border-top: 1px solid #dee2e6;">
                                <div class="font-monospace text-center fw-bold" style="font-size: 0.9rem; letter-spacing: 4px; color: #6c757d;">
                                    {{-- MICR Format: C{ChequeNumber}C A{AccountNumber}A {ChequeID}B --}}
                                    <span style="font-family: 'Courier New', monospace;" title="Cheque Number (6 digits)">C{{ str_pad($row->ChequeNumber, 6, '0', STR_PAD_LEFT) }}C</span>
                                    <span class="mx-2" title="Bank Account Number">A{{ $row->bankAccount?->AccountNumber ?? '000000' }}A</span>
                                    <span title="Cheque ID (8 digits)">{{ str_pad($row->ChequeID, 8, '0', STR_PAD_LEFT) }}B</span>
                                </div>
                                <div class="text-center mt-1">
                                    <small class="text-muted" style="font-size: 0.65rem;">MICR Code: Cheque# (6 digits) | Account# | Cheque ID (8 digits)</small>
                                </div>
                            </div>
                        </div>

                        {{-- Security Features Corner --}}
                        <div class="position-absolute top-0 start-0 m-2" style="opacity: 0.3;">
                            <div class="d-flex gap-1">
                                <div class="bg-primary" style="width: 3px; height: 20px; border-radius: 2px;"></div>
                                <div class="bg-success" style="width: 3px; height: 20px; border-radius: 2px;"></div>
                                <div class="bg-danger" style="width: 3px; height: 20px; border-radius: 2px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function printCheque() {
            // Create a hidden iframe for direct printing
            var iframe = document.createElement('iframe');
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = 'none';
            document.body.appendChild(iframe);

            var printContents = document.getElementById('cheque-preview').outerHTML;
            var iframeDoc = iframe.contentWindow.document;
            
            iframeDoc.open();
            iframeDoc.write('<html><head><title>Print Cheque - #{{ $row->ChequeNumber }}</title>');
            iframeDoc.write('<style>');
            // Reset and base styles
            iframeDoc.write('* { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }');
            iframeDoc.write('body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #fff; padding: 20px; }');
            
            // Bootstrap utility classes we need
            iframeDoc.write('.position-relative { position: relative !important; }');
            iframeDoc.write('.position-absolute { position: absolute !important; }');
            iframeDoc.write('.d-flex { display: flex !important; }');
            iframeDoc.write('.flex-grow-1 { flex-grow: 1 !important; }');
            iframeDoc.write('.flex-fill { flex: 1 1 auto !important; }');
            iframeDoc.write('.justify-content-between { justify-content: space-between !important; }');
            iframeDoc.write('.justify-content-end { justify-content: flex-end !important; }');
            iframeDoc.write('.align-items-center { align-items: center !important; }');
            iframeDoc.write('.align-items-start { align-items: flex-start !important; }');
            iframeDoc.write('.align-items-end { align-items: flex-end !important; }');
            iframeDoc.write('.gap-1 { gap: 0.25rem !important; }');
            iframeDoc.write('.gap-2 { gap: 0.5rem !important; }');
            iframeDoc.write('.gap-3 { gap: 1rem !important; }');
            iframeDoc.write('.w-100 { width: 100% !important; }');
            iframeDoc.write('.h-100 { height: 100% !important; }');
            iframeDoc.write('.top-0 { top: 0 !important; }');
            iframeDoc.write('.bottom-0 { bottom: 0 !important; }');
            iframeDoc.write('.start-0 { left: 0 !important; }');
            iframeDoc.write('.start-50 { left: 50% !important; }');
            iframeDoc.write('.end-0 { right: 0 !important; }');
            iframeDoc.write('.top-50 { top: 50% !important; }');
            iframeDoc.write('.translate-middle { transform: translate(-50%, -50%) !important; }');
            iframeDoc.write('.translate-middle-x { transform: translateX(-50%) !important; }');
            iframeDoc.write('.text-center { text-align: center !important; }');
            iframeDoc.write('.text-end { text-align: right !important; }');
            iframeDoc.write('.text-uppercase { text-transform: uppercase !important; }');
            iframeDoc.write('.text-capitalize { text-transform: capitalize !important; }');
            iframeDoc.write('.text-muted { color: #6c757d !important; }');
            iframeDoc.write('.text-primary { color: #0d6efd !important; }');
            iframeDoc.write('.text-white { color: #fff !important; }');
            iframeDoc.write('.fw-bold { font-weight: 700 !important; }');
            iframeDoc.write('.fst-italic { font-style: italic !important; }');
            iframeDoc.write('.small { font-size: 0.875em !important; }');
            iframeDoc.write('.fs-4 { font-size: calc(1.275rem + 0.3vw) !important; }');
            iframeDoc.write('.fs-5 { font-size: 1.25rem !important; }');
            iframeDoc.write('.mb-0 { margin-bottom: 0 !important; }');
            iframeDoc.write('.mb-1 { margin-bottom: 0.25rem !important; }');
            iframeDoc.write('.mb-2 { margin-bottom: 0.5rem !important; }');
            iframeDoc.write('.mb-3 { margin-bottom: 1rem !important; }');
            iframeDoc.write('.mb-4 { margin-bottom: 1.5rem !important; }');
            iframeDoc.write('.mt-1 { margin-top: 0.25rem !important; }');
            iframeDoc.write('.mt-2 { margin-top: 0.5rem !important; }');
            iframeDoc.write('.mt-3 { margin-top: 1rem !important; }');
            iframeDoc.write('.mt-5 { margin-top: 3rem !important; }');
            iframeDoc.write('.ms-2 { margin-left: 0.5rem !important; }');
            iframeDoc.write('.me-2 { margin-right: 0.5rem !important; }');
            iframeDoc.write('.mx-2 { margin-left: 0.5rem !important; margin-right: 0.5rem !important; }');
            iframeDoc.write('.m-2 { margin: 0.5rem !important; }');
            iframeDoc.write('.m-4 { margin: 1.5rem !important; }');
            iframeDoc.write('.p-2 { padding: 0.5rem !important; }');
            iframeDoc.write('.p-4 { padding: 1.5rem !important; }');
            iframeDoc.write('.px-2 { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }');
            iframeDoc.write('.px-3 { padding-left: 1rem !important; padding-right: 1rem !important; }');
            iframeDoc.write('.px-4 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }');
            iframeDoc.write('.py-1 { padding-top: 0.25rem !important; padding-bottom: 0.25rem !important; }');
            iframeDoc.write('.py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }');
            iframeDoc.write('.py-3 { padding-top: 1rem !important; padding-bottom: 1rem !important; }');
            iframeDoc.write('.pt-1 { padding-top: 0.25rem !important; }');
            iframeDoc.write('.pb-1 { padding-bottom: 0.25rem !important; }');
            iframeDoc.write('.pb-2 { padding-bottom: 0.5rem !important; }');
            iframeDoc.write('.pb-3 { padding-bottom: 1rem !important; }');
            iframeDoc.write('.border { border: 1px solid #dee2e6 !important; }');
            iframeDoc.write('.border-2 { border-width: 2px !important; }');
            iframeDoc.write('.border-top { border-top: 1px solid #dee2e6 !important; }');
            iframeDoc.write('.border-bottom { border-bottom: 1px solid #dee2e6 !important; }');
            iframeDoc.write('.border-dark { border-color: #212529 !important; }');
            iframeDoc.write('.rounded-circle { border-radius: 50% !important; }');
            iframeDoc.write('.bg-primary { background-color: #0d6efd !important; }');
            iframeDoc.write('.bg-success { background-color: #198754 !important; }');
            iframeDoc.write('.bg-danger { background-color: #dc3545 !important; }');
            iframeDoc.write('.bg-light { background-color: #f8f9fa !important; }');
            iframeDoc.write('.bg-dark { background-color: #212529 !important; }');
            iframeDoc.write('.bg-white { background-color: #fff !important; }');
            iframeDoc.write('.bg-gradient { background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0)) !important; }');
            iframeDoc.write('.shadow { box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; }');
            iframeDoc.write('.badge { display: inline-block; padding: 0.35em 0.65em; font-size: 0.75em; font-weight: 700; line-height: 1; color: #fff; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 0.25rem; }');
            iframeDoc.write('.font-monospace { font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important; }');
            
            // Font Awesome icons (minimal inline)
            iframeDoc.write('.fas { font-family: "Font Awesome 6 Free"; font-weight: 900; display: inline-block; font-style: normal; font-variant: normal; text-rendering: auto; line-height: 1; }');
            iframeDoc.write('.fa-landmark:before { content: "\\f66f"; }');
            
            // Cheque specific styles
            iframeDoc.write('#cheque-preview { box-shadow: none !important; border: 2px solid #000 !important; }');
            
            // Print styles
            iframeDoc.write('@media print {');
            iframeDoc.write('  * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }');
            iframeDoc.write('  body { background: white; padding: 0; margin: 0; }');
            iframeDoc.write('  #cheque-preview { page-break-inside: avoid; background: linear-gradient(to bottom, #ffffff 0%, #fefefe 100%) !important; }');
            iframeDoc.write('  @page { margin: 0.5cm; }');
            iframeDoc.write('}');
            iframeDoc.write('</style>');
            iframeDoc.write('</head><body>');
            iframeDoc.write(printContents);
            iframeDoc.write('</body></html>');
            iframeDoc.close();

            // Wait for content to load, then print
            iframe.onload = function() {
                setTimeout(function() {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                    // Remove iframe after printing
                    setTimeout(function() {
                        document.body.removeChild(iframe);
                    }, 1000);
                }, 500);
            };
        }
    </script>

    {{-- MODALS --}}

    {{-- Deposit Modal --}}
    <div class="modal fade" id="depositModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-piggy-bank text-info me-2"></i> Deposit Cheque</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('finance.cheques.deposit',$row->ChequeID) }}" class="action-form">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deposit To Bank Account <span class="text-danger">*</span></label>
                            <select name="BankAccountID" class="form-select" required>
                                <option value="">-- Select Account --</option>
                                @foreach(\App\Models\Finance\BankAccount::with('bank')->orderBy('AccountNumber')->get() as $ba)
                                    <option value="{{ $ba->AccountID }}">{{ optional($ba->bank)->BankName }} — {{ $ba->AccountNumber }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deposit Date <span class="text-danger">*</span></label>
                            <input type="date" name="DocDate" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info text-white btn-submit">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="btn-text">Deposit</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Clear Modal --}}
    <div class="modal fade" id="clearModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-check-double text-success me-2"></i> Mark as Cleared</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('finance.cheques.clear',$row->ChequeID) }}" class="action-form">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Clear Date <span class="text-danger">*</span></label>
                            <input type="date" name="DocDate" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <p class="text-muted small mb-0">This will mark the cheque as cleared and update the ledger.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-submit">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="btn-text">Confirm Clear</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bounce Modal --}}
    <div class="modal fade" id="bounceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle text-warning me-2"></i> Mark as Bounced</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('finance.cheques.bounce',$row->ChequeID) }}" class="action-form">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Bounce Date <span class="text-danger">*</span></label>
                            <input type="date" name="DocDate" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason</label>
                            <textarea name="Reason" class="form-control" rows="2" placeholder="e.g., Insufficient funds"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-dark btn-submit">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="btn-text">Confirm Bounce</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Cancel Modal --}}
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger"><i class="fas fa-ban me-2"></i> Cancel Cheque</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('finance.cheques.cancel',$row->ChequeID) }}" class="action-form">
                    @csrf
                    <div class="modal-body">
                        <p class="fw-bold mb-1">Are you sure you want to cancel this cheque?</p>
                        <p class="text-muted small">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep Cheque</button>
                        <button type="submit" class="btn btn-danger btn-submit">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="btn-text">Yes, Cancel It</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.action-form').forEach(form => {
            form.addEventListener('submit', function() {
                const btn = this.querySelector('.btn-submit');
                if(btn) {
                    const spinner = btn.querySelector('.spinner-border');
                    const text = btn.querySelector('.btn-text');

                    btn.disabled = true;
                    if(spinner) spinner.classList.remove('d-none');
                    if(text) text.textContent = 'Processing...';
                }
            });
        });
    </script>
@endsection
