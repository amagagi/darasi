<?php
// app/Models/ForumReponse.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumReponse extends Model
{
    protected $fillable = ['discussion_id', 'formateur_id', 'contenu'];

    public function discussion()
    {
        return $this->belongsTo(ForumDiscussion::class);
    }

    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }
}