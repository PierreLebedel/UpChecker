<?php

namespace App\Livewire;

use App\Models\Endpoint;
use Livewire\Component;

class EndpointDeleteButton extends Component
{

    public $endpoint;

    public function mount(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function render()
    {
        return view('livewire.endpoint-delete-button');
    }
    
    public function confirmDelete()
    {
        $this->authorize('delete', $this->endpoint);

        $project = $this->endpoint->project;

        $this->endpoint->delete();

        $this->dispatch('project-'.$project->id.'.endpoint-deleted'); 
        $this->dispatch('user-'.$project->user_id.'.endpoint-deleted'); 

        $this->redirectRoute('project.show', $project);
    }

}
