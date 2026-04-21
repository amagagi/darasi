<?php
// app/Models/ReponseQuestion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReponseQuestion extends Model
{
    protected $fillable = [
        'tentative_test_id', 'tentative_final_id', 'question_id',
        'reponse_texte', 'choix_id', 'est_correcte', 'points_obtenus'
    ];

    protected $casts = [
        'est_correcte' => 'boolean',
        'points_obtenus' => 'decimal:2',
    ];

    public function tentativeTest()
    {
        return $this->belongsTo(TentativeTest::class);
    }

    public function tentativeFinal()
    {
        return $this->belongsTo(TentativeTestFinal::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function choix()
    {
        return $this->belongsTo(ChoixQuestion::class);
    }

    public function correction()
    {
        return $this->hasOne(CorrectionOuverte::class);
    }
}