<?php

namespace App\Http\Controllers;

use App\Events\EndpointCreatedEvent;
use App\Events\EndpointUpdatedEvent;
use App\Http\Requests\EndpointFormRequest;
use App\Models\Endpoint;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class EndpointController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        $this->authorize('create', Endpoint::class);

        return view('project.endpoint.create', [
            'project'  => $project,
            'endpoint' => Endpoint::make([
                'project_id'           => $project->id,
                'expected_status_code' => 200,
                'timeout'              => 30,
                'follow_redirects'     => true,
                'checkup_delay'        => 60,
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

        EndpointCreatedEvent::dispatch($endpoint);

        return Redirect::route('project.show', $project)->with('status', __('Endpoint created successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Endpoint $endpoint)
    {
        $this->authorize('view', $endpoint);

        return view('project.endpoint.show', [
            'project'  => $project,
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
            'project'  => $project,
            'endpoint' => $endpoint,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EndpointFormRequest $request, Project $project, Endpoint $endpoint)
    {
        $this->authorize('update', $endpoint);

        $endpoint->update($request->validated());

        EndpointUpdatedEvent::dispatch($endpoint);

        return Redirect::route('endpoint.show', [$project, $endpoint])->with('status', __('Endpoint updated successfully.'));
    }

}
