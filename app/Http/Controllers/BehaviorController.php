<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use App\Models\Project;
use App\Models\Behavior;
use App\Models\Endpoint;
use Illuminate\Http\Request;
use App\Http\Requests\BehaviorFormRequest;
use App\Models\Action;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

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

    public function store(BehaviorFormRequest $request, Project $project, Endpoint $endpoint): RedirectResponse
    {
        //$this->authorize('create', Behavior::class);

        $formArray = $request->validated();

        $behavior = Behavior::create([
            'endpoint_id' => $endpoint->id,
        ]);

        foreach( $formArray['rules'] as $ruleArray ){
            Rule::create(array_merge([
                'behavior_id' => $behavior->id,
            ], $ruleArray));
        }

        foreach( $formArray['actions'] as $actionArray ){
            Action::create(array_merge([
                'behavior_id' => $behavior->id,
            ], $actionArray));
        }

        return Redirect::route('endpoint.show', [$project, $endpoint])->with('status', __('Behavior created successfully.'));
    }

    public function edit(Project $project, Endpoint $endpoint, Behavior $behavior)
    {
        return view('project.endpoint.behavior.edit', [
            'project'  => $project,
            'endpoint' => $endpoint,
            'behavior' => $behavior,
        ]);
    }

    public function update(BehaviorFormRequest $request, Project $project, Endpoint $endpoint, Behavior $behavior): RedirectResponse
    {
        //dd($request->all());

        $formArray = $request->validated();

        $rulesIds = [];
        foreach( $formArray['rules'] as $ruleArray ){
            if($ruleArray['id']>0){
                $rule = Rule::find( $ruleArray['id'] );
                $rule->update($ruleArray);
            }else{
                $rule = Rule::create(array_merge([
                    'behavior_id' => $behavior->id,
                ], $ruleArray));
            }
            $rulesIds[ $rule->id ] = $rule->id;
        }

        Rule::where('behavior_id', $behavior->id)
            ->whereNotIn('id', $rulesIds)
            ->delete();

        $actionsIds = [];
        foreach( $formArray['actions'] as $actionArray ){
            if($actionArray['id']>0){
                $action = Action::find( $actionArray['id'] );
                $action->update($actionArray);
            }else{
                $action = Action::create(array_merge([
                    'behavior_id' => $behavior->id,
                ], $actionArray));
            }
            $actionsIds[ $action->id ] = $action->id;
        }

        Action::where('behavior_id', $behavior->id)
            ->whereNotIn('id', $actionsIds)
            ->delete();
            
        return Redirect::route('endpoint.show', [$project, $endpoint])->with('status', __('Behavior updated successfully.'));
    }

    public function destroy(Behavior $behavior)
    {
        //
    }
}
