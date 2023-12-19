<div class="form-control">
    <x-input-label for="project_name" :value="__('Project name')" />
    <x-text-input id="project_name" type="text" name="name" :value="old('name', $project->name)" maxlength="255" required autofocus />
    <x-input-error :messages="$errors->get('name')" />
</div>