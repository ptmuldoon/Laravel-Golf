<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    protected $fillable = [
        'name',
        'season',
        'start_date',
        'end_date',
        'golf_course_id',
        'default_teebox',
        'is_active',
        'fee_per_player',
        'par3_payout',
        'segment_winner_payout',
        'payout_1st_pct',
        'payout_2nd_pct',
        'payout_3rd_pct',
        'sub_request_code',
        'default_tee_time',
        'tee_time_interval',
        'flash_message',
        'flash_message_enabled',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'flash_message_enabled' => 'boolean',
        'fee_per_player' => 'decimal:2',
        'par3_payout' => 'decimal:2',
        'segment_winner_payout' => 'decimal:2',
        'payout_1st_pct' => 'decimal:2',
        'payout_2nd_pct' => 'decimal:2',
        'payout_3rd_pct' => 'decimal:2',
    ];

    public function golfCourse()
    {
        return $this->belongsTo(GolfCourse::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class)->orderBy('id');
    }

    public function matches()
    {
        return $this->hasMany(LeagueMatch::class);
    }

    public function players()
    {
        return $this->belongsToMany(Player::class, 'league_players')
            ->withTimestamps();
    }

    public function segments()
    {
        return $this->hasMany(LeagueSegment::class)->orderBy('display_order');
    }

    public function hasSegments()
    {
        return $this->segments()->exists();
    }

    /**
     * Whether the season has finished playing.
     *
     * True once the league has a schedule, every match on it is completed, and
     * the league's end date has passed. The end date is what keeps a partially
     * built schedule — eight weeks generated and scored out of a planned
     * sixteen — from reading as a finished season. Gates the public Season
     * Recap link on the home page; the recap URL itself stays reachable so
     * admins can preview it early.
     */
    public function isSeasonComplete(): bool
    {
        if ($this->end_date && $this->end_date->isFuture()) {
            return false;
        }

        return $this->matches()->exists()
            && !$this->matches()->where('status', '!=', 'completed')->exists();
    }

    public function par3Winners()
    {
        return $this->hasMany(Par3Winner::class);
    }

    public function scoringSettings()
    {
        return $this->hasMany(ScoringSetting::class);
    }

    public function finances()
    {
        return $this->hasMany(LeagueFinance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
