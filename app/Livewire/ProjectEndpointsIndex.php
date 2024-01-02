<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Attributes\On;
use Livewire\Component;

class ProjectEndpointsIndex extends Component
{
    public $project;

    public $endpoints;

    public function mount(Project $project)
    {
        $this->project = $project;

        $this->reloadEndpoints();
    }

    #[On('echo-private:user.{project.user_id},EndpointCreatedEvent')]
    #[On('echo-private:user.{project.user_id},EndpointUpdatedEvent')]
    #[On('echo-private:user.{project.user_id},EndpointDeletedEvent')]
    #[On('echo-private:user.{project.user_id},CheckupCreatedEvent')]
    public function reloadEndpoints()
    {
        $this->endpoints = $this->project->endpoints()->with('lastCheckup')->get();
    }

    public function render()
    {
        return view('livewire.project-endpoints-index');
    }
}
