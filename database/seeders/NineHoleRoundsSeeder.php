<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NineHoleRoundsSeeder extends Seeder
{
    /**
     * Player skill tiers keyed by player ID.
     */
    private array $playerTiers = [];

    public function run(): void
    {
        $players = DB::table('players')->get();
        $courses = DB::table('golf_courses')->pluck('id');
        $teeboxes = ['Black', 'Blue', 'White', 'Red'];

        $this->assignPlayerTiers($players);

        foreach ($players as $player) {
            $tier = $this->playerTiers[$player->id];

            for ($i = 0; $i < 15; $i++) {
                $courseId = $courses->random();
                $teebox = $teeboxes[array_rand($teeboxes)];
                $daysAgo = rand(1, 365);
                $playedAt = now()->subDays($daysAgo)->format('Y-m-d');

                // Alternate front and back 9
                $isFrontNine = ($i % 2 === 0);
                $holeStart = $isFrontNine ? 1 : 10;
                $holeEnd = $isFrontNine ? 9 : 18;

                $roundId = DB::table('rounds')->insertGetId([
                    'player_id' => $player->id,
                    'golf_course_id' => $courseId,
                    'teebox' => $teebox,
                    'holes_played' => 9,
                    'played_at' => $playedAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $courseHoles = DB::table('course_info')
                    ->where('golf_course_id', $courseId)
                    ->where('teebox', $teebox)
                    ->whereBetween('hole_number', [$holeStart, $holeEnd])
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
     */
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
            'low' => $this->generateLowHandicapScore($par, $rand),
            'mid' => $this->generateMidHandicapScore($par, $rand),
            'high' => $this->generateHighHandicapScore($par, $rand),
        };

        return max(1, $score + $difficultyShift);
    }

    private function generateLowHandicapScore(int $par, int $rand): int
    {
        if ($rand <= 1) return max(1, $par - 2);
        if ($rand <= 8) return $par - 1;
        if ($rand <= 45) return $par;
        if ($rand <= 78) return $par + 1;
        if ($rand <= 94) return $par + 2;
        if ($rand <= 99) return $par + 3;
        return $par + 4;
    }

    private function generateMidHandicapScore(int $par, int $rand): int
    {
        if ($rand <= 3) return $par - 1;
        if ($rand <= 23) return $par;
        if ($rand <= 56) return $par + 1;
        if ($rand <= 81) return $par + 2;
        if ($rand <= 94) return $par + 3;
        if ($rand <= 99) return $par + 4;
        return $par + 5;
    }

    private function generateHighHandicapScore(int $par, int $rand): int
    {
        if ($rand <= 8) return $par;
        if ($rand <= 29) return $par + 1;
        if ($rand <= 58) return $par + 2;
        if ($rand <= 80) return $par + 3;
        if ($rand <= 93) return $par + 4;
        return $par + 5;
    }
}
