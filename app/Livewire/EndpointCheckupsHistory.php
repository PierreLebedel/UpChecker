<?php

namespace App\Livewire;

use App\Models\Checkup;
use Livewire\Component;
use App\Models\Endpoint;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class EndpointCheckupsHistory extends Component
{
    use WithPagination;

    public $project;
    public $endpoint;

    public function mount(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->project = $this->endpoint->project;
        $this->reloadCheckups();
    }

    #[On('echo-private:user.{project.user_id},CheckupCreatedEvent')]
    public function reloadCheckups()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.endpoint-checkups-history', [
            'checkups' => $this->endpoint->checkups()
                ->orderByDesc("started_at")
                ->paginate(5, pageName: 'page'),
        ]);
    }
}
