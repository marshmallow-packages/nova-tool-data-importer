<?php

namespace Marshmallow\NovaDataImporter\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportedErrorEvent implements ShouldBroadcast
{
    // use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $response;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ['myChannel'];

        return new Channel('myChannel');
        // return new PrivateChannel('myEvent');
    }

    public function broadcastAs()
    {
        return 'server.error';
    }

    public function broadcastWith()
    {
        return $this->response;
    }
}
