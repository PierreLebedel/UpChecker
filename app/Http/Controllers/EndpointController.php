<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Endpoint;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\EndpointFormRequest;
use Illuminate\Http\RedirectResponse;

class EndpointController extends Controller
{

    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        $this->authorize('create', Endpoint::class);

        //'slug' => Str::random(20),


        return view('project.endpoint.create', [
            'project' => $project,
            'endpoint' => Endpoint::make([
                'project_id' => $project->id,
                'expected_status_code' => 200,
                'timeout' => 30,
                'follow_redirects' => true,
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EndpointFormRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('create', Endpoint::class);

        $endpoint = Endpoint::create($request->validated() + [
            'project_id' => $project->id,
        ]);

        return Redirect::route('project.show', $project)->with('status', __("Endpoint created successfully"));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Endpoint $endpoint)
    {
        $this->authorize('view', $endpoint);

        return view('project.endpoint.show', [
            'project' => $project,
            'endpoint' => $endpoint,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project, Endpoint $endpoint)
    {
        $this->authorize('update', $endpoint);

        return view('project.endpoint.edit', [
            'project' => $project,
            'endpoint' => $endpoint,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EndpointFormRequest $request, Project $project, Endpoint $endpoint)
    {
        $this->authorize('update', $endpoint);

        $endpoint->update( $request->validated() );

        return Redirect::route('endpoint.show', [$project, $endpoint])->with('status', __("Endpoint updated successfully."));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Endpoint $endpoint)
    {
        $this->authorize('delete', $endpoint);

        $endpoint->delete();

        return Redirect::route('project.show', $project)->with('status', __("Endpoint deleted successfully."));
    }
}
