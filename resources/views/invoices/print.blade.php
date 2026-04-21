<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->code }} – Rilip Traders Limited</title>
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
            grid-template-columns: repeat(5, 1fr);
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

        /* ── Address block ── */
        .doc-addresses {
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding: 24px 32px;
            gap: 32px;
            border-bottom: 1px solid #e0e0e0;
        }

        .address-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1b6b5a;
            border-bottom: 2px solid #1b6b5a;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }

        .address-name {
            font-size: 15px;
            font-weight: 700;
            color: #1b6b5a;
            margin-bottom: 6px;
        }

        .address-body {
            font-size: 12px;
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
            font-size: 10px;
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
            font-size: 11px;
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
            width: 300px;
        }

        .totals-table td {
            padding: 5px 8px;
            font-size: 13px;
        }

        .totals-table td.label {
            color: #666;
            text-align: right;
        }

        .totals-table td.amount {
            text-align: right;
            font-weight: 600;
            min-width: 110px;
        }

        .totals-table tr.divider td {
            border-top: 1px solid #e0e0e0;
            padding-top: 8px;
        }

        .totals-table tr.total-row td {
            background: #1b6b5a;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            padding: 10px 12px;
        }

        .totals-table tr.balance-row td {
            background: #145449;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            padding: 8px 12px;
        }

        .totals-table tr.paid-row td {
            color: #1b6b5a;
            font-size: 12px;
        }

        /* ── Payment instructions ── */
        .doc-payment {
            margin: 8px 32px 0;
            padding: 14px 16px;
            background: #f5faf8;
            border-left: 3px solid #1b6b5a;
            border-radius: 0 4px 4px 0;
        }

        .payment-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1b6b5a;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 24px;
            font-size: 12px;
            color: #444;
            line-height: 1.7;
        }

        /* ── Terms ── */
        .doc-terms {
            margin: 8px 32px 24px;
            padding: 14px 16px;
            background: #fff8f0;
            border-left: 3px solid #e0a020;
            border-radius: 0 4px 4px 0;
        }

        .terms-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #b07010;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .terms-body {
            font-size: 12px;
            line-height: 1.6;
            color: #555;
        }

        /* ── Signature ── */
        .doc-signature {
            margin: 0 32px 24px;
            display: flex;
            justify-content: flex-end;
        }

        .signature-block {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            border-top: 1px solid #999;
            padding-top: 6px;
            font-size: 11px;
            color: #666;
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
                <p>123 Commerce Street, Industrial Area</p>
                <p>Nairobi, Kenya &nbsp;|&nbsp; P.O. Box 00100</p>
                <p>+254 700 000 000</p>
                <p>info@riliptraders.co.ke</p>
            </div>
        </div>
        <div class="header-right">
            <div class="doc-type">INVOICE</div>
            <div class="doc-subtype">Tax Invoice</div>
        </div>
    </div>

    {{-- Meta bar --}}
    <div class="doc-meta">
        <div class="meta-cell">
            <div class="meta-label">Invoice No.</div>
            <div class="meta-value">{{ $invoice->code }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Invoice Date</div>
            <div class="meta-value">{{ $invoice->date->format('d M Y') }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Due Date</div>
            <div class="meta-value">{{ $invoice->date->addDays(14)->format('d M Y') }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Currency</div>
            <div class="meta-value">{{ $invoice->currency?->code ?? 'KSH' }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">VAT Reg. No.</div>
            <div class="meta-value">P051234567W</div>
        </div>
    </div>

    {{-- Address block --}}
    <div class="doc-addresses">
        <div>
            <div class="address-label">From</div>
            <div class="address-name">Rilip Traders Limited</div>
            <div class="address-body">
                123 Commerce Street, Industrial Area<br>
                Nairobi, Kenya<br>
                P.O. Box 00100<br>
                +254 700 000 000<br>
                info@riliptraders.co.ke
            </div>
        </div>
        <div>
            <div class="address-label">Bill To</div>
            <div class="address-name">{{ $invoice->customer->name }}</div>
            <div class="address-body">
                @if($invoice->customer->address)
                    {{ $invoice->customer->address }}<br>
                @endif
                @if($invoice->customer->email)
                    {{ $invoice->customer->email }}<br>
                @endif
                @if($invoice->customer->phone)
                    {{ $invoice->customer->phone }}
                @endif
            </div>
        </div>
    </div>

    {{-- Items table --}}
    <div class="doc-table-wrap">
        <table class="doc-table">
            <thead>
                <tr>
                    <th style="width:42%">Description</th>
                    <th class="text-right" style="width:10%">Qty</th>
                    <th style="width:10%">Unit</th>
                    <th class="text-right" style="width:18%">Unit Price ({{ $invoice->currency?->code ?? 'KSH' }})</th>
                    <th class="text-right" style="width:20%">Amount ({{ $invoice->currency?->code ?? 'KSH' }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                @php
                    $name = $item->invoiceable?->name
                        ?? $item->invoiceable?->description
                        ?? class_basename($item->invoiceable_type ?? 'Item');
                @endphp
                <tr>
                    <td>
                        <div class="item-name">{{ $name }}</div>
                        @if($item->invoiceable && isset($item->invoiceable->description) && $item->invoiceable->description !== $name)
                            <div class="item-desc">{{ $item->invoiceable->description }}</div>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ $item->unitOfMeasure?->code ?? '—' }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    @php
        $currency = $invoice->currency?->code ?? 'KSh';
        $subtotal = $invoice->items->sum('total');
        $discount = (float) $invoice->discount;
    @endphp
    <div class="doc-totals">
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal</td>
                <td class="amount">{{ $currency }} {{ number_format($subtotal, 2) }}</td>
            </tr>
            @if($discount > 0)
            <tr>
                <td class="label">Discount ({{ $discount }}%)</td>
                <td class="amount">– {{ $currency }} {{ number_format($subtotal * $discount / 100, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="label" style="color:rgba(255,255,255,0.8)">Total Due</td>
                <td class="amount">{{ $currency }} {{ number_format($invoice->total, 2) }}</td>
            </tr>
            @if((float)$invoice->amount_paid > 0)
            <tr class="paid-row">
                <td class="label">Amount Paid</td>
                <td class="amount">– {{ $currency }} {{ number_format($invoice->amount_paid, 2) }}</td>
            </tr>
            <tr class="balance-row">
                <td class="label" style="color:rgba(255,255,255,0.8)">Balance Due</td>
                <td class="amount">{{ $currency }} {{ number_format($invoice->balance, 2) }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Payment instructions --}}
    <div class="doc-payment">
        <div class="payment-label">Payment Instructions</div>
        <div class="payment-grid">
            <div><strong>Bank:</strong> KCB Bank Kenya Limited</div>
            <div><strong>Account Name:</strong> Rilip Traders Limited</div>
            <div><strong>Account No.:</strong> 1234567890</div>
            <div><strong>Branch:</strong> Nairobi CBD</div>
            <div><strong>Swift Code:</strong> KCBLKENX</div>
        </div>
    </div>

    {{-- Terms --}}
    <div class="doc-terms">
        <div class="terms-label">Terms &amp; Conditions</div>
        <div class="terms-body">
            Payment is due within 14 days. Late payments attract a 2% monthly interest charge.
            Goods remain the property of Rilip Traders Limited until full payment is received.
        </div>
    </div>

    {{-- Signature --}}
    <div class="doc-signature">
        <div class="signature-block">
            <div style="height:40px;"></div>
            <div class="signature-line">Authorised Signature</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="doc-footer">
        <div class="footer-cell">
            <span class="footer-icon">&#9990;</span>
            +254 700 000 000
        </div>
        <div class="footer-cell">
            <span class="footer-icon">&#9993;</span>
            info@riliptraders.co.ke
        </div>
        <div class="footer-cell">
            <span class="footer-icon">&#127760;</span>
            www.riliptraders.co.ke
        </div>
        <div class="footer-cell" style="justify-content:flex-end;font-size:10px;opacity:0.7">
            VAT Reg. No. P051234567W
        </div>
    </div>

</div>

</body>
</html>
