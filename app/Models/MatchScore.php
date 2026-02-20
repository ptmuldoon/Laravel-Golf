<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchScore extends Model
{
    protected $fillable = [
        'match_player_id',
        'hole_number',
        'strokes',
        'adjusted_gross',
        'net_score',
    ];

    public function matchPlayer()
    {
        return $this->belongsTo(MatchPlayer::class);
    }
}
