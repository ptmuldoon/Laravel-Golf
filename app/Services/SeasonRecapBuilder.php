<?php

namespace App\Services;

use App\Http\Controllers\Concerns\ComputesIndividualMatchups;
use App\Models\League;
use App\Models\LeagueMatch;
use App\Models\Par3Winner;

/**
 * Season Recap.
 *
 * Assembles the end-of-season story for a league in one pass over its completed
 * matches: final standings, a player leaderboard, season awards and the
 * hole-by-hole summary.
 *
 * Two attribution rules run throughout, matching the rest of the app:
 *
 *  - Points and W-L-T belong to the *roster* player who holds the slot, even in
 *    a week a substitute filled it (mirrors the home page player standings),
 *    and individual match play points come from ComputesIndividualMatchups, so
 *    recap totals are the same numbers players have watched all season.
 *  - Score-based stats (low round, birdies, averages) and attendance belong to
 *    whoever actually played — the substitute when there is one (mirrors Hole
 *    Stats and Player Stats). Scramble weeks are excluded from the scoring
 *    stats, since those scores are the team's ball, not one player's round,
 *    but they still count as a week attended.
 *
 * Rounds are ranked vs. par rather than by raw strokes so a league mixing 9-
 * and 18-hole weeks still compares its low rounds honestly.
 */
class SeasonRecapBuilder
{
    use ComputesIndividualMatchups;

    /** Minimum complete rounds before a player qualifies for average-based awards. */
    private const MIN_ROUNDS_FOR_AVERAGE = 5;

    /** Minimum matches together before a pairing qualifies for Best Duo. */
    private const MIN_MATCHES_FOR_DUO = 3;

    public function build(League $league): array
    {
        $matches = LeagueMatch::where('league_id', $league->id)
            ->where('status', 'completed')
            ->with([
                'result',
                'matchPlayers.player',
                'matchPlayers.substitutePlayer',
                'matchPlayers.scores',
                'golfCourse.courseInfo',
            ])
            ->orderBy('week_number')
            ->orderBy('id')
            ->get();

        if ($matches->isEmpty()) {
            return ['hasData' => false, 'league' => $league];
        }

        $teamColors = $this->teamColors($league);
        $teamNames = $league->teams->pluck('name', 'id')->toArray();
        $playerTeam = $this->playerTeamMap($league);

        $rows = [];         // player_id => accumulating stats row
        $rounds = [];       // every complete, non-scramble round played
        $holes = [];        // hole position => scoring aggregate
        $outcomes = [];     // player_id => [week => win|loss|tie] for streaks
        $mpPoints = [];     // match_player_id => points earned (for pairings)
        $weeksPlayed = [];  // player_id => [week => true] for weeks actually played

        // holeInfo() queries course rows per match, so cache by the shape that
        // determines it — a season replays the same handful of layouts.
        $parCache = [];

        foreach ($matches as $match) {
            $shape = implode('|', [
                $match->golf_course_id,
                $match->teebox,
                $match->holes,
                $match->front_nine_id,
                $match->back_nine_id,
            ]);
            if (!isset($parCache[$shape])) {
                $parCache[$shape] = [];
                foreach ($match->holeInfo() as $position => $info) {
                    $parCache[$shape][$position] = (int) $info->par;
                }
            }
            $parByHole = $parCache[$shape];
            $holeCount = count($parByHole);
            $isScramble = $match->scoring_type === 'scramble';

            foreach ($match->matchPlayers as $mp) {
                // Point each entry back at the match already in memory, so the
                // shared matchup calculation reuses it instead of re-querying
                // the match, its roster and its course for every player.
                $mp->setRelation('match', $match);

                // --- Points, record and attendance: the roster player ---
                $rosterPlayer = $mp->player;
                if ($rosterPlayer) {
                    $id = $this->ensureRow($rows, $rosterPlayer, $playerTeam, $teamNames, $teamColors);
                    $rows[$id]['matches']++;

                    if ($match->result) {
                        [$points, $outcome] = $this->pointsAndOutcome($mp, $match);
                        $mpPoints[$mp->id] = $points;

                        $rows[$id]['points'] += $points;
                        $rows[$id][$outcome === 'win' ? 'wins' : ($outcome === 'loss' ? 'losses' : 'ties')]++;
                        $outcomes[$id][] = $outcome;
                    }

                    // Attendance is about who actually teed off. A roster slot
                    // still scores points for its owner when a substitute fills
                    // it, but it is not a week that player showed up for.
                    if ($mp->has_substitute) {
                        $rows[$id]['weeks_subbed_out']++;
                    } else {
                        $weeksPlayed[$id][$match->week_number] = true;
                    }

                    // Handicap trend only reads weeks the player played himself;
                    // a substituted slot stores the substitute's index.
                    if (!$mp->has_substitute && $mp->handicap_index !== null) {
                        if ($rows[$id]['first_hi'] === null) {
                            $rows[$id]['first_hi'] = (float) $mp->handicap_index;
                            $rows[$id]['first_hi_week'] = $match->week_number;
                        }
                        $rows[$id]['last_hi'] = (float) $mp->handicap_index;
                        $rows[$id]['last_hi_week'] = $match->week_number;
                    }
                }

                // A substitute filling someone else's slot did show up and play,
                // so the week counts for them. Tracked before the scramble skip
                // below, since a scramble week is still a week at the course.
                if ($mp->substitute_player_id && $mp->substitutePlayer) {
                    $subId = $this->ensureRow(
                        $rows, $mp->substitutePlayer, $playerTeam, $teamNames, $teamColors
                    );
                    $weeksPlayed[$subId][$match->week_number] = true;
                }

                // --- Scoring stats: whoever actually swung the club ---
                if ($isScramble || $holeCount === 0) {
                    continue;
                }

                $activePlayer = $mp->substitute_player_id ? $mp->substitutePlayer : $mp->player;
                if (!$activePlayer || $mp->substitute_name) {
                    continue; // guest sub with no player record — nothing to attribute
                }

                $id = $this->ensureRow($rows, $activePlayer, $playerTeam, $teamNames, $teamColors);

                $strokes = 0;
                $net = 0;
                $scoredHoles = 0;
                $roundPar = 0;

                foreach ($mp->scores as $score) {
                    $par = $parByHole[$score->hole_number] ?? null;
                    if ($par === null || !$score->strokes || $score->strokes <= 0) {
                        continue;
                    }

                    $scoredHoles++;
                    $strokes += (int) $score->strokes;
                    $net += (int) ($score->net_score ?? $score->strokes);
                    $roundPar += $par;

                    $category = $this->category((int) $score->strokes - $par);
                    $rows[$id][$category]++;

                    $this->ensureHole($holes, (int) $score->hole_number, $par);
                    $holes[$score->hole_number]['strokes'] += (int) $score->strokes;
                    $holes[$score->hole_number]['played']++;
                    $holes[$score->hole_number][$category === 'par' ? 'par_count' : $category]++;
                }

                // Only complete rounds enter round-level records, so a player who
                // walked in after six holes can't take the low-round trophy.
                if ($scoredHoles > 0 && $scoredHoles === $holeCount) {
                    $round = [
                        'player' => $activePlayer,
                        'week' => $match->week_number,
                        'date' => $match->match_date,
                        'strokes' => $strokes,
                        'net' => $net,
                        'par' => $roundPar,
                        'vs_par' => $strokes - $roundPar,
                        'net_vs_par' => $net - $roundPar,
                        'holes' => $holeCount,
                        'label' => $this->holesLabel($match, $holeCount),
                    ];
                    $rounds[] = $round;

                    $rows[$id]['rounds']++;
                    $rows[$id]['strokes'] += $strokes;
                    $rows[$id]['round_par'] += $roundPar;
                    if ($rows[$id]['low_gross'] === null
                        || $round['vs_par'] < $rows[$id]['low_gross']['vs_par']) {
                        $rows[$id]['low_gross'] = $round;
                    }
                }
            }
        }

        $par3 = Par3Winner::where('league_id', $league->id)
            ->get()
            ->groupBy('player_id')
            ->map->count()
            ->toArray();

        foreach ($rows as $id => $row) {
            $rows[$id]['par3'] = $par3[$id] ?? 0;
            $rows[$id]['weeks_played'] = count($weeksPlayed[$id] ?? []);
            $decided = $row['wins'] + $row['losses'] + $row['ties'];
            $rows[$id]['win_pct'] = $decided > 0
                ? round((($row['wins'] + 0.5 * $row['ties']) / $decided) * 100, 1)
                : null;
            $rows[$id]['avg_vs_par'] = $row['rounds'] > 0
                ? round(($row['strokes'] - $row['round_par']) / $row['rounds'], 2)
                : null;
            $rows[$id]['avg_strokes'] = $row['rounds'] > 0
                ? round($row['strokes'] / $row['rounds'], 1)
                : null;
            $rows[$id]['birdies'] = $row['birdie'] + $row['eagle'] + $row['albatross'];
            // Only a real trend counts: a player seen in a single week has no delta.
            $rows[$id]['hi_delta'] = ($row['first_hi'] !== null && $row['last_hi'] !== null
                    && $row['first_hi_week'] !== $row['last_hi_week'])
                ? round($row['first_hi'] - $row['last_hi'], 1)
                : null;
        }

        $players = collect($rows)
            ->sortByDesc(fn($r) => [$r['points'], $r['wins']])
            ->values();

        // Roster players only for the standings table; substitutes who never held
        // a slot have no points and would just be noise there (their scores still
        // count toward the awards above).
        $standings = $players->filter(fn($r) => $r['matches'] > 0)->values();

        $weeks = $matches->pluck('week_number')->unique()->sort()->values();
        $duos = $this->pairings($matches, $mpPoints);

        return [
            'hasData' => true,
            'league' => $league,
            'overview' => $this->overview($matches, $weeks, $standings, $rounds, $holes),
            'teamGroups' => $this->teamGroups($league, $matches, $teamColors),
            'players' => $standings,
            'podium' => $standings->take(3),
            'awards' => $this->awards($players, $standings, $rounds, $duos, $holes, $outcomes, $weeks->count()),
            'weeklyBest' => $this->weeklyBest($rounds, $weeks, $matches),
            'holes' => $this->holeSummary($holes),
        ];
    }

