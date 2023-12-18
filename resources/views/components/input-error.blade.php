@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'label-text-alt text-error mt-2']) }}>
        @foreach ((array) $messages as $message)
            {{ $message }}<br>
        @endforeach
    </div>
@endif
