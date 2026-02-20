<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeagueSegment extends Model
{
    protected $fillable = [
        'league_id',
        'name',
        'start_week',
        'end_week',
        'display_order',
    ];

    public function league()
    {
        return $this->belongsTo(League::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function weekCount()
    {
        return $this->end_week - $this->start_week + 1;
    }
}
