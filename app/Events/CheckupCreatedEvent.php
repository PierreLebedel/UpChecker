<?php

namespace App\Events;

use App\Models\Checkup;
use App\Models\Endpoint;
use App\Models\Project;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class CheckupCreatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $project;
    public $endpoint;
    public $checkup;

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
