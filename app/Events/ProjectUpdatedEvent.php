<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Project $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->project->user_id),
        ];
    }
}
