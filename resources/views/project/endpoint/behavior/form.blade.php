<div>

    <div class="mb-4">
        <h3 class="text-xl mb-3">{{ __('Rules') }}</h3>
        @livewire('behavior-rules-form', ['behavior'=>$behavior])
    </div>

    <div class="mb-4">
        <h3 class="text-xl mb-3">{{ __('Actions') }}</h3>
        @livewire('behavior-actions-form', ['behavior'=>$behavior])
    </div>

</div>