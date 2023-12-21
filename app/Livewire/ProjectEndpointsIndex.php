<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\On;

class ProjectEndpointsIndex extends Component
{

    public $project;
    public $endpoints;

    public function mount(Project $project)
    {
        $this->project = $project;

        $this->reloadEndpoints();
    }

    #[On('project-{project.id}.checkup-created')] 
    //#[On('project-{project.id}.endpoint-deleted')] 
    public function reloadEndpoints()
    {
        $this->endpoints = $this->project->endpoints()->with('lastCheckup')->get();
    }

    public function render()
    {
        return view('livewire.project-endpoints-index');
    }
}
