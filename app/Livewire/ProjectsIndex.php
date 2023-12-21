<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\On;

class ProjectsIndex extends Component
{

    public $user;
    public $projects;

    public function mount()
    {
        $this->authorize('viewAny', Project::class);

        $this->user = auth()->user();

        $this->reloadProjects();
    }

    //#[On('user-{user.id}.endpoint-deleted')] 
    public function reloadProjects()
    {
        $this->projects = $this->user->projects()->withCount('endpoints')->get();
    }

    public function render()
    {
        return view('livewire.projects-index');
    }
}