    /**
     * Points earned and win/loss/tie for one player in one match. Individual
     * match play is decided by the player's own head-to-head matchup; every
     * other format inherits the team's result.
     *
     * @return array{0: float, 1: string}
     */
    private function pointsAndOutcome($mp, LeagueMatch $match): array
    {
        $result = $match->result;
        $isHome = $mp->team_id == $match->home_team_id;

        if ($match->scoring_type === 'individual_match_play') {
            // Resolving the matchup re-reads the course, so do it once and map
            // the result the same way getIndividualPlayerPoints() does rather
            // than paying for the whole calculation twice.
            $outcome = $this->getIndividualMatchupResult($mp);
            $points = $outcome === 'win' ? 1.0 : ($outcome === 'loss' ? 0.0 : 0.5);

            return [$points, $outcome];
        }

        $points = $isHome
            ? ($result->team_points_home ?? 0)
            : ($result->team_points_away ?? 0);

        if ($result->winning_team_id === null) {
            return [$points, 'tie'];
        }

        $won = ($isHome && $result->winning_team_id == $match->home_team_id)
            || (!$isHome && $result->winning_team_id == $match->away_team_id);

        return [$points, $won ? 'win' : 'loss'];
    }

    /**
     * Aggregate every two-player pairing across the season. A pairing's points
     * are its two players' own points added together, so the number means the
     * same thing here as it does in the player standings.
     */
    private function pairings($matches, array $mpPoints): array
    {
        $duos = [];

        foreach ($matches as $match) {
            if (!$match->result) continue;

            foreach ($match->matchPlayers->groupBy('team_id') as $teamId => $side) {
                if ($side->count() !== 2) continue;

                $a = $side->first()->player;
                $b = $side->last()->player;
                if (!$a || !$b || $a->id === $b->id) continue;

                $ids = [$a->id, $b->id];
                sort($ids);
                $key = implode('-', $ids);

                if (!isset($duos[$key])) {
                    $duos[$key] = [
                        'players' => $a->id < $b->id ? [$a, $b] : [$b, $a],
                        'matches' => 0,
                        'points' => 0.0,
                        'wins' => 0,
                        'losses' => 0,
                        'ties' => 0,
                    ];
                }

                $duos[$key]['matches']++;
                foreach ($side as $mp) {
                    $duos[$key]['points'] += $mpPoints[$mp->id] ?? 0;
                }

                if ($match->result->winning_team_id === null) {
                    $duos[$key]['ties']++;
                } elseif ($match->result->winning_team_id == $teamId) {
                    $duos[$key]['wins']++;
                } else {
                    $duos[$key]['losses']++;
                }
            }
        }

        return $duos;
    }

