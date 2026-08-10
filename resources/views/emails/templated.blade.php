{{-- resources/views/emails/templated.blade.php --}}
<p>{!! nl2br(e($bodyText)) !!}</p>
<br>
<p style="color:#6b7280;font-size:12px">{{ config('noorhan.name') }}</p>
@if ($unsubscribeUrl)
    <p style="color:#9ca3af;font-size:11px"><a href="{{ $unsubscribeUrl }}">Unsubscribe from marketing emails</a></p>
@endif