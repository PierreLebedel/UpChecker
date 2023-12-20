<div>
    <button class="btn btn-sm btn-secondary whitespace-nowrap" wire:click="runNow()" wire:loading.class='opacity-50'>
        <span class="loading loading-spinner" wire:loading></span>
        <span wire:loading.remove>
            {{ __('Run now') }}
        </span>
    </button>
</div>