    private function ensureRow(array &$rows, $player, array $playerTeam, array $teamNames, array $teamColors): int
    {
        if (!isset($rows[$player->id])) {
            $teamId = $playerTeam[$player->id] ?? null;
            $rows[$player->id] = [
                'player' => $player,
                'team_id' => $teamId,
                'team_name' => $teamId ? ($teamNames[$teamId] ?? null) : null,
                'team_color' => $teamId ? ($teamColors[$teamId] ?? null) : null,
                'matches' => 0,
                'weeks_played' => 0,
                'weeks_subbed_out' => 0,
                'points' => 0.0,
                'wins' => 0,
                'losses' => 0,
                'ties' => 0,
                'win_pct' => null,
                'rounds' => 0,
                'strokes' => 0,
                'round_par' => 0,
                'avg_vs_par' => null,
                'avg_strokes' => null,
                'low_gross' => null,
                'albatross' => 0,
                'eagle' => 0,
                'birdie' => 0,
                'birdies' => 0,
                'par' => 0,
                'bogey' => 0,
                'double' => 0,
                'triple_plus' => 0,
                'first_hi' => null,
                'first_hi_week' => null,
                'last_hi' => null,
                'last_hi_week' => null,
                'hi_delta' => null,
                'par3' => 0,
            ];
        }

        return $player->id;
    }

    private function ensureHole(array &$holes, int $position, int $par): void
    {
        if (!isset($holes[$position])) {
            $holes[$position] = [
                'hole' => $position,
                'par' => $par,
                'played' => 0,
                'strokes' => 0,
                'albatross' => 0,
                'eagle' => 0,
                'birdie' => 0,
                'par_count' => 0,
                'bogey' => 0,
                'double' => 0,
                'triple_plus' => 0,
            ];
        }
    }

    /** Score relative to par -> category key used on both player and hole rows. */
    private function category(int $diff): string
    {
        if ($diff <= -3) return 'albatross';
        if ($diff === -2) return 'eagle';
        if ($diff === -1) return 'birdie';
        if ($diff === 0) return 'par';
        if ($diff === 1) return 'bogey';
        if ($diff === 2) return 'double';
        return 'triple_plus';
    }

    private function overview($matches, $weeks, $standings, array $rounds, array $holes): array
    {
        $birdies = 0;
        $eagles = 0;
        $pars = 0;
        $holesPlayed = 0;
        foreach ($holes as $hole) {
            $birdies += $hole['birdie'] + $hole['eagle'] + $hole['albatross'];
            $eagles += $hole['eagle'] + $hole['albatross'];
            $pars += $hole['par_count'];
            $holesPlayed += $hole['played'];
        }

        return [
            'weeks' => $weeks->count(),
            'first_week' => $weeks->first(),
            'last_week' => $weeks->last(),
            'start_date' => $matches->first()->match_date,
            'end_date' => $matches->last()->match_date,
            'matches' => $matches->count(),
            'players' => $standings->count(),
            'rounds' => count($rounds),
            'holes_played' => $holesPlayed,
            'birdies' => $birdies,
            'eagles' => $eagles,
            'pars' => $pars,
        ];
    }

