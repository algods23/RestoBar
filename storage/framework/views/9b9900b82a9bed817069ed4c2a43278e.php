

<?php $__env->startSection('content'); ?>
<style>
    /* ── SCREEN VIEW (readable) ── */
    body {
        background: #f0f0f0;
    }

    .receipt {
        width: 100%;
        max-width: 400px;
        background: #fff;
        padding: 24px;
        margin: 0 auto;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        font-size: 13px;
        line-height: 1.6;
    }

    .receipt-header {
        text-align: center;
        margin-bottom: 12px;
    }

    .receipt-header h2 {
        font-size: 18px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .receipt-header .date {
        font-size: 11px;
        color: #888;
        margin-top: 2px;
    }

    .receipt-meta {
        font-size: 12px;
        margin-bottom: 10px;
        color: #444;
    }

    hr.dashed {
        border: none;
        border-top: 1px dashed #ccc;
        margin: 10px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    thead tr {
        border-bottom: 1px dashed #ccc;
    }

    th {
        padding: 4px 2px;
        text-align: left;
        font-size: 11px;
        color: #888;
        font-weight: normal;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    td {
        padding: 5px 2px;
        vertical-align: top;
    }

    td:nth-child(2),
    th:nth-child(2) { text-align: center; }

    td:nth-child(3),
    th:nth-child(3),
    td:nth-child(4),
    th:nth-child(4) { text-align: right; }

    .summary {
        width: 100%;
        font-size: 12px;
        margin-top: 4px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 3px 0;
        color: #444;
    }

    .summary-row.total {
        font-size: 15px;
        font-weight: bold;
        color: #000;
        margin-top: 4px;
    }

    .receipt-footer {
        text-align: center;
        margin-top: 16px;
        font-size: 11px;
        color: #888;
    }

    .print-btn {
        display: block;
        width: 100%;
        padding: 10px;
        margin: 14px 0 0;
        font-size: 13px;
        background: #111;
        color: #fff;
        border: none;
        cursor: pointer;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }

    .print-btn:hover {
        background: #333;
    }

    /* ── PRINT VIEW (clean 58mm) ── */
    @media print {
        body {
            background: #fff;
        }

        .receipt {
            width: 58mm !important;
            max-width: 58mm !important;
            padding: 2mm !important;
            margin: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            font-size: 10px !important;
            line-height: 1.4 !important;
        }

        .receipt-header h2 {
            font-size: 12px !important;
        }

        .receipt-header .date {
            font-size: 9px !important;
        }

        .receipt-meta {
            font-size: 9px !important;
        }

        table, th, td {
            font-size: 9px !important;
        }

        th {
            font-size: 8px !important;
        }

        td { padding: 2px 1px !important; }

        .summary { font-size: 9px !important; }

        .summary-row.total {
            font-size: 11px !important;
        }

        .receipt-footer {
            font-size: 8px !important;
            margin-top: 6px !important;
        }

        .no-print {
            display: none !important;
        }

        hr.dashed {
            border-top: 1px dashed #000 !important;
            margin: 4px 0 !important;
        }
    }
</style>

<div class="no-print">
    <button class="print-btn" style="background:#444; margin-top:8px;" onclick="history.back()">← Back</button>
</div>
<div class="receipt">

    
    <div class="receipt-header">
        <h2>RestoBar POS</h2>
        <div class="date">
            Receipt #<?php echo e($order->id); ?> &nbsp;|&nbsp;
            <?php echo e($order->created_at->format('M d, Y h:i A')); ?>

        </div>
    </div>

  

    <hr class="dashed">

    
    <div class="receipt-meta">
        <div>Cashier: <strong><?php echo e($order->user?->name ?? 'N/A'); ?></strong></div>
        <div>Type: <strong><?php echo e(str_replace('_', ' ', ucfirst($order->order_type))); ?></strong></div>
    </div>

    <hr class="dashed">

    
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>₱</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->product?->name ?? 'N/A'); ?></td>
                    <td style="text-align:center;"><?php echo e($item->quantity); ?></td>
                    <td style="text-align:right;"><?php echo e(number_format($item->price, 2)); ?></td>
                    <td style="text-align:right;"><?php echo e(number_format($item->subtotal, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <hr class="dashed">

    
    <div class="summary">
        <div class="summary-row">
            <span>Subtotal</span>
            <span>₱<?php echo e(number_format($order->subtotal, 2)); ?></span>
        </div>
        <div class="summary-row">
            <span>Discount</span>
            <span>₱<?php echo e(number_format($order->discount_amount, 2)); ?></span>
        </div>
        <div class="summary-row">
            <span>VAT (12%)</span>
            <span>₱<?php echo e(number_format($order->vat_amount, 2)); ?></span>
        </div>
        <hr class="dashed">
        <div class="summary-row total">
            <span>TOTAL</span>
            <span>₱<?php echo e(number_format($order->total_amount, 2)); ?></span>
        </div>
    </div>

    
    <div class="receipt-footer">
        <div>Thank you for your purchase!</div>
        <div style="margin-top:4px;">Powered by RestoBar POS</div>
    </div>

</div>
  
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    window.addEventListener('load', () => {
        setTimeout(() => window.print(), 500);
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.print', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\RestoBar\resources\views/pos/receipt.blade.php ENDPATH**/ ?>