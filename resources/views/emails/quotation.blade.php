<p>Dear {{ $quotation->customer?->name ?? 'Valued Customer' }},</p>
<p>Thank you for your enquiry. Please find your quotation <strong>{{ $quotation->reference }}</strong>
    totalling <strong>{{ $quotation->currency }} {{ number_format((float) $quotation->total, 2) }}</strong>.</p>
<p><a href="{{ $publicUrl }}">View & accept your quotation</a></p>
<p>Valid until {{ $quotation->valid_until?->format('M d, Y') }}.<br>{{ config('noorhan.name') }}</p>