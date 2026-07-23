<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MonitorCheckCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $monitorId,
        public string $userId,
        public string $checkResultId,
        public string $status,
        public string $checkedAt,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('monitors.'.$this->monitorId),
            new PrivateChannel('users.'.$this->userId),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function broadcastWith(): array
    {
        return [
            'monitorId' => $this->monitorId,
            'userId' => $this->userId,
            'checkResultId' => $this->checkResultId,
            'status' => $this->status,
            'checkedAt' => $this->checkedAt,
        ];
    }
}
