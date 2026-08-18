<tr>
<td class="header">
<a href="{{ $url ?? config('app.url') }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<!-- On remplace le logo Laravel par notre image DARASI -->
<img src="{{ asset('logo.png') }}" class="logo" alt="DARASI HUB Logo" style="max-height: 60px; width: auto;">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>