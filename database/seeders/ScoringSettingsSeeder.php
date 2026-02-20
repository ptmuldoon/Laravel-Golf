<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\League;
use App\Models\ScoringSetting;

class ScoringSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $leagues = League::all();

        foreach ($leagues as $league) {
            static::seedForLeague($league->id);
        }
    }

    public static function defaultSettings(): array
    {
        return [
            // Individual Match Play
            ['scoring_type' => 'individual_match_play', 'outcome' => 'win', 'points' => 1.00, 'description' => 'Points for winning individual match play'],
            ['scoring_type' => 'individual_match_play', 'outcome' => 'loss', 'points' => 0.00, 'description' => 'Points for losing individual match play'],
            ['scoring_type' => 'individual_match_play', 'outcome' => 'tie', 'points' => 0.50, 'description' => 'Points for tying individual match play'],

            // Best Ball Match Play
            ['scoring_type' => 'best_ball_match_play', 'outcome' => 'win', 'points' => 1.00, 'description' => 'Points for winning best ball match play'],
            ['scoring_type' => 'best_ball_match_play', 'outcome' => 'loss', 'points' => 0.00, 'description' => 'Points for losing best ball match play'],
            ['scoring_type' => 'best_ball_match_play', 'outcome' => 'tie', 'points' => 0.50, 'description' => 'Points for tying best ball match play'],

            // Team 2 Ball Match Play
            ['scoring_type' => 'team_2ball_match_play', 'outcome' => 'win', 'points' => 1.00, 'description' => 'Points for winning team 2 ball match play'],
            ['scoring_type' => 'team_2ball_match_play', 'outcome' => 'loss', 'points' => 0.00, 'description' => 'Points for losing team 2 ball match play'],
            ['scoring_type' => 'team_2ball_match_play', 'outcome' => 'tie', 'points' => 0.50, 'description' => 'Points for tying team 2 ball match play'],

            // Scramble Play
            ['scoring_type' => 'scramble', 'outcome' => 'win', 'points' => 1.00, 'description' => 'Points for winning scramble play'],
            ['scoring_type' => 'scramble', 'outcome' => 'loss', 'points' => 0.00, 'description' => 'Points for losing scramble play'],
            ['scoring_type' => 'scramble', 'outcome' => 'tie', 'points' => 0.50, 'description' => 'Points for tying scramble play'],

            // Stableford - per-hole scoring
            ['scoring_type' => 'stableford', 'outcome' => 'albatross', 'points' => 5.00, 'description' => 'Points for albatross (3 under par) or better'],
            ['scoring_type' => 'stableford', 'outcome' => 'eagle', 'points' => 4.00, 'description' => 'Points for eagle (2 under par)'],
            ['scoring_type' => 'stableford', 'outcome' => 'birdie', 'points' => 3.00, 'description' => 'Points for birdie (1 under par)'],
            ['scoring_type' => 'stableford', 'outcome' => 'par', 'points' => 2.00, 'description' => 'Points for par'],
            ['scoring_type' => 'stableford', 'outcome' => 'bogey', 'points' => 1.00, 'description' => 'Points for bogey (1 over par)'],
            ['scoring_type' => 'stableford', 'outcome' => 'double_bogey_or_worse', 'points' => 0.00, 'description' => 'Points for double bogey or worse'],

            // Stableford - match-level outcomes
            ['scoring_type' => 'stableford', 'outcome' => 'win', 'points' => 2.00, 'description' => 'Bonus points for winning stableford round'],
            ['scoring_type' => 'stableford', 'outcome' => 'loss', 'points' => 0.00, 'description' => 'Points for losing stableford round'],
            ['scoring_type' => 'stableford', 'outcome' => 'tie', 'points' => 1.00, 'description' => 'Points for tying stableford round'],
        ];
    }

    public static function seedForLeague(int $leagueId): void
    {
        foreach (static::defaultSettings() as $setting) {
            ScoringSetting::firstOrCreate(
                [
                    'league_id' => $leagueId,
                    'scoring_type' => $setting['scoring_type'],
                    'outcome' => $setting['outcome'],
                ],
                [
                    'points' => $setting['points'],
                    'description' => $setting['description'],
                ]
            );
        }
    }
}
