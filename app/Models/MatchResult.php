<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchResult extends Model
{
    protected $fillable = [
        'match_id',
        'winning_team_id',
        'holes_won_home',
        'holes_won_away',
        'holes_tied',
        'team_points_home',
        'team_points_away',
    ];

    public function match()
    {
        return $this->belongsTo(LeagueMatch::class, 'match_id');
    }

    public function winningTeam()
    {
        return $this->belongsTo(Team::class, 'winning_team_id');
    }

    public function isTie()
    {
        return $this->winning_team_id === null;
    }

    public function margin()
    {
        return abs($this->holes_won_home - $this->holes_won_away);
    }
}