    /**
     * Final standings, grouped by segment when the league runs them. Teams keep
     * the home page's ordering (wins, then ties) so the recap crowns the same
     * champion players have seen in the standings all season.
     */
    private function teamGroups(League $league, $matches, array $teamColors): array
    {
        $points = [];
        foreach ($matches as $match) {
            if (!$match->result) continue;
            foreach ([
                [$match->home_team_id, $match->result->team_points_home ?? 0],
                [$match->away_team_id, $match->result->team_points_away ?? 0],
            ] as [$teamId, $teamPoints]) {
                if ($teamId) {
                    $points[$teamId] = ($points[$teamId] ?? 0) + $teamPoints;
                }
            }
        }

        $build = fn($teams) => $teams
            ->sortByDesc(fn($t) => [$t->wins, $t->ties])
            ->values()
            ->map(fn($team) => [
                'team' => $team,
                'color' => $teamColors[$team->id] ?? null,
                'points' => $points[$team->id] ?? 0,
                'win_pct' => $team->winPercentage(),
            ]);

        if ($league->segments->isEmpty()) {
            return [['segment' => null, 'teams' => $build($league->teams)]];
        }

        $groups = [];
        foreach ($league->segments as $segment) {
            if ($segment->teams->isEmpty()) continue;
            $groups[] = ['segment' => $segment, 'teams' => $build($segment->teams)];
        }

        return $groups;
    }

    /**
     * The award cards. Each is either null (not enough data to name a winner)
     * or an array holding the winner plus whatever that award is about.
     */
    private function awards($players, $standings, array $rounds, array $duos, array $holes, array $outcomes, int $seasonWeeks): array
    {
        $roundsCollection = collect($rounds);

        $lowGross = $roundsCollection->sortBy(fn($r) => [$r['vs_par'], $r['strokes']])->first();
        $lowNet = $roundsCollection->sortBy(fn($r) => [$r['net_vs_par'], $r['net']])->first();

        $mostImproved = $players
            ->filter(fn($p) => $p['hi_delta'] !== null && $p['hi_delta'] > 0)
            ->sortByDesc('hi_delta')
            ->first();

        $birdieKing = $players
            ->filter(fn($p) => $p['birdies'] > 0)
            ->sortByDesc('birdies')
            ->first();

        $bestAverage = $players
            ->filter(fn($p) => $p['rounds'] >= self::MIN_ROUNDS_FOR_AVERAGE)
            ->sortBy('avg_vs_par')
            ->first();

        // Attendance counts weeks a player actually teed off — a slot filled by
        // a substitute still scores for its owner, but they were not there. It
        // is usually a shared honour, so the card reports everyone who tops it.
        $mostWeeks = $standings->max('weeks_played') ?? 0;
        $best = $standings->where('weeks_played', $mostWeeks);
        $attendance = $mostWeeks > 0 ? [
            'weeks' => $mostWeeks,
            'season_weeks' => $seasonWeeks,
            'perfect' => $mostWeeks >= $seasonWeeks,
            'players' => $best->pluck('player'),
            'count' => $best->count(),
        ] : null;

        $par3King = $players->filter(fn($p) => $p['par3'] > 0)->sortByDesc('par3')->first();

        $hotStreak = $this->longestWinStreak($standings, $outcomes);

        $bestDuo = collect($duos)
            ->filter(fn($d) => $d['matches'] >= self::MIN_MATCHES_FOR_DUO)
            ->map(function ($duo) {
                $duo['ppm'] = round($duo['points'] / $duo['matches'], 3);
                return $duo;
            })
            ->sortByDesc(fn($d) => [$d['ppm'], $d['matches']])
            ->first();

        $holeRows = collect($this->holeSummary($holes));

        return [
            'champion' => $standings->first(),
            'runners_up' => $standings->slice(1, 2)->values(),
            'low_gross' => $lowGross,
            'low_net' => $lowNet,
            'most_improved' => $mostImproved,
            'birdie_king' => $birdieKing,
            'best_average' => $bestAverage,
            'hot_streak' => $hotStreak,
            'attendance' => $attendance,
            'par3_king' => $par3King,
            'best_duo' => $bestDuo,
            'toughest_hole' => $holeRows->sortByDesc('avg_vs_par')->first(),
            'easiest_hole' => $holeRows->sortBy('avg_vs_par')->first(),
            'min_rounds' => self::MIN_ROUNDS_FOR_AVERAGE,
            'min_duo_matches' => self::MIN_MATCHES_FOR_DUO,
        ];
    }

