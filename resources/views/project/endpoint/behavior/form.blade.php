<div>

    <div class="mb-4">
        <h3 class="text-xl mb-1">{{ __('Rules') }}</h3>
        <p class="opacity-50 mb-3">{{ __("Note that every rule must be respected to perform the actions") }}</p>
        @livewire('behavior-rules-form', ['behavior'=>$behavior])
    </div>

    <div class="mb-4">
        <h3 class="text-xl mb-3">{{ __('Actions') }}</h3>
        @livewire('behavior-actions-form', ['behavior'=>$behavior])
    </div>

</div>