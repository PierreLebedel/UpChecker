<?php

namespace App\Events;

use App\Models\Checkup;
use App\Models\Endpoint;
use App\Models\Project;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckupCreatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Project $project;

    public Endpoint $endpoint;

    public Checkup $checkup;

    public function __construct(Checkup $checkup)
    {
        $this->checkup  = $checkup;
        $this->endpoint = $this->checkup->endpoint;
        $this->project  = $this->endpoint->project;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->project->user_id),
        ];
    }
}
