<?php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        // Broadcast on BOTH users' private channels
        return [
            new PrivateChannel('chat.' . $this->message->sender_id),
            new PrivateChannel('chat.' . $this->message->receiver_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->message->id,
            'sender_id'   => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'content'     => $this->message->content,
            'type'        => $this->message->type,
            'file_path'   => $this->message->file_path,
            'created_at'  => $this->message->created_at->toDateTimeString(),
            'sender'      => [
                'id'         => $this->message->sender->id,
                'name'       => $this->message->sender->name,
                'avatar_url' => $this->message->sender->avatar_url,
            ],
        ];
    }

    public function broadcastAs(): string
    {
        return 'new.message';
    }
}
