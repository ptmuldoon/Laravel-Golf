<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchPlayer extends Model
{
    protected $fillable = [
        'match_id',
        'team_id',
        'player_id',
        'substitute_player_id',
        'substitute_name',
        'handicap_index',
        'course_handicap',
        'position_in_pairing',
    ];

    public function match()
    {
        return $this->belongsTo(LeagueMatch::class, 'match_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function substitutePlayer()
    {
        return $this->belongsTo(Player::class, 'substitute_player_id');
    }

    public function activePlayer()
    {
        return $this->substitutePlayer ?? $this->player;
    }

    public function getDisplayNameAttribute()
    {
        if ($this->substitute_player_id && $this->substitutePlayer) {
            return $this->substitutePlayer->name . ' (sub)';
        }
        if ($this->substitute_name) {
            return $this->substitute_name . ' (sub)';
        }
        return $this->player->name;
    }

    public function getHasSubstituteAttribute()
    {
        return $this->substitute_player_id !== null || $this->substitute_name !== null;
    }

    public function scores()
    {
        return $this->hasMany(MatchScore::class);
    }

    public function totalStrokes()
    {
        return $this->scores()->sum('strokes');
    }

    public function totalNetScore()
    {
        return $this->scores()->sum('net_score');
    }
}
