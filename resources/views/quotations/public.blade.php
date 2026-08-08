<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->reference }}</title>
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
        .muted { color: #6b7280; font-size: 12px; }
        .actions { margin-top: 32px; }
        button { background: #16a34a; color: #fff; border: 0; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        @media print { .actions, .flash { display: none !important; } }
    </style>
</head>
<body>
    <div class="head">
        <div>
            <div class="brand">NOORHAN GROUP</div>
            <div class="muted">{{ config('noorhan.name') }}</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:18px;font-weight:700">QUOTATION</div>
            <div class="muted">{{ $quotation->reference }} · v{{ $quotation->version }}</div>
            <div class="muted">Valid until {{ $quotation->valid_until?->format('M d, Y') }}</div>
        </div>
    </div>

    <div>
        <strong>Prepared for:</strong><br>
        {{ $quotation->customer?->displayName() ?? $quotation->lead?->name ?? '—' }}<br>
        <span class="muted">{{ $quotation->customer?->email ?? '' }} {{ $quotation->customer?->phone ?? '' }}</span>
    </div>

    <table>
        <thead><tr><th>#</th><th>Description</th><th style="text-align:right">Qty</th><th style="text-align:right">Unit Price</th><th style="text-align:right">Total</th></tr></thead>
        <tbody>
            @foreach ($quotation->items as $i => $item)
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
        <div><span>Subtotal</span><span>{{ number_format((float) $quotation->subtotal, 2) }}</span></div>
        <div><span>Discount</span><span>-{{ number_format((float) $quotation->discount_amount, 2) }}</span></div>
        <div><span>Tax ({{ $quotation->tax_rate }}%)</span><span>{{ number_format((float) $quotation->tax_amount, 2) }}</span></div>
        <div class="grand"><span>Total ({{ $quotation->currency }})</span><span>{{ number_format((float) $quotation->total, 2) }}</span></div>
    </div>

    @if ($quotation->notes) <p class="muted" style="margin-top:24px">{{ $quotation->notes }}</p> @endif
    @if ($quotation->terms) <p class="muted"><strong>Terms:</strong> {{ $quotation->terms }}</p> @endif

    <div class="actions">
        @if (session('public_status')) <p class="flash" style="color:#16a34a;font-weight:600">{{ session('public_status') }}</p> @endif
        @if (session('public_error')) <p class="flash" style="color:#dc2626;font-weight:600">{{ session('public_error') }}</p> @endif

        @if ($quotation->status->value === 'sent' && ! $quotation->isExpired())
            <form method="POST" action="{{ route('quotations.public.accept', $quotation) }}">
                @csrf
                <button type="submit">Accept Quotation</button>
            </form>
        @elseif ($quotation->status->value === 'accepted')
            <p style="color:#16a34a;font-weight:700">✓ Accepted on {{ $quotation->accepted_at?->format('M d, Y') }}</p>
        @endif
        <p class="muted" style="margin-top:12px">Use your browser's Print → Save as PDF to download this quotation.</p>
    </div>
</body>
</html>