<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $payment->code }} – Rilip Traders Limited</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            background: #e8e8e8;
        }

        .print-btn-wrap {
            text-align: center;
            padding: 16px;
        }

        .print-btn {
            background: #1b6b5a;
            color: #fff;
            border: none;
            padding: 10px 32px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            letter-spacing: 0.5px;
        }

        .print-btn:hover { background: #145449; }

        .document {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 32px;
            background: #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
        }

        /* ── Header ── */
        .doc-header {
            background: #1b6b5a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 32px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .header-logo {
            height: 80px;
            width: auto;
            flex-shrink: 0;
            background: #fff;
            border-radius: 4px;
            padding: 4px;
        }

        .header-company p {
            font-size: 12px;
            line-height: 1.6;
            opacity: 0.88;
        }

        .header-right {
            text-align: right;
        }

        .header-right .doc-type {
            font-size: 38px;
            font-weight: 700;
            letter-spacing: 1px;
            line-height: 1.1;
        }

        .header-right .doc-subtype {
            font-size: 13px;
            opacity: 0.8;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* ── Meta bar ── */
        .doc-meta {
            background: #145449;
            color: #fff;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
        }

        .meta-cell {
            padding: 10px 16px;
            border-right: 1px solid rgba(255,255,255,0.12);
        }

        .meta-cell:last-child { border-right: none; }

        .meta-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
            margin-bottom: 3px;
        }

        .meta-value {
            font-size: 13px;
            font-weight: 600;
        }

        /* ── Received from ── */
        .doc-received {
            padding: 28px 32px 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .received-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1b6b5a;
            border-bottom: 2px solid #1b6b5a;
            padding-bottom: 4px;
            margin-bottom: 12px;
            display: inline-block;
        }

        .received-name {
            font-size: 22px;
            font-weight: 700;
            color: #1b6b5a;
        }

        .received-detail {
            font-size: 12px;
            color: #555;
            margin-top: 4px;
            line-height: 1.6;
        }

        /* ── Amount box ── */
        .doc-amount {
            margin: 28px 32px;
            background: #f5faf8;
            border: 2px solid #1b6b5a;
            border-radius: 6px;
            padding: 28px 32px;
            text-align: center;
        }

        .amount-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #1b6b5a;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .amount-value {
            font-size: 44px;
            font-weight: 700;
            color: #1b6b5a;
            letter-spacing: -1px;
        }

        .amount-currency {
            font-size: 20px;
            font-weight: 400;
            margin-right: 6px;
            opacity: 0.7;
        }

        /* ── Notes ── */
        .doc-notes {
            margin: 0 32px 24px;
            padding: 14px 16px;
            background: #fffdf5;
            border-left: 3px solid #e0a020;
            border-radius: 0 4px 4px 0;
        }

        .notes-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #b07010;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .notes-body {
            font-size: 12px;
            line-height: 1.6;
            color: #555;
        }

        /* ── Thank you ── */
        .doc-thankyou {
            margin: 0 32px 32px;
            text-align: center;
            color: #1b6b5a;
            font-size: 14px;
            font-style: italic;
            opacity: 0.85;
        }

        /* ── Footer ── */
        .doc-footer {
            margin-top: auto;
            background: #1b6b5a;
            color: rgba(255,255,255,0.85);
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            padding: 12px 32px;
            gap: 8px;
        }

        .footer-cell {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
        }

        .footer-icon {
            font-size: 13px;
            opacity: 0.7;
        }

        @media print {
            body { background: #fff; }
            .print-btn-wrap { display: none; }
            .document {
                width: 100%;
                margin: 0;
                box-shadow: none;
                min-height: unset;
            }
            @page {
                size: A4;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="print-btn-wrap">
    <button class="print-btn" onclick="window.print()">&#128438; Print / Save PDF</button>
</div>

<div class="document">

    {{-- Header --}}
    <div class="doc-header">
        <div class="header-left">
            <img src="{{ asset('images/rtl-logo.png') }}" alt="Rilip Traders Limited" class="header-logo">
            <div class="header-company">
                <p><strong>Rilip Traders Limited</strong></p>
                <p>P.O Box 31406-00600</p>
                <p>Nairobi, Kenya</p>
                <p>+254768858398 &nbsp;|&nbsp; +254713622269</p>
                <p>info@riliptraders.co.ke</p>
            </div>
        </div>
        <div class="header-right">
            <div class="doc-type">RECEIPT</div>
            <div class="doc-subtype">Payment Receipt</div>
        </div>
    </div>

    {{-- Meta bar --}}
    <div class="doc-meta">
        <div class="meta-cell">
            <div class="meta-label">Receipt No.</div>
            <div class="meta-value">{{ $payment->code }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Date</div>
            <div class="meta-value">{{ $payment->date->format('d M Y') }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Payment Mode</div>
            <div class="meta-value">{{ $payment->paymentMode?->name ?? '—' }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Currency</div>
            <div class="meta-value">{{ $payment->currency?->code ?? 'KSH' }}</div>
        </div>
    </div>

    {{-- Received from --}}
    <div class="doc-received">
        <div class="received-label">Received From</div>
        <div class="received-name">{{ $payment->customer?->name ?? '—' }}</div>
        @if($payment->customer?->phone || $payment->customer?->email)
        <div class="received-detail">
            @if($payment->customer->phone){{ $payment->customer->phone }}@endif
            @if($payment->customer->phone && $payment->customer->email) &nbsp;|&nbsp; @endif
            @if($payment->customer->email){{ $payment->customer->email }}@endif
        </div>
        @endif
    </div>

    {{-- Amount --}}
    @php $currency = $payment->currency?->code ?? 'KSh'; @endphp
    <div class="doc-amount">
        <div class="amount-label">Amount Received</div>
        <div class="amount-value">
            <span class="amount-currency">{{ $currency }}</span>{{ number_format($payment->amount, 2) }}
        </div>
    </div>

    {{-- Notes --}}
    @if($payment->notes)
    <div class="doc-notes">
        <div class="notes-label">Notes</div>
        <div class="notes-body">{{ $payment->notes }}</div>
    </div>
    @endif

    {{-- Thank you --}}
    <div class="doc-thankyou">Thank you for your payment.</div>

    {{-- Footer --}}
    <div class="doc-footer">
        <div class="footer-cell">
            <span class="footer-icon">&#9990;</span>
            +254768858398
        </div>
        <div class="footer-cell">
            <span class="footer-icon">&#9990;</span>
            +254713622269
        </div>
        <div class="footer-cell">
            <span class="footer-icon">&#9993;</span>
            info@riliptraders.co.ke
        </div>
        <div class="footer-cell">
            <span class="footer-icon">&#127760;</span>
            www.riliptraders.co.ke
        </div>
    </div>

</div>

</body>
</html>
