<?php
// app/Models/Lecon.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lecon extends Model
{
    protected $fillable = [
        'module_id', 'titre', 'type_contenu',
        'contenu_text', 'url_video', 'url_pdf', 'duree_video', 'ordre'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function progres()
    {
        return $this->hasMany(ProgresLecon::class);
    }

    public function getUrlAttribute()
    {
        return match($this->type_contenu) {
            'video' => $this->url_video,
            'pdf' => $this->url_pdf ? asset('storage/' . $this->url_pdf) : null,
            default => null,
        };
    }
}