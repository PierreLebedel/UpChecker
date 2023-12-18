@props(['active'])

@php
$classes = ($active ?? false)
            ? 'btn btn-sm btn-primary btn-active text-white'
            : 'btn btn-sm btn-ghost';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
