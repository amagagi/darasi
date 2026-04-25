<?php
// app/Models/ForumDiscussion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumDiscussion extends Model
{
    protected $fillable = ['cours_id', 'apprenant_id', 'titre', 'contenu', 'est_resolu'];

    protected $casts = [
        'est_resolu' => 'boolean',
    ];

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function apprenant()
    {
        return $this->belongsTo(User::class, 'apprenant_id');
    }

        public function reponses()
    {
        return $this->hasMany(ForumReponse::class, 'discussion_id');
    }
}