<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HandicapHistory extends Model
{
    protected $table = 'handicap_history';

    protected $fillable = [
        'player_id',
        'calculation_date',
        'handicap_index',
        'rounds_used',
        'score_differentials'
    ];

    protected $casts = [
        'calculation_date' => 'date',
        'score_differentials' => 'array',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
