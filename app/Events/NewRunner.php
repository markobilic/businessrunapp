<?php

namespace App\Events;

use App\Models\Runner;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewRunner
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $runner;

    /**
     * Create a new event instance.
     */
    public function __construct(Runner $runner)
    {
        $this->runner = $runner;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
