<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $senderId;
    public int $receiverId;
    public array $signalData;

    public function __construct(int $senderId, int $receiverId, array $signalData)
    {
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
        $this->signalData = $signalData;
    }

    public function broadcastOn(): array
    {
        // Broadcasts securely only to the receiver's private channel
        return [
            new PrivateChannel('chat.' . $this->receiverId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'sender_id' => $this->senderId,
            'signal'    => $this->signalData,
        ];
    }

    public function broadcastAs(): string
    {
        return 'call.signal';
    }
}
