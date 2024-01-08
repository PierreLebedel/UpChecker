<x-mail::message>
# {{ __("Project:") }} {{ $project->name }}
## {{ __("Endpoint:") }} {{ $endpoint->url }}
 
You receive this alert because the last checkup of this endpoint satisfied all the rules configured in {{ config('app.name') }}:

<x-mail::panel>
@foreach ($behavior->rules as $rule)
- {{ $rule->type->getInstance($rule)->toString() }}
@endforeach
</x-mail::panel>
 
<x-mail::button url="{{ route('endpoint.show', [$project, $endpoint]) }}">
{{ __("View endpoint history") }}
</x-mail::button>
 
{{ __("Thanks") }},<br>
{{ config('app.name') }}
</x-mail::message>