<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $certificationName }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            font-family: DejaVu Sans, sans-serif;
            color: #1f2430;
        }

        .certificate-page {
            position: relative;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
            background: #ffffff;
        }

        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 210mm;
        }

        .fallback-frame {
            position: absolute;
            top: 6mm;
            left: 6mm;
            right: 6mm;
            bottom: 6mm;
            border: 2mm solid #0f3f8c;
        }

        .fallback-frame-inner {
            position: absolute;
            top: 12mm;
            left: 12mm;
            right: 12mm;
            bottom: 12mm;
            border: 0.6mm solid #d8b25d;
        }

        .overlay {
            position: absolute;
            top: 12mm;
            left: 14mm;
            right: 14mm;
            bottom: 12mm;
            text-align: center;
        }

        .certificate-number {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 10pt;
            color: #0f3f8c;
        }

        .org-name {
            margin-top: 14mm;
            font-size: 14pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .heading {
            margin-top: 8mm;
            font-size: 30pt;
            font-weight: 700;
            color: #0b1f8f;
        }

        .subheading {
            margin-top: 9mm;
            font-size: 17pt;
            font-style: italic;
        }

        .participant-name {
            margin: 11mm auto 0;
            width: 72%;
            border-bottom: 1.2mm solid #111111;
            font-size: 24pt;
            font-weight: 700;
            line-height: 1.4;
            min-height: 16mm;
        }

        .body-text {
            margin: 8mm auto 0;
            width: 85%;
            font-size: 14pt;
            line-height: 1.7;
        }

        .awarded-line {
            margin-top: 8mm;
            font-size: 15pt;
            font-style: italic;
            font-weight: 600;
        }

        .expiry-line {
            margin-top: 3mm;
            font-size: 11pt;
        }

        .signature-line {
            margin: 11mm auto 0;
            width: 33%;
            border-top: 0.7mm solid #333333;
            font-size: 10pt;
            padding-top: 2.5mm;
            font-style: italic;
        }

        .meta-line {
            margin-top: 2mm;
            font-size: 10pt;
        }
    </style>
</head>
<body>
<div class="certificate-page">
    @if($backgroundImageDataUri)
        <img class="background-image" src="{{ $backgroundImageDataUri }}" alt="Certificate background">
    @else
        <div class="fallback-frame"></div>
        <div class="fallback-frame-inner"></div>
    @endif

    <div class="overlay">
        @if(!empty($certificateNumber))
            <div class="certificate-number">Certificate No: {{ $certificateNumber }}</div>
        @endif

        <div class="org-name">{{ $issuingBody }}</div>
        <div class="heading">{{ $certificationName }}</div>
        <div class="subheading">This certifies that</div>

        <div class="participant-name">{{ $participantName }}</div>

        <div class="body-text">{!! nl2br(e($renderedTemplateBody)) !!}</div>

        <div class="awarded-line">Awarded this {{ $issuedOnLabel }}</div>

        @if(!empty($expiresOnLabel))
            <div class="expiry-line">Valid until {{ $expiresOnLabel }}</div>
        @endif

        <div class="signature-line">Authorized Signatory</div>
        <div class="meta-line">{{ $programName }} @if(!empty($sessionDateLabel)) | Session Date: {{ $sessionDateLabel }} @endif</div>
    </div>
</div>
</body>
</html>
