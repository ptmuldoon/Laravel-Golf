<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Par3Winner extends Model
{
    protected $fillable = [
        'league_id',
        'week_number',
        'hole_number',
        'player_id',
        'distance',
    ];

    public function league()
    {
        return $this->belongsTo(League::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
