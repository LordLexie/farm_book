<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Invoice <?php echo e($invoice->code); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', sans-serif;
      font-size: 14px;
      color: #1a1a2e;
      background: #f0f2f5;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 32px 16px 64px;
    }

    /* ── Screen toolbar ──────────────────────────────── */
    .toolbar {
      width: 100%;
      max-width: 860px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .toolbar-title {
      font-size: 13px;
      color: #6b7280;
    }

    .toolbar-actions {
      display: flex;
      gap: 10px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border: none;
      border-radius: 8px;
      padding: 9px 18px;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      font-family: inherit;
      transition: opacity 0.15s;
    }

    .btn:hover { opacity: 0.85; }

    .btn-print {
      background: #16a34a;
      color: #fff;
    }

    .btn-close {
      background: #fff;
      color: #374151;
      border: 1px solid #d1d5db;
    }

    /* ── Invoice sheet ───────────────────────────────── */
    .sheet {
      width: 100%;
      max-width: 860px;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
      overflow: hidden;
    }

    /* ── Header band ─────────────────────────────────── */
    .sheet-header {
      background: linear-gradient(135deg, #0f4c81 0%, #1565c0 100%);
      padding: 36px 48px 32px;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }

    .brand {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .brand-logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .brand-icon {
      width: 40px;
      height: 40px;
      background: rgba(255,255,255,0.2);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .brand-icon svg {
      width: 24px;
      height: 24px;
      fill: #fff;
    }

    .brand-name {
      font-size: 22px;
      font-weight: 700;
      color: #fff;
      letter-spacing: -0.3px;
    }

    .brand-tagline {
      font-size: 12px;
      color: rgba(255,255,255,0.65);
      margin-top: 2px;
      padding-left: 50px;
    }

    .invoice-id-block {
      text-align: right;
    }

    .invoice-label {
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: rgba(255,255,255,0.6);
      margin-bottom: 6px;
    }

    .invoice-code {
      font-size: 28px;
      font-weight: 700;
      color: #fff;
      letter-spacing: -0.5px;
    }

    .invoice-status {
      display: inline-block;
      margin-top: 8px;
      background: rgba(255,255,255,0.18);
      color: #fff;
      border-radius: 20px;
      padding: 3px 12px;
      font-size: 12px;
      font-weight: 500;
    }

    /* ── Meta row ────────────────────────────────────── */
    .meta-row {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 32px;
      padding: 32px 48px;
      border-bottom: 1px solid #f0f0f0;
    }

    .bill-to-label {
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: #9ca3af;
      margin-bottom: 8px;
    }

    .customer-name {
      font-size: 18px;
      font-weight: 600;
      color: #111827;
    }

    .meta-details {
      display: flex;
      flex-direction: column;
      gap: 8px;
      align-items: flex-end;
    }

    .meta-detail {
      display: flex;
      gap: 20px;
      align-items: baseline;
    }

    .meta-detail-label {
      font-size: 11px;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #9ca3af;
      min-width: 72px;
      text-align: right;
    }

    .meta-detail-value {
      font-size: 13px;
      font-weight: 500;
      color: #374151;
      min-width: 80px;
      text-align: right;
    }

    /* ── Items table ─────────────────────────────────── */
    .items-section {
      padding: 0 48px 0;
    }

    .items-title {
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: #9ca3af;
      padding: 24px 0 12px;
    }

    .items-table {
      width: 100%;
      border-collapse: collapse;
    }

    .items-table thead tr {
      background: #f8fafc;
      border-radius: 8px;
    }

    .items-table th {
      padding: 11px 14px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #6b7280;
      text-align: left;
      border-bottom: 1px solid #e5e7eb;
    }

    .items-table th.col-right,
    .items-table td.col-right {
      text-align: right;
    }

    .items-table th.col-center,
    .items-table td.col-center {
      text-align: center;
    }

    .items-table tbody tr {
      border-bottom: 1px solid #f3f4f6;
      transition: background 0.1s;
    }

    .items-table tbody tr:last-child {
      border-bottom: none;
    }

    .items-table tbody tr:hover {
      background: #fafbff;
    }

    .items-table td {
      padding: 13px 14px;
      font-size: 13px;
      color: #374151;
      vertical-align: middle;
    }

    .item-num {
      font-size: 11px;
      color: #9ca3af;
      font-weight: 500;
    }

    .type-badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 10px;
      font-size: 11px;
      font-weight: 500;
    }

    .type-farm-item {
      background: #eff6ff;
      color: #1d4ed8;
    }

    .type-service {
      background: #f0fdf4;
      color: #166534;
    }

    .item-name {
      font-weight: 500;
      color: #111827;
    }

    .item-unit {
      font-size: 11px;
      color: #9ca3af;
      margin-top: 2px;
    }

    .amount-cell {
      font-weight: 600;
      color: #111827;
    }

    /* ── Totals ──────────────────────────────────────── */
    .totals-section {
      padding: 20px 48px 32px;
      display: flex;
      justify-content: flex-end;
    }

    .totals-box {
      min-width: 300px;
      border-top: 2px solid #e5e7eb;
      padding-top: 16px;
    }

    .totals-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 5px 0;
    }

    .totals-label {
      font-size: 13px;
      color: #6b7280;
    }

    .totals-value {
      font-size: 13px;
      font-weight: 500;
      color: #374151;
    }

    .totals-row.discount .totals-label,
    .totals-row.discount .totals-value {
      color: #dc2626;
    }

    .totals-divider {
      border: none;
      border-top: 1px solid #e5e7eb;
      margin: 10px 0;
    }

    .totals-row.grand-total {
      margin-top: 2px;
    }

    .totals-row.grand-total .totals-label {
      font-size: 15px;
      font-weight: 700;
      color: #111827;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .totals-row.grand-total .totals-value {
      font-size: 20px;
      font-weight: 700;
      color: #0f4c81;
    }

    /* ── Footer band ─────────────────────────────────── */
    .sheet-footer {
      background: #f8fafc;
      border-top: 1px solid #e5e7eb;
      padding: 20px 48px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .footer-note {
      font-size: 12px;
      color: #6b7280;
      font-style: italic;
    }

    .footer-thanks {
      font-size: 13px;
      font-weight: 600;
      color: #0f4c81;
    }

    /* ── Print ───────────────────────────────────────── */
    @media print {
      @page {
        size: A4;
        margin: 12mm 14mm;
      }

      body {
        background: #fff;
        padding: 0;
        display: block;
      }

      .no-print { display: none !important; }

      .sheet {
        box-shadow: none;
        border-radius: 0;
        max-width: 100%;
      }

      .sheet-header {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .items-table thead tr {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .type-badge {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .items-table tbody tr:hover {
        background: transparent;
      }
    }
  </style>
</head>
<body>

  
  <div class="toolbar no-print">
    <span class="toolbar-title">Preview — <?php echo e($invoice->code); ?></span>
    <div class="toolbar-actions">
      <button class="btn btn-close" onclick="window.close()">Close</button>
      <button class="btn btn-print" onclick="window.print()">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
          <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
        </svg>
        Print
      </button>
    </div>
  </div>

  
  <div class="sheet">

    
    <div class="sheet-header">
      <div class="brand">
        <div class="brand-logo">
          <div class="brand-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M12 3L2 12h3v8h6v-5h2v5h6v-8h3L12 3zm0 12.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
            </svg>
          </div>
          <span class="brand-name">Farm App</span>
        </div>
        <span class="brand-tagline">Farm Management System</span>
      </div>

      <div class="invoice-id-block">
        <div class="invoice-label">Invoice</div>
        <div class="invoice-code"><?php echo e($invoice->code); ?></div>
        <?php if($invoice->status): ?>
          <span class="invoice-status"><?php echo e($invoice->status->name); ?></span>
        <?php endif; ?>
      </div>
    </div>

    
    <div class="meta-row">
      <div>
        <div class="bill-to-label">Bill To</div>
        <div class="customer-name"><?php echo e($invoice->customer?->name ?? '—'); ?></div>
      </div>
      <div class="meta-details">
        <div class="meta-detail">
          <span class="meta-detail-label">Date</span>
          <span class="meta-detail-value"><?php echo e(\Carbon\Carbon::parse($invoice->date)->format('d M Y')); ?></span>
        </div>
        <div class="meta-detail">
          <span class="meta-detail-label">Currency</span>
          <span class="meta-detail-value"><?php echo e($invoice->currency?->code); ?> — <?php echo e($invoice->currency?->name); ?></span>
        </div>
        <?php if($invoice->creator): ?>
          <div class="meta-detail">
            <span class="meta-detail-label">Issued by</span>
            <span class="meta-detail-value"><?php echo e($invoice->creator->name); ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    
    <div class="items-section">
      <div class="items-title">Line Items</div>
      <table class="items-table">
        <thead>
          <tr>
            <th style="width:36px">#</th>
            <th>Type</th>
            <th>Item / Description</th>
            <th class="col-right" style="width:70px">Qty</th>
            <th style="width:90px">Unit</th>
            <th class="col-right" style="width:110px">Unit Price</th>
            <th class="col-right" style="width:110px">Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
              <td><span class="item-num"><?php echo e($i + 1); ?></span></td>
              <td>
                <?php if($item->invoiceable_type === 'farm_item'): ?>
                  <span class="type-badge type-farm-item">Farm Item</span>
                <?php else: ?>
                  <span class="type-badge type-service">Service</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="item-name"><?php echo e($item->invoiceable?->name ?? '—'); ?></div>
              </td>
              <td class="col-right"><?php echo e(number_format($item->quantity, 2)); ?></td>
              <td><?php echo e($item->unitOfMeasure?->code ?? '—'); ?></td>
              <td class="col-right"><?php echo e(number_format($item->unit_price, 2)); ?></td>
              <td class="col-right amount-cell"><?php echo e(number_format($item->total, 2)); ?></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>

    
    <?php
      $subtotal = $invoice->items->sum('total');
      $discountAmount = $subtotal * ($invoice->discount / 100);
    ?>
    <div class="totals-section">
      <div class="totals-box">
        <div class="totals-row">
          <span class="totals-label">Subtotal</span>
          <span class="totals-value"><?php echo e(number_format($subtotal, 2)); ?></span>
        </div>
        <?php if($invoice->discount > 0): ?>
          <div class="totals-row discount">
            <span class="totals-label">Discount (<?php echo e($invoice->discount); ?>%)</span>
            <span class="totals-value">- <?php echo e(number_format($discountAmount, 2)); ?></span>
          </div>
        <?php endif; ?>
        <hr class="totals-divider" />
        <div class="totals-row grand-total">
          <span class="totals-label">Total</span>
          <span class="totals-value"><?php echo e($invoice->currency?->code); ?> <?php echo e(number_format($invoice->total, 2)); ?></span>
        </div>
      </div>
    </div>

    
    <div class="sheet-footer">
      <span class="footer-note">Generated on <?php echo e(now()->format('d M Y, H:i')); ?></span>
      <span class="footer-thanks">Thank you for your business!</span>
    </div>

  </div>

  <script>
    // Auto-open print dialog when the page loads
    window.addEventListener('load', () => window.print());
  </script>
</body>
</html>
<?php /**PATH /opt/homebrew/var/www/farm_app/resources/views/invoices/print.blade.php ENDPATH**/ ?>