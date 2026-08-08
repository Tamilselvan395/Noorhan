<p>Dear {{ $invoice->customer?->name ?? 'Valued Customer' }},</p>
<p>Please find attached your invoice <strong>{{ $invoice->reference }}</strong>
    for <strong>{{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</strong>.</p>
<p>Balance due: <strong>{{ $invoice->currency }} {{ number_format((float) $invoice->balance_due, 2) }}</strong> by {{ $invoice->due_date?->format('M d, Y') }}.</p>
<p><a href="{{ $publicUrl }}">View Invoice & Payment Details</a></p>
<p>Thank you for your business.<br>{{ config('noorhan.name') }}</p>