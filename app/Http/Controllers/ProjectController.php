<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\ProjectFormRequest;

class ProjectController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Project::class);

        $projects = auth()->user()->projects()->withCount('endpoints')->get();

        return view('project.index', [
            'projects' => $projects,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('project.create');
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

        return Redirect::route('project.show', $project)->with('status', __("Votre projet a bien été créé."));
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

        $project->update( $request->validated() );

        return Redirect::route('project.show', $project)->with('status', __("Votre projet a bien été enregistré."));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return Redirect::route('project.index')->with('status', __("Votre projet a bien été supprimé."));
    }
}
