<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id', 'receiver_id', 'content', 'type',
        'file_path', 'read_at', 'reply_to_id', 'reactions',
        'is_deleted_by_sender', 'is_deleted_by_receiver'
    ];

    protected $casts = [
        'read_at'                => 'datetime',
        'is_deleted_by_sender'   => 'boolean',
        'is_deleted_by_receiver' => 'boolean',
        'reactions'              => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────
    public function scopeConversation($query, int $userId1, int $userId2)
    {
        return $query->where(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId1)->where('receiver_id', $userId2);
        })->orWhere(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId2)->where('receiver_id', $userId1);
        });
    }

    public function scopeVisibleTo($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)->where('is_deleted_by_sender', false);
        })->orWhere(function ($q) use ($userId) {
            $q->where('receiver_id', $userId)->where('is_deleted_by_receiver', false);
        });
    }

    // ── Helpers ────────────────────────────────────────────────────
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function addReaction(int $userId, string $emoji): void
    {
        $reactions = $this->reactions ?? [];
        $reactions[$userId] = $emoji;
        $this->update(['reactions' => $reactions]);
    }

    public function removeReaction(int $userId): void
    {
        $reactions = $this->reactions ?? [];
        unset($reactions[$userId]);
        $this->update(['reactions' => $reactions]);
    }
}
