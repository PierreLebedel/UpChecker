<?php

namespace App\Http\Controllers;

use App\Models\Behavior;
use App\Models\Endpoint;
use App\Models\Project;
use Illuminate\Http\Request;

class BehaviorController extends Controller
{
    public function create(Project $project, Endpoint $endpoint)
    {
        return view('project.endpoint.behavior.create', [
            'project'  => $project,
            'endpoint' => $endpoint,
            'behavior' => Behavior::make([
                'endpoint_id' => $endpoint->id,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        dd($request->all());
    }

    public function edit(Behavior $behavior)
    {
        //
    }

    public function update(Request $request, Behavior $behavior)
    {
        dd($request->all());
    }

    public function destroy(Behavior $behavior)
    {
        //
    }
}
