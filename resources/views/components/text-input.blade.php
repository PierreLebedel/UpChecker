@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['autocomplete'=>'off', 'class' => 'input input-bordered input-md w-full']) !!}>
