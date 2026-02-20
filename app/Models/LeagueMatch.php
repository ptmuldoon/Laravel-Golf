<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeagueMatch extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'league_id',
        'week_number',
        'match_date',
        'tee_time',
        'golf_course_id',
        'teebox',
        'holes',
        'scoring_type',
        'score_mode',
        'home_team_id',
        'away_team_id',
        'ride_with_opponent',
        'status',
    ];

    protected $casts = [
        'match_date' => 'date',
        'ride_with_opponent' => 'boolean',
    ];

    public function league()
    {
        return $this->belongsTo(League::class);
    }

    public function golfCourse()
    {
        return $this->belongsTo(GolfCourse::class);
    }

    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function matchPlayers()
    {
        return $this->hasMany(MatchPlayer::class, 'match_id');
    }

    public function result()
    {
        return $this->hasOne(MatchResult::class, 'match_id');
    }

    public function scopeByWeek($query, $week)
    {
        return $query->where('week_number', $week);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
