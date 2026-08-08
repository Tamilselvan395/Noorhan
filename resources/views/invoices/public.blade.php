<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->reference }}</title>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; color: #111827; margin: 0; padding: 32px; }
        .head { display: flex; justify-content: space-between; margin-bottom: 32px; }
        .brand { font-size: 20px; font-weight: 800; color: #2563eb; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; color: #6b7280; padding: 8px; border-bottom: 2px solid #e5e7eb; }
        td { padding: 10px 8px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .totals { margin-left: auto; width: 260px; margin-top: 16px; font-size: 13px; }
        .totals div { display: flex; justify-content: space-between; padding: 4px 0; }
        .grand { font-weight: 800; font-size: 16px; border-top: 2px solid #111827; }
        .balance { font-weight: 800; font-size: 16px; color: #d97706; border-top: 1px solid #e5e7eb; }
        .muted { color: #6b7280; font-size: 12px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="head">
        <div>
            <div class="brand">NOORHAN GROUP</div>
            <div class="muted">{{ config('noorhan.name') }}</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:18px;font-weight:700">INVOICE</div>
            <div class="muted">{{ $invoice->reference }}</div>
            <div class="muted">Issued: {{ $invoice->issue_date?->format('M d, Y') }}</div>
            <div class="muted">Due: {{ $invoice->due_date?->format('M d, Y') }}</div>
        </div>
    </div>

    <div>
        <strong>Bill To:</strong><br>
        {{ $invoice->customer?->displayName() ?? '—' }}<br>
        <span class="muted">{{ $invoice->customer?->email ?? '' }} {{ $invoice->customer?->phone ?? '' }}</span>
    </div>

    <table>
        <thead><tr><th>#</th><th>Description</th><th style="text-align:right">Qty</th><th style="text-align:right">Unit Price</th><th style="text-align:right">Total</th></tr></thead>
        <tbody>
            @foreach ($invoice->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td style="text-align:right">{{ $item->quantity }}</td>
                    <td style="text-align:right">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td style="text-align:right">{{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div><span>Subtotal</span><span>{{ number_format((float) $invoice->subtotal, 2) }}</span></div>
        <div><span>Tax ({{ $invoice->tax_rate }}%)</span><span>{{ number_format((float) $invoice->tax_amount, 2) }}</span></div>
        <div class="grand"><span>Total ({{ $invoice->currency }})</span><span>{{ number_format((float) $invoice->total, 2) }}</span></div>
        <div class="balance"><span>Balance Due</span><span>{{ number_format((float) $invoice->balance_due, 2) }}</span></div>
    </div>

    @if ($invoice->terms) <p class="muted" style="margin-top:32px"><strong>Terms:</strong> {{ $invoice->terms }}</p> @endif
    
    <p class="muted no-print" style="margin-top:32px">Use your browser's Print → Save as PDF to download this invoice.</p>
</body>
</html>