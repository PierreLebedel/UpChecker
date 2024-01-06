<?php

namespace App\Http\Controllers;

use App\Events\ProjectCreatedEvent;
use App\Events\ProjectDeletedEvent;
use App\Events\ProjectUpdatedEvent;
use App\Http\Requests\ProjectFormRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('project.create', [
            'project' => Project::make([
                'user_id' => auth()->id(),
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectFormRequest $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $project = Project::create($request->validated() + [
            'user_id' => auth()->id(),
        ]);

        ProjectCreatedEvent::dispatch($project);

        return Redirect::route('project.show', $project)->with('status', __('Your project was successfully created.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): View
    {
        $this->authorize('view', $project);

        return view('project.show', [
            'project' => $project,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('project.edit', [
            'project' => $project,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectFormRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        ProjectUpdatedEvent::dispatch($project);

        return Redirect::route('project.show', $project)->with('status', __('Your project was successfully updated.'));
    }
    
}
