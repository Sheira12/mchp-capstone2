{{--
    x-safe-image — a drop-in <img> wrapper that:
      1. Resolves the src through media_url() so it always points to Supabase
      2. Shows a graceful placeholder on onerror (never a broken-image icon)
      3. Accepts standard img attributes via $attributes

    Usage:
      <x-safe-image :src="$announcement->image_path" :alt="$announcement->title" class="w-full h-44 object-cover" />
      <x-safe-image :src="$item->image_path" alt="Gallery photo" loading="lazy" />

    Props:
      $src        — relative path stored in DB, or null/empty
      $alt        — alt text (defaults to '')
      $fallback   — optional override placeholder URL; defaults to parish logo
--}}
@props([
    'src'      => null,
    'alt'      => '',
    'fallback' => null,
])

@php
    $resolvedSrc = $src ? media_url($src) : '';
    $fallbackSrc = $fallback
        ?? asset('images/parish-logo.png');  // static file in public/ — survives redeploys
@endphp

@if($resolvedSrc)
<img
    src="{{ $resolvedSrc }}"
    alt="{{ $alt }}"
    onerror="this.onerror=null;this.src='{{ $fallbackSrc }}';"
    {{ $attributes }}
>
@else
{{-- No path at all — show placeholder immediately, no failed HTTP request --}}
<img
    src="{{ $fallbackSrc }}"
    alt="{{ $alt }}"
    {{ $attributes }}
>
@endif
