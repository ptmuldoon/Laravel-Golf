<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoringSetting extends Model
{
    protected $table = 'scoring_settings';

    protected $fillable = [
        'league_id',
        'scoring_type',
        'outcome',
        'points',
        'description',
    ];

    protected $casts = [
        'points' => 'decimal:2',
    ];

    public const TYPE_INDIVIDUAL_MATCH_PLAY = 'individual_match_play';
    public const TYPE_BEST_BALL_MATCH_PLAY = 'best_ball_match_play';
    public const TYPE_TEAM_2BALL_MATCH_PLAY = 'team_2ball_match_play';
    public const TYPE_SCRAMBLE = 'scramble';
    public const TYPE_STABLEFORD = 'stableford';

    public const OUTCOME_WIN = 'win';
    public const OUTCOME_LOSS = 'loss';
    public const OUTCOME_TIE = 'tie';

    public const OUTCOME_ALBATROSS = 'albatross';
    public const OUTCOME_EAGLE = 'eagle';
    public const OUTCOME_BIRDIE = 'birdie';
    public const OUTCOME_PAR = 'par';
    public const OUTCOME_BOGEY = 'bogey';
    public const OUTCOME_DOUBLE_BOGEY_OR_WORSE = 'double_bogey_or_worse';

    public function league()
    {
        return $this->belongsTo(League::class);
    }

    /**
     * Get human-readable labels for all scoring types
     */
    public static function scoringTypes(): array
    {
        return [
            self::TYPE_INDIVIDUAL_MATCH_PLAY => 'Individual Match Play',
            self::TYPE_BEST_BALL_MATCH_PLAY => 'Best Ball Match Play',
            self::TYPE_TEAM_2BALL_MATCH_PLAY => '2 Ball Match Play',
            self::TYPE_SCRAMBLE => 'Scramble Play',
            self::TYPE_STABLEFORD => 'Stableford Scoring',
        ];
    }

    /**
     * Get point value for a specific scoring type and outcome.
     * Returns the fallback default if not found in database.
     */
    public static function getPoints(string $scoringType, string $outcome, float $default = 0.0, ?int $leagueId = null): float
    {
        $query = static::where('scoring_type', $scoringType)
            ->where('outcome', $outcome);

        if ($leagueId) {
            $query->where('league_id', $leagueId);
        }

        $setting = $query->first();

        return $setting ? (float) $setting->points : $default;
    }

    /**
     * Get all settings grouped by scoring type, ordered for admin display
     */
    public static function allGroupedByType(?int $leagueId = null): \Illuminate\Support\Collection
    {
        $query = static::orderBy('scoring_type')
            ->orderByRaw("FIELD(outcome, 'win', 'loss', 'tie', 'albatross', 'eagle', 'birdie', 'par', 'bogey', 'double_bogey_or_worse')");

        if ($leagueId) {
            $query->where('league_id', $leagueId);
        }

        return $query->get()->groupBy('scoring_type');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('scoring_type', $type);
    }
}
