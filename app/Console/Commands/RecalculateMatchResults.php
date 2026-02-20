<?php

namespace App\Console\Commands;

use App\Models\LeagueMatch;
use App\Models\MatchResult;
use App\Models\Team;
use App\Services\MatchPlayCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateMatchResults extends Command
{
    protected $signature = 'matches:recalculate
                            {--match= : Recalculate a specific match ID only}
                            {--league= : Recalculate all matches in a specific league}
                            {--dry-run : Show what would change without saving}';

    protected $description = 'Recalculate match results from current scores and rebuild team W-L-T records';

    public function handle()
    {
        $calculator = app(MatchPlayCalculator::class);

        $query = LeagueMatch::with(['result', 'homeTeam', 'awayTeam'])
            ->where('status', 'completed');

        if ($this->option('match')) {
            $query->where('id', $this->option('match'));
        }
        if ($this->option('league')) {
            $query->where('league_id', $this->option('league'));
        }

        $matches = $query->orderBy('week_number')->orderBy('id')->get();

        if ($matches->isEmpty()) {
            $this->info('No completed matches found.');
            return;
        }

        $this->info("Recalculating results for {$matches->count()} matches...");

        $changed = 0;
        $unchanged = 0;

        DB::transaction(function () use ($matches, $calculator, &$changed, &$unchanged) {
            // First, reset all team W-L-T for affected teams
            $teamIds = $matches->pluck('home_team_id')
                ->merge($matches->pluck('away_team_id'))
                ->filter()
                ->unique();

            if (!$this->option('dry-run')) {
                Team::whereIn('id', $teamIds)->update([
                    'wins' => 0,
                    'losses' => 0,
                    'ties' => 0,
                ]);
            }

            foreach ($matches as $match) {
                $recalc = $calculator->calculateMatchResult($match);
                $old = $match->result;

                $isChanged = !$old
                    || $recalc['holes_won_home'] != $old->holes_won_home
                    || $recalc['holes_won_away'] != $old->holes_won_away
                    || $recalc['holes_tied'] != $old->holes_tied
                    || $recalc['winning_team_id'] != $old->winning_team_id;

                if ($isChanged) {
                    $changed++;
                    $oldStr = $old
                        ? "H={$old->holes_won_home} A={$old->holes_won_away} T={$old->holes_tied} winner={$old->winning_team_id}"
                        : 'none';
                    $newStr = "H={$recalc['holes_won_home']} A={$recalc['holes_won_away']} T={$recalc['holes_tied']} winner={$recalc['winning_team_id']}";
                    $this->line("  Match #{$match->id} Wk{$match->week_number}: {$oldStr} → {$newStr}");

                    if ($old && $old->winning_team_id != $recalc['winning_team_id']) {
                        $this->warn("    *** Winner changed!");
                    }
                } else {
                    $unchanged++;
                }

                if (!$this->option('dry-run')) {
                    MatchResult::updateOrCreate(
                        ['match_id' => $match->id],
                        $recalc
                    );

                    // Rebuild team W-L-T
                    $homeTeam = $match->homeTeam;
                    $awayTeam = $match->awayTeam;
                    if ($homeTeam && $awayTeam) {
                        if ($recalc['winning_team_id'] == $homeTeam->id) {
                            $homeTeam->increment('wins');
                            $awayTeam->increment('losses');
                        } elseif ($recalc['winning_team_id'] == $awayTeam->id) {
                            $homeTeam->increment('losses');
                            $awayTeam->increment('wins');
                        } else {
                            $homeTeam->increment('ties');
                            $awayTeam->increment('ties');
                        }
                    }
                }
            }
        });

        $dryLabel = $this->option('dry-run') ? ' (dry run)' : '';
        $this->info("Done{$dryLabel}! {$changed} changed, {$unchanged} unchanged.");
    }
}
