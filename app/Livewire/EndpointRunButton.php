<?php

namespace App\Livewire;

use App\Jobs\EndpointCheckJob;
use App\Models\Endpoint;
use Livewire\Component;

class EndpointRunButton extends Component
{
    public $endpoint;

    public $isRunning = false;

    public function mount(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function render()
    {
        return view('livewire.endpoint-run-button');
    }

    public function runNow()
    {
        $this->isRunning = true;

        dispatch_sync(new EndpointCheckJob($this->endpoint));

        $this->isRunning = false;
    }
}
