<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlayerRoundsSeeder extends Seeder
{
    /**
     * Player skill tiers keyed by player ID.
     * - 'low'  = handicap 4-8   (5 players)
     * - 'mid'  = handicap 9-18  (10 players)
     * - 'high' = handicap 20-30 (remaining 17 players)
     */
    private array $playerTiers = [];

    public function run(): void
    {
        $teeboxes = ['Black', 'Blue', 'White', 'Red'];

        $players = DB::table('players')->get();
        $golfCourses = DB::table('golf_courses')->pluck('id')->toArray();

        $this->assignPlayerTiers($players);

        foreach ($players as $player) {
            $tier = $this->playerTiers[$player->id];

            for ($roundNum = 1; $roundNum <= 15; $roundNum++) {
                $courseId = $golfCourses[array_rand($golfCourses)];
                $teebox = $teeboxes[array_rand($teeboxes)];
                $playedAt = now()->subDays(rand(1, 365))->format('Y-m-d');

                $roundId = DB::table('rounds')->insertGetId([
                    'player_id' => $player->id,
                    'golf_course_id' => $courseId,
                    'teebox' => $teebox,
                    'holes_played' => 18,
                    'played_at' => $playedAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $courseHoles = DB::table('course_info')
                    ->where('golf_course_id', $courseId)
                    ->where('teebox', $teebox)
                    ->whereBetween('hole_number', [1, 18])
                    ->orderBy('hole_number')
                    ->get();

                foreach ($courseHoles as $hole) {
                    DB::table('scores')->insert([
                        'round_id' => $roundId,
                        'hole_number' => $hole->hole_number,
                        'strokes' => $this->generateScore($hole->par, $tier, $hole->handicap ?? null),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Assign each player to a skill tier.
     * First 5 = low, next 10 = mid, remaining = high.
     */
    private function assignPlayerTiers($players): void
    {
        $sorted = $players->sortBy('id')->values();

        foreach ($sorted as $index => $player) {
            if ($index < 5) {
                $this->playerTiers[$player->id] = 'low';
            } elseif ($index < 15) {
                $this->playerTiers[$player->id] = 'mid';
            } else {
                $this->playerTiers[$player->id] = 'high';
            }
        }
    }

    /**
     * Generate a realistic hole score based on par, skill tier, and hole difficulty.
     *
     * Low  (handicap 4-8):  ~+0.33/hole → ~6 over par per round
     * Mid  (handicap 9-18): ~+0.78/hole → ~14 over par per round
     * High (handicap 20-30): ~+1.44/hole → ~26 over par per round
     *
     * Hole handicap adjusts difficulty: harder holes (low handicap rank)
     * shift the distribution slightly worse.
     */
    private function generateScore(int $par, string $tier, ?int $holeHandicap): int
    {
        $rand = rand(1, 100);

        // Difficulty modifier: harder holes (handicap 1-6) shift worse,
        // easier holes (handicap 13-18) shift better
        $difficultyShift = 0;
        if ($holeHandicap !== null) {
            if ($holeHandicap <= 6) {
                $difficultyShift = rand(0, 1); // occasionally add a stroke on hard holes
            } elseif ($holeHandicap >= 13) {
                $difficultyShift = -rand(0, 1); // occasionally save a stroke on easy holes
            }
        }

        $score = match ($tier) {
            'low' => $this->generateLowHandicapScore($par, $rand),
            'mid' => $this->generateMidHandicapScore($par, $rand),
            'high' => $this->generateHighHandicapScore($par, $rand),
        };

        // Apply difficulty shift but never go below 1
        return max(1, $score + $difficultyShift);
    }

    /**
     * Low handicap (4-8): Mostly pars and bogeys with occasional birdies.
     * Expected: ~+0.75/hole → ~13.5 over par raw (after WHS cap → handicap 4-8)
     */
    private function generateLowHandicapScore(int $par, int $rand): int
    {
        if ($rand <= 1) return max(1, $par - 2);   // Eagle 1%
        if ($rand <= 8) return $par - 1;            // Birdie 7%
        if ($rand <= 45) return $par;               // Par 37%
        if ($rand <= 78) return $par + 1;           // Bogey 33%
        if ($rand <= 94) return $par + 2;           // Double 16%
        if ($rand <= 99) return $par + 3;           // Triple 5%
        return $par + 4;                             // Quad 1%
    }

    /**
     * Mid handicap (9-18): Bogeys and doubles with some pars.
     * Expected: ~+1.44/hole → ~26 over par raw (after WHS cap → handicap 9-18)
     */
    private function generateMidHandicapScore(int $par, int $rand): int
    {
        if ($rand <= 3) return $par - 1;            // Birdie 3%
        if ($rand <= 23) return $par;               // Par 20%
        if ($rand <= 56) return $par + 1;           // Bogey 33%
        if ($rand <= 81) return $par + 2;           // Double 25%
        if ($rand <= 94) return $par + 3;           // Triple 13%
        if ($rand <= 99) return $par + 4;           // Quad 5%
        return $par + 5;                             // Quad+ 1%
    }

    /**
     * High handicap (20-30): Doubles, triples and worse with few pars.
     * Expected: ~+2.32/hole → ~42 over par raw (after WHS cap → handicap 20-30)
     */
    private function generateHighHandicapScore(int $par, int $rand): int
    {
        if ($rand <= 8) return $par;                // Par 8%
        if ($rand <= 29) return $par + 1;           // Bogey 21%
        if ($rand <= 58) return $par + 2;           // Double 29%
        if ($rand <= 80) return $par + 3;           // Triple 22%
        if ($rand <= 93) return $par + 4;           // Quad 13%
        return $par + 5;                             // Quad+ 7%
    }
}
