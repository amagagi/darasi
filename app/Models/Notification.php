<?php
// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'titre', 'message', 'type', 'est_lu', 'data'];

    protected $casts = [
        'est_lu' => 'boolean',
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNonLues($query)
    {
        return $query->where('est_lu', false);
    }
}