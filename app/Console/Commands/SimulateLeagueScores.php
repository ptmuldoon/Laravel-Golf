<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\LeagueMatch;
use App\Models\Team;
use App\Models\ScoringSetting;
use App\Services\MatchPlayCalculator;

class SimulateLeagueScores extends Command
{
    protected $signature = 'league:simulate {league_id} {--weeks=8}';
    protected $description = 'Simulate scores for scheduled matches in a league';

    private array $playerTierCache = [];

    public function handle()
    {
        $leagueId = $this->argument('league_id');
        $maxWeeks = (int) $this->option('weeks');

        $matches = DB::table('matches')
            ->where('league_id', $leagueId)
            ->where('status', 'scheduled')
            ->where('week_number', '<=', $maxWeeks)
            ->orderBy('week_number')
            ->orderBy('id')
            ->get();

        if ($matches->isEmpty()) {
            $this->error('No scheduled matches found.');
            return 1;
        }

        $this->info("Found {$matches->count()} scheduled matches across " . $matches->pluck('week_number')->unique()->count() . " weeks.");

        // Build player tiers for all players across all teams in this league
        $allTeamIds = $matches->pluck('home_team_id')->merge($matches->pluck('away_team_id'))->unique();
        $allPlayerIds = DB::table('team_players')->whereIn('team_id', $allTeamIds)->pluck('player_id')->unique()->toArray();
        $this->buildPlayerTiers($allPlayerIds);

        // Group matches by week
        $byWeek = $matches->groupBy('week_number');
        $allTeamIdsProcessed = [];

        foreach ($byWeek as $week => $weekMatches) {
            $this->info("--- Week {$week} ({$weekMatches->first()->scoring_type}, {$weekMatches->first()->holes}) ---");

            // Get teams for this week (may differ per season/segment)
            $weekHomeTeamId = $weekMatches->first()->home_team_id;
            $weekAwayTeamId = $weekMatches->first()->away_team_id;
            $allTeamIdsProcessed[$weekHomeTeamId] = true;
            $allTeamIdsProcessed[$weekAwayTeamId] = true;

            $homePlayerIds = DB::table('team_players')->where('team_id', $weekHomeTeamId)->pluck('player_id')->toArray();
            $awayPlayerIds = DB::table('team_players')->where('team_id', $weekAwayTeamId)->pluck('player_id')->toArray();

            // Shuffle players for this week's pairings
            shuffle($homePlayerIds);
            shuffle($awayPlayerIds);

            // Track all players who played this week for par 3 winner selection
            $weekPlayerIds = [];

            $matchIdx = 0;
            foreach ($weekMatches as $match) {
                $playedIds = $this->simulateMatch($match, $homePlayerIds, $awayPlayerIds, $matchIdx, $weekHomeTeamId, $weekAwayTeamId);
                $weekPlayerIds = array_merge($weekPlayerIds, $playedIds);
                $matchIdx++;
            }

            // Simulate par 3 winner for this week
            $this->simulatePar3Winner($leagueId, $week, $weekMatches->first(), array_unique($weekPlayerIds));
        }

        // Update team records for all teams that played
        foreach (array_keys($allTeamIdsProcessed) as $teamId) {
            $this->updateTeamRecords(Team::find($teamId), $leagueId);
        }

        // Print standings grouped by unique team pairs
        $this->info("\nFinal Standings:");
        foreach (array_keys($allTeamIdsProcessed) as $teamId) {
            $team = Team::find($teamId);
            $this->info("  {$team->name}: {$team->wins}W-{$team->losses}L-{$team->ties}T ({$this->getTeamPoints($teamId, $leagueId)} pts)");
        }

        $this->info("\nDone! All {$matches->count()} matches simulated.");
        return 0;
    }

    private function buildPlayerTiers(array $playerIds): void
    {
        foreach ($playerIds as $pid) {
            $hi = DB::table('handicap_history')
                ->where('player_id', $pid)
                ->orderByDesc('calculation_date')
                ->value('handicap_index') ?? 20.0;

            if ($hi <= 10.0) {
                $this->playerTierCache[$pid] = 'low';
            } elseif ($hi <= 22.0) {
                $this->playerTierCache[$pid] = 'mid';
            } else {
                $this->playerTierCache[$pid] = 'high';
            }
        }
    }

    private function simulateMatch($match, array $shuffledHome, array $shuffledAway, int $matchIdx, int $homeTeamId, int $awayTeamId): array
    {
        $teebox = $match->teebox ?? 'White';
        $courseId = $match->golf_course_id;
        $isBackNine = ($match->holes === 'back_9');
        $holeStart = $isBackNine ? 10 : 1;
        $holeEnd = $isBackNine ? 18 : 9;

        // Load course info
        $courseHoles = DB::table('course_info')
            ->where('golf_course_id', $courseId)
            ->where('teebox', $teebox)
            ->orderBy('hole_number')
            ->get();

        $holeRange = $courseHoles->where('hole_number', '>=', $holeStart)->where('hole_number', '<=', $holeEnd);
        $firstHole = $courseHoles->first();
        $slope = (float) $firstHole->slope;
        $rating = (float) $firstHole->rating;
        $par = $courseHoles->sum('par');

        // Pick 2 players per team for this match
        $homeP1 = $shuffledHome[$matchIdx * 2] ?? $shuffledHome[0];
        $homeP2 = $shuffledHome[$matchIdx * 2 + 1] ?? $shuffledHome[1];
        $awayP1 = $shuffledAway[$matchIdx * 2] ?? $shuffledAway[0];
        $awayP2 = $shuffledAway[$matchIdx * 2 + 1] ?? $shuffledAway[1];

        // Create match_players
        $matchPlayerIds = [];
        $position = 1;
        foreach ([
            [$homeP1, $homeTeamId],
            [$homeP2, $homeTeamId],
            [$awayP1, $awayTeamId],
            [$awayP2, $awayTeamId],
        ] as [$playerId, $teamId]) {
            $latestHI = DB::table('handicap_history')
                ->where('player_id', $playerId)
                ->orderByDesc('calculation_date')
                ->value('handicap_index') ?? 20.0;

            $ch18 = round(($latestHI * $slope / 113) + ($rating - $par));
            $ch9 = (int) round($ch18 / 2);

            $mpId = DB::table('match_players')->insertGetId([
                'match_id'            => $match->id,
                'team_id'             => $teamId,
                'player_id'           => $playerId,
                'handicap_index'      => $latestHI,
                'course_handicap'     => $ch18,
                'position_in_pairing' => $position,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            $matchPlayerIds[] = [
                'mp_id'     => $mpId,
                'player_id' => $playerId,
                'team_id'   => $teamId,
                'ch9'       => $ch9,
            ];
            $position++;
        }

        // Generate scores for each player on each hole
        $teamScores = [$homeTeamId => [], $awayTeamId => []];
        $roundData = [];

        foreach ($matchPlayerIds as $mp) {
            $tier = $this->playerTierCache[$mp['player_id']] ?? 'mid';
            $holeScores = [];

            foreach ($holeRange as $hole) {
                $strokes = $this->generateScore($hole->par, $tier, $hole->handicap);

                // Stroke allocation for net score
                $ch = max(0, $mp['ch9']);
                $base = intdiv($ch, 9);
                $remainder = $ch % 9;
                $strokesReceived = $base + ($hole->handicap <= $remainder ? 1 : 0);

                $netScore = $strokes - $strokesReceived;
                $maxScore = $hole->par + 2 + $strokesReceived;
                $adjustedGross = min($strokes, $maxScore);

                DB::table('match_scores')->insert([
                    'match_player_id' => $mp['mp_id'],
                    'hole_number'     => $hole->hole_number,
                    'strokes'         => $strokes,
                    'adjusted_gross'  => $adjustedGross,
                    'net_score'       => $netScore,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                $teamScores[$mp['team_id']][$hole->hole_number][] = $netScore;
                $holeScores[] = [
                    'hole_number'    => $hole->hole_number,
                    'strokes'        => $strokes,
                    'adjusted_gross' => $adjustedGross,
                    'net_score'      => $netScore,
                ];
            }

            $roundData[] = [
                'player_id'       => $mp['player_id'],
                'mp_id'           => $mp['mp_id'],
                'hole_scores'     => $holeScores,
            ];
        }

        // Create rounds and round scores for handicap tracking
        foreach ($roundData as $rd) {
            $roundId = DB::table('rounds')->insertGetId([
                'player_id'       => $rd['player_id'],
                'match_player_id' => $rd['mp_id'],
                'golf_course_id'  => $courseId,
                'teebox'          => $teebox,
                'played_at'       => $match->match_date,
                'holes_played'    => 9,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            foreach ($rd['hole_scores'] as $hs) {
                DB::table('scores')->insert([
                    'round_id'       => $roundId,
                    'hole_number'    => $hs['hole_number'],
                    'strokes'        => $hs['strokes'],
                    'adjusted_gross' => $hs['adjusted_gross'],
                    'net_score'      => $hs['net_score'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }

        // Calculate match result using the service
        $leagueMatch = LeagueMatch::find($match->id);

        // Update status to completed first so players are loaded
        DB::table('matches')->where('id', $match->id)->update([
            'status' => 'completed',
            'updated_at' => now(),
        ]);

        $calculator = new MatchPlayCalculator();
        $result = $calculator->calculateMatchResult($leagueMatch->fresh());

        DB::table('match_results')->insert([
            'match_id'         => $match->id,
            'winning_team_id'  => $result['winning_team_id'],
            'holes_won_home'   => $result['holes_won_home'],
            'holes_won_away'   => $result['holes_won_away'],
            'holes_tied'       => $result['holes_tied'],
            'team_points_home' => $result['team_points_home'],
            'team_points_away' => $result['team_points_away'],
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $winnerLabel = $result['winning_team_id']
            ? ($result['winning_team_id'] == $homeTeamId ? 'HOME' : 'AWAY')
            : 'TIE';

        $this->line("  Match {$match->id}: {$result['holes_won_home']}-{$result['holes_won_away']}-{$result['holes_tied']} => {$winnerLabel} ({$result['team_points_home']}/{$result['team_points_away']} pts)");

        return [$homeP1, $homeP2, $awayP1, $awayP2];
    }

    private function simulatePar3Winner(int $leagueId, int $week, $sampleMatch, array $playerIds): void
    {
        $isBackNine = ($sampleMatch->holes === 'back_9');
        $holeStart = $isBackNine ? 10 : 1;
        $holeEnd = $isBackNine ? 18 : 9;

        // Find par 3 holes in the played range
        $par3Holes = DB::table('course_info')
            ->where('golf_course_id', $sampleMatch->golf_course_id)
            ->where('teebox', $sampleMatch->teebox ?? 'White')
            ->where('par', 3)
            ->whereBetween('hole_number', [$holeStart, $holeEnd])
            ->pluck('hole_number')
            ->toArray();

        if (empty($par3Holes) || empty($playerIds)) return;

        // Pick a random par 3 hole and a random player as the winner
        $hole = $par3Holes[array_rand($par3Holes)];
        $winnerId = $playerIds[array_rand($playerIds)];
        $distance = rand(1, 30) . "'" . rand(0, 11) . '"';

        $par3WinnerId = DB::table('par3_winners')->insertGetId([
            'league_id'   => $leagueId,
            'week_number' => $week,
            'hole_number' => $hole,
            'player_id'   => $winnerId,
            'distance'    => $distance,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Create finance entry if league has par3_payout configured
        $par3Payout = (float) DB::table('leagues')->where('id', $leagueId)->value('par3_payout');
        if ($par3Payout > 0) {
            DB::table('league_finances')->insert([
                'league_id'      => $leagueId,
                'player_id'      => $winnerId,
                'type'           => 'winnings',
                'amount'         => $par3Payout,
                'date'           => $sampleMatch->match_date,
                'notes'          => 'Par 3 Winner - Week ' . $week . ', Hole ' . $hole,
                'par3_winner_id' => $par3WinnerId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        $playerName = DB::table('players')->where('id', $winnerId)->value(DB::raw("CONCAT(first_name, ' ', last_name)"));
        $this->line("  🏆 Par 3 Winner: {$playerName} on hole {$hole} ({$distance})" . ($par3Payout > 0 ? " [\${$par3Payout}]" : ''));
    }

    private function updateTeamRecords(Team $team, int $leagueId): void
    {
        $teamMatchIds = DB::table('matches')
            ->where('league_id', $leagueId)
            ->where(function ($q) use ($team) {
                $q->where('home_team_id', $team->id)
                  ->orWhere('away_team_id', $team->id);
            })
            ->pluck('id');

        $wins = DB::table('match_results')
            ->whereIn('match_id', $teamMatchIds)
            ->where('winning_team_id', $team->id)
            ->count();

        $losses = DB::table('match_results')
            ->whereIn('match_id', $teamMatchIds)
            ->whereNotNull('winning_team_id')
            ->where('winning_team_id', '!=', $team->id)
            ->count();

        $ties = DB::table('match_results')
            ->whereIn('match_id', $teamMatchIds)
            ->whereNull('winning_team_id')
            ->count();

        $team->update(['wins' => $wins, 'losses' => $losses, 'ties' => $ties]);
    }

    private function getTeamPoints(int $teamId, int $leagueId): float
    {
        $matchIds = DB::table('matches')
            ->where('league_id', $leagueId)
            ->where('status', 'completed')
            ->get(['id', 'home_team_id', 'away_team_id']);

        $total = 0;
        foreach ($matchIds as $m) {
            $result = DB::table('match_results')->where('match_id', $m->id)->first();
            if (!$result) continue;
            if ($m->home_team_id == $teamId) {
                $total += (float) $result->team_points_home;
            } elseif ($m->away_team_id == $teamId) {
                $total += (float) $result->team_points_away;
            }
        }
        return $total;
    }

    // ─── Score generation (matching DemoSeeder distributions) ───

    private function generateScore(int $par, string $tier, ?int $holeHandicap): int
    {
        $rand = rand(1, 100);

        $difficultyShift = 0;
        if ($holeHandicap !== null) {
            if ($holeHandicap <= 6) {
                $difficultyShift = rand(0, 1);
            } elseif ($holeHandicap >= 13) {
                $difficultyShift = -rand(0, 1);
            }
        }

        $score = match ($tier) {
            'low'  => $this->scoreLow($par, $rand),
            'mid'  => $this->scoreMid($par, $rand),
            'high' => $this->scoreHigh($par, $rand),
            default => $this->scoreMid($par, $rand),
        };

        return max(1, $score + $difficultyShift);
    }

    private function scoreLow(int $par, int $rand): int
    {
        if ($rand <= 1) return max(1, $par - 2);
        if ($rand <= 8) return $par - 1;
        if ($rand <= 45) return $par;
        if ($rand <= 78) return $par + 1;
        if ($rand <= 94) return $par + 2;
        if ($rand <= 99) return $par + 3;
        return $par + 4;
    }

    private function scoreMid(int $par, int $rand): int
    {
        if ($rand <= 3) return $par - 1;
        if ($rand <= 23) return $par;
        if ($rand <= 56) return $par + 1;
        if ($rand <= 81) return $par + 2;
        if ($rand <= 94) return $par + 3;
        if ($rand <= 99) return $par + 4;
        return $par + 5;
    }

    private function scoreHigh(int $par, int $rand): int
    {
        if ($rand <= 8) return $par;
        if ($rand <= 29) return $par + 1;
        if ($rand <= 58) return $par + 2;
        if ($rand <= 80) return $par + 3;
        if ($rand <= 93) return $par + 4;
        return $par + 5;
    }
}
