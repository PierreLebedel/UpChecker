@props(['value'])

<label {{ $attributes->merge(['class' => 'label']) }}>
    <div class="label-text">{{ $value ?? $slot }}</div>
</label>