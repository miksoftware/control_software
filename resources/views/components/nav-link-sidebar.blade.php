@props(['active', 'href'])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-4 py-3 text-sm font-medium rounded-lg bg-white/10 text-white'
            : 'flex items-center px-4 py-3 text-sm font-medium rounded-lg text-white/70 hover:bg-white/5 hover:text-white transition-all duration-200';
@endphp

<a {{ $attributes->merge(['class' => $classes, 'href' => $href]) }}>
    {{ $slot }}
</a>