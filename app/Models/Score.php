<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = ['round_id', 'hole_number', 'strokes', 'adjusted_gross', 'net_score'];

    public function round()
    {
        return $this->belongsTo(Round::class);
    }
}
