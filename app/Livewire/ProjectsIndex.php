<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Attributes\On;
use Livewire\Component;

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

    #[On('echo-private:user.{user.id},ProjectCreatedEvent')]
    #[On('echo-private:user.{user.id},ProjectUpdatedEvent')]
    #[On('echo-private:user.{user.id},ProjectDeletedEvent')]
    #[On('echo-private:user.{user.id},EndpointCreatedEvent')]
    #[On('echo-private:user.{user.id},EndpointDeletedEvent')]
    public function reloadProjects()
    {
        $this->projects = $this->user->projects()->withCount('endpoints')->get();
    }

    public function render()
    {
        return view('livewire.projects-index');
    }
}