    /**
     * Longest run of consecutive matches won, in week order. Ties are broken by
     * season wins so the streak card names one player rather than whoever the
     * sort happened to reach first.
     */
    private function longestWinStreak($standings, array $outcomes): ?array
    {
        $best = null;

        foreach ($standings as $row) {
            $sequence = $outcomes[$row['player']->id] ?? [];
            $longest = 0;
            $current = 0;
            foreach ($sequence as $outcome) {
                $current = $outcome === 'win' ? $current + 1 : 0;
                $longest = max($longest, $current);
            }

            if ($longest < 2) {
                continue;
            }

            if ($best === null
                || $longest > $best['streak']
                || ($longest === $best['streak'] && $row['wins'] > $best['wins'])) {
                $best = [
                    'player' => $row['player'],
                    'streak' => $longest,
                    'wins' => $row['wins'],
                ];
            }
        }

        return $best;
    }

    /**
     * The best round of each week — the season told week by week. Weeks with no
     * individual rounds (scrambles) still get a row so the table reads as a
     * complete season rather than one with holes in it.
     */
    private function weeklyBest(array $rounds, $weeks, $matches): array
    {
        $byWeek = collect($rounds)->groupBy('week');
        $matchesByWeek = $matches->groupBy('week_number');

        $out = [];
        foreach ($weeks as $week) {
            $weekMatches = $matchesByWeek->get($week) ?? collect();
            $weekRounds = $byWeek->get($week);

            $out[] = [
                'week' => $week,
                'date' => $weekMatches->first()->match_date ?? null,
                'round' => $weekRounds
                    ? $weekRounds->sortBy(fn($r) => [$r['vs_par'], $r['strokes']])->first()
                    : null,
                'format' => $this->formatLabel($weekMatches),
            ];
        }

        return $out;
    }

    /** Human label for the format a week was played in, when it is all one format. */
    private function formatLabel($weekMatches): ?string
    {
        $types = $weekMatches->pluck('scoring_type')->unique()->filter()->values();
        if ($types->count() !== 1) {
            return null;
        }

        return match ($types->first()) {
            'individual_match_play' => 'Individual',
            'best_ball_match_play' => 'Best Ball',
            'team_2ball_match_play' => 'Team 2-Ball',
            'scramble' => 'Scramble',
            'stableford' => 'Stableford',
            default => null,
        };
    }

    private function holeSummary(array $holes): array
    {
        ksort($holes);

        return collect($holes)
            ->filter(fn($h) => $h['played'] > 0)
            ->map(function ($hole) {
                $hole['avg'] = round($hole['strokes'] / $hole['played'], 2);
                $hole['avg_vs_par'] = round($hole['avg'] - $hole['par'], 2);
                $hole['birdie_or_better'] = $hole['birdie'] + $hole['eagle'] + $hole['albatross'];
                return $hole;
            })
            ->values()
            ->all();
    }

    private function playerTeamMap(League $league): array
    {
        $map = [];
        foreach ($league->teams as $team) {
            foreach ($team->players as $player) {
                $map[$player->id] = $team->id;
            }
        }

        return $map;
    }

    /**
     * Team id -> display color, matching the home page: the admin-picked color,
     * else red/blue by team order within each segment.
     */
    private function teamColors(League $league): array
    {
        $fallback = ['#dc3545', '#2563eb'];
        $map = [];
        foreach ($league->teams->groupBy('league_segment_id') as $group) {
            foreach ($group->sortBy('id')->values() as $i => $team) {
                $map[$team->id] = $team->color ?: ($fallback[$i] ?? null);
            }
        }

        return $map;
    }

    private function holesLabel(LeagueMatch $match, int $holeCount): string
    {
        if ($holeCount >= 18) {
            return '18 holes';
        }

        return $match->holes === 'back_9' ? 'Back 9' : 'Front 9';
    }
}
