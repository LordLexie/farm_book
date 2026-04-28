<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $quotation->code }} – Rilip Traders Limited</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
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
            font-size: 14px;
            line-height: 1.6;
            opacity: 0.88;
        }

        .header-right {
            text-align: right;
        }

        .header-right .doc-type {
            font-size: 48px;
            font-weight: 700;
            letter-spacing: 1px;
            line-height: 1.1;
        }

        .header-right .doc-subtype {
            font-size: 15px;
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
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
            margin-bottom: 3px;
        }

        .meta-value {
            font-size: 15px;
            font-weight: 600;
        }

        /* ── Address block ── */
        .doc-addresses {
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding: 24px 32px;
            gap: 32px;
            border-bottom: 1px solid #e0e0e0;
        }

        .address-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1b6b5a;
            border-bottom: 2px solid #1b6b5a;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }

        .address-name {
            font-size: 18px;
            font-weight: 700;
            color: #1b6b5a;
            margin-bottom: 6px;
        }

        .address-body {
            font-size: 14px;
            line-height: 1.7;
            color: #444;
        }

        /* ── Items table ── */
        .doc-table-wrap {
            padding: 0 32px;
            margin-top: 8px;
        }

        .doc-table {
            width: 100%;
            border-collapse: collapse;
        }

        .doc-table thead tr {
            background: #1b6b5a;
            color: #fff;
        }

        .doc-table thead th {
            padding: 10px 12px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
            text-align: left;
        }

        .doc-table thead th.text-right { text-align: right; }

        .doc-table tbody tr:nth-child(even) {
            background: #f7faf9;
        }

        .doc-table tbody td {
            padding: 10px 12px;
            vertical-align: top;
            border-bottom: 1px solid #eee;
        }

        .doc-table tbody td.text-right { text-align: right; }

        .item-name {
            font-weight: 600;
            color: #1a1a1a;
        }

        .item-desc {
            font-size: 13px;
            color: #777;
            margin-top: 2px;
        }

        /* ── Totals ── */
        .doc-totals {
            padding: 16px 32px 8px;
            display: flex;
            justify-content: flex-end;
        }

        .totals-table {
            width: 280px;
        }

        .totals-table td {
            padding: 5px 8px;
            font-size: 14px;
        }

        .totals-table td.label {
            color: #666;
            text-align: right;
        }

        .totals-table td.amount {
            text-align: right;
            font-weight: 600;
        }

        .totals-table tr.total-row td {
            background: #1b6b5a;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            padding: 10px 12px;
        }

        /* ── Notes ── */
        .doc-notes {
            margin: 8px 32px 24px;
            padding: 14px 16px;
            background: #f5faf8;
            border-left: 3px solid #1b6b5a;
            border-radius: 0 4px 4px 0;
        }

        .notes-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1b6b5a;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .notes-body {
            font-size: 14px;
            line-height: 1.6;
            color: #444;
            white-space: pre-line;
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
            <div class="doc-type">QUOTATION</div>
            <div class="doc-subtype">Tax Quotation</div>
        </div>
    </div>

    {{-- Meta bar --}}
    <div class="doc-meta">
        <div class="meta-cell">
            <div class="meta-label">Quotation No.</div>
            <div class="meta-value">{{ $quotation->code }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Quotation Date</div>
            <div class="meta-value">{{ $quotation->date->format('d M Y') }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Valid Until</div>
            <div class="meta-value">{{ $quotation->valid_until ? $quotation->valid_until->format('d M Y') : '—' }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Prepared By</div>
            <div class="meta-value">{{ $quotation->creator?->name ?? '—' }}</div>
        </div>
    </div>

    {{-- Address block --}}
    <div class="doc-addresses">
        <div>
            <div class="address-label">From</div>
            <div class="address-name">Rilip Traders Limited</div>
            <div class="address-body">
                P.O Box 31406-00600<br>
                Nairobi, Kenya<br>
                +254768858398 | +254713622269<br>
                info@riliptraders.co.ke
            </div>
        </div>
        <div>
            <div class="address-label">Prepared For</div>
            <div class="address-name">{{ $quotation->customer->name }}</div>
            <div class="address-body">
                @if($quotation->customer->address)
                    {{ $quotation->customer->address }}<br>
                @endif
                @if($quotation->customer->email)
                    {{ $quotation->customer->email }}<br>
                @endif
                @if($quotation->customer->phone)
                    {{ $quotation->customer->phone }}
                @endif
            </div>
        </div>
    </div>

    {{-- Items table --}}
    <div class="doc-table-wrap">
        <table class="doc-table">
            <thead>
                <tr>
                    <th style="width:40%">Description</th>
                    <th class="text-right" style="width:10%">Qty</th>
                    <th class="text-right" style="width:10%">Unit</th>
                    <th class="text-right" style="width:18%">Unit Price (KSH)</th>
                    <th class="text-right" style="width:22%">Amount (KSH)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->name }}</div>
                        @if($item->description)
                            <div class="item-desc">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">{{ $item->unitOfMeasure?->code ?? '—' }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="doc-totals">
        <table class="totals-table">
            <tr class="total-row">
                <td class="label" style="color:rgba(255,255,255,0.8)">TOTAL DUE</td>
                <td class="amount">KSh {{ number_format($quotation->total, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- Notes --}}
    @if($quotation->notes)
    <div class="doc-notes">
        <div class="notes-label">Notes &amp; Terms</div>
        <div class="notes-body">{{ $quotation->notes }}</div>
    </div>
    @endif

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
