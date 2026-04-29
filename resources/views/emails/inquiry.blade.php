<h2>New Parish Inquiry</h2>
<p><strong>From:</strong> {{ $inquiry['name'] }} ({{ $inquiry['email'] }})</p>
@if(isset($inquiry['phone']))<p><strong>Phone:</strong> {{ $inquiry['phone'] }}</p>@endif
<p><strong>Subject:</strong> {{ $inquiry['subject'] }}</p>
<hr>
<p>{{ nl2br(e($inquiry['message'])) }}</p>
<hr>
<p><small>Sent from the MHC Parish website contact form.</small></p>
