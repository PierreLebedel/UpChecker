<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Endpoint;
use Livewire\Attributes\On;

class EndpointCheckupsHistory extends Component
{
    public $project;
    public $endpoint;
    public $checkups;

    public function mount(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->project = $this->endpoint->project;
        $this->reloadCheckups();
    }

    #[On('echo-private:user.{project.user_id},CheckupCreatedEvent')]
    public function reloadCheckups()
    {
        $this->checkups = $this->endpoint->checkups()
            ->orderByDesc("started_at")
            ->limit(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.endpoint-checkups-history');
    }
}
