<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pro Forma <?php echo e($proFormaInvoice->code); ?> – Rilip Traders Limited</title>
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
            font-size: 38px;
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
            font-size: 14px;
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

        .totals-table tr.total-row td {
            background: #1b6b5a;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            padding: 10px 12px;
        }

        .doc-notice {
            margin: 8px 32px 0;
            padding: 14px 16px;
            background: #fff8f0;
            border-left: 3px solid #e0a020;
            border-radius: 0 4px 4px 0;
        }

        .notice-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #b07010;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .notice-body {
            font-size: 14px;
            line-height: 1.6;
            color: #555;
        }

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

    
    <div class="doc-header">
        <div class="header-left">
            <img src="<?php echo e(asset('images/rtl-logo.png')); ?>" alt="Rilip Traders Limited" class="header-logo">
            <div class="header-company">
                <p><strong>Rilip Traders Limited</strong></p>
                <p>P.O Box 31406-00600</p>
                <p>Nairobi, Kenya</p>
                <p>+254768858398 &nbsp;|&nbsp; +254713622269</p>
                <p>info@riliptraders.co.ke</p>
            </div>
        </div>
        <div class="header-right">
            <div class="doc-type">PRO FORMA</div>
            <div class="doc-subtype">Invoice</div>
        </div>
    </div>

    
    <div class="doc-meta">
        <div class="meta-cell">
            <div class="meta-label">Pro Forma No.</div>
            <div class="meta-value"><?php echo e($proFormaInvoice->code); ?></div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Date</div>
            <div class="meta-value"><?php echo e($proFormaInvoice->date->format('d M Y')); ?></div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Currency</div>
            <div class="meta-value"><?php echo e($proFormaInvoice->currency?->code ?? 'KSH'); ?></div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Status</div>
            <div class="meta-value"><?php echo e($proFormaInvoice->status?->name ?? '—'); ?></div>
        </div>
    </div>

    
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
            <div class="address-name"><?php echo e($proFormaInvoice->customer->name); ?></div>
            <div class="address-body">
                <?php if($proFormaInvoice->customer->address): ?>
                    <?php echo e($proFormaInvoice->customer->address); ?><br>
                <?php endif; ?>
                <?php if($proFormaInvoice->customer->email): ?>
                    <?php echo e($proFormaInvoice->customer->email); ?><br>
                <?php endif; ?>
                <?php if($proFormaInvoice->customer->phone): ?>
                    <?php echo e($proFormaInvoice->customer->phone); ?>

                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="doc-table-wrap">
        <table class="doc-table">
            <thead>
                <tr>
                    <th style="width:42%">Description</th>
                    <th class="text-right" style="width:10%">Qty</th>
                    <th style="width:10%">Unit</th>
                    <th class="text-right" style="width:18%">Unit Price (<?php echo e($proFormaInvoice->currency?->code ?? 'KSH'); ?>)</th>
                    <th class="text-right" style="width:20%">Amount (<?php echo e($proFormaInvoice->currency?->code ?? 'KSH'); ?>)</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $proFormaInvoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $name = $item->invoiceable?->name
                        ?? $item->invoiceable?->description
                        ?? class_basename($item->invoiceable_type ?? 'Item');
                ?>
                <tr>
                    <td>
                        <div class="item-name"><?php echo e($name); ?></div>
                        <?php if($item->invoiceable && isset($item->invoiceable->description) && $item->invoiceable->description !== $name): ?>
                            <div class="item-desc"><?php echo e($item->invoiceable->description); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-right"><?php echo e(number_format($item->quantity, 2)); ?></td>
                    <td><?php echo e($item->unitOfMeasure?->code ?? '—'); ?></td>
                    <td class="text-right"><?php echo e(number_format($item->unit_price, 2)); ?></td>
                    <td class="text-right"><?php echo e(number_format($item->total, 2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    
    <?php
        $currency      = $proFormaInvoice->currency?->code ?? 'KSh';
        $subtotal      = $proFormaInvoice->items->sum('total');
        $discount      = (float) $proFormaInvoice->discount;
        $afterDiscount = round($subtotal * (1 - $discount / 100), 2);
        $taxRate       = $proFormaInvoice->tax ? (float) $proFormaInvoice->tax->value : 0;
        $taxAmount     = round($afterDiscount * $taxRate / 100, 2);
    ?>
    <div class="doc-totals">
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal</td>
                <td class="amount"><?php echo e($currency); ?> <?php echo e(number_format($subtotal, 2)); ?></td>
            </tr>
            <?php if($discount > 0): ?>
            <tr>
                <td class="label">Discount (<?php echo e($discount); ?>%)</td>
                <td class="amount">– <?php echo e($currency); ?> <?php echo e(number_format($subtotal * $discount / 100, 2)); ?></td>
            </tr>
            <?php endif; ?>
            <?php if($taxAmount > 0): ?>
            <tr>
                <td class="label"><?php echo e($proFormaInvoice->tax->name); ?> (<?php echo e($proFormaInvoice->tax->value); ?>%)</td>
                <td class="amount">+ <?php echo e($currency); ?> <?php echo e(number_format($taxAmount, 2)); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td class="label" style="color:rgba(255,255,255,0.8)">Total</td>
                <td class="amount"><?php echo e($currency); ?> <?php echo e(number_format($proFormaInvoice->total, 2)); ?></td>
            </tr>
        </table>
    </div>

    
    <div class="doc-notice">
        <div class="notice-label">Important Notice</div>
        <div class="notice-body">
            This is a pro forma invoice and is not a demand for payment. Goods and services will be delivered upon confirmation of order.
            This document is valid for 30 days from the date of issue.
        </div>
    </div>

    
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
<?php /**PATH /opt/homebrew/var/www/farm_app/resources/views/pro-forma-invoices/print.blade.php ENDPATH**/ ?>