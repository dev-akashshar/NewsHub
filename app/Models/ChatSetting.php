<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSetting extends Model
{
    protected $fillable = ['user_id', 'chat_with_id', 'auto_delete', 'pin_hash', 'is_pinned'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatWith()
    {
        return $this->belongsTo(User::class, 'chat_with_id');
    }
}
