<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;
use App\Models\Endpoint;
use App\Events\ProjectDeletedEvent;
use App\Events\EndpointDeletedEvent;

class ProjectDeleteLink extends Component
{
    public $project;

    public function mount(Project $project)
    {
        $this->project = $project;
    }

    public function render()
    {
        return view('livewire.project-delete-link');
    }

    public function confirmDeleteProject()
    {
        $this->authorize('delete', $this->project);

        $user = $this->project->user;

        $this->project->delete();

        ProjectDeletedEvent::dispatch($user);

        session()->flash('status', __('Your project was successfully deleted.'));

        $this->redirectRoute('project.index');
    }
}
