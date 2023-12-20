<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Endpoint;
use Livewire\Attributes\On;

class EndpointCheckupsHistory extends Component
{

    public $endpoint;
    public $checkups;

    public function mount(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->reloadCheckups();
    }

    #[On('endpoint-{endpoint.id}.checkup-created')] 
    public function reloadCheckups()
    {
        $this->checkups = $this->endpoint->checkups()->orderByDesc("started_at")->limit(5)->get();
    }

    public function render()
    {
        return view('livewire.endpoint-checkups-history');
    }
}
