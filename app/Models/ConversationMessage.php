<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'expediteur_id',
        'contenu',
        'est_masque',
        'masque_par',
    ];

    protected $casts = [
        'est_masque' => 'boolean',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function expediteur()
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }

    public function masquePar()
    {
        return $this->belongsTo(User::class, 'masque_par');
    }

    protected static function booted(): void
    {
        // Maintient le tri de la liste des conversations sans agrégation.
        static::created(function (self $message) {
            $message->conversation?->forceFill([
                'dernier_message_le' => $message->created_at,
            ])->save();
        });
    }
}
