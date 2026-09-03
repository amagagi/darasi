<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Curseur de lecture : dernier message lu par un utilisateur dans un fil.
 */
class ConversationLecture extends Model
{
    protected $table = 'conversation_lectures';

    protected $fillable = [
        'conversation_id',
        'user_id',
        'dernier_message_lu_id',
    ];

    protected $casts = [
        'dernier_message_lu_id' => 'integer',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
