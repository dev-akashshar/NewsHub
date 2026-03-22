<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard = 'hidden';

    protected $fillable = [
        'name', 'username', 'password', 'role', 'is_active', 'avatar', 'last_seen'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active'  => 'boolean',
        'last_seen'  => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class);
    }

    // ── Helpers ────────────────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=6366f1&color=fff&size=128";
    }

    /** Get latest message with a specific user */
    public function latestMessageWith(int $userId)
    {
        return Message::where(function ($q) use ($userId) {
            $q->where('sender_id', $this->id)->where('receiver_id', $userId);
        })->orWhere(function ($q) use ($userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $this->id);
        })->latest()->first();
    }

    /** Count unread messages from a specific user */
    public function unreadCountFrom(int $userId): int
    {
        return Message::where('sender_id', $userId)
            ->where('receiver_id', $this->id)
            ->whereNull('read_at')
            ->count();
    }
}
