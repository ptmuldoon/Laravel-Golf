<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateNineHoleRatings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ratings:populate-nine-hole';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate 9-hole ratings for all existing course info records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Populating 9-hole ratings for all courses...');

        // Get all unique golf course + teebox combinations
        $combinations = DB::table('course_info')
            ->select('golf_course_id', 'teebox', 'slope', 'rating')
            ->groupBy('golf_course_id', 'teebox', 'slope', 'rating')
            ->get();

        foreach ($combinations as $combo) {
            // Get holes for this combination to calculate par
            $holes = DB::table('course_info')
                ->where('golf_course_id', $combo->golf_course_id)
                ->where('teebox', $combo->teebox)
                ->orderBy('hole_number')
                ->pluck('par')
                ->toArray();

            if (count($holes) !== 18) {
                $this->warn("Skipping incomplete course: {$combo->golf_course_id} - {$combo->teebox}");
                continue;
            }

            // Calculate front 9 and back 9 par
            $frontNinePar = array_sum(array_slice($holes, 0, 9));
            $backNinePar = array_sum(array_slice($holes, 9, 9));

            // Calculate 9-hole ratings (proportional to par)
            $rating9Front = round(($combo->rating * $frontNinePar) / 36, 1);
            $rating9Back = round(($combo->rating * $backNinePar) / 36, 1);

            // 9-hole slopes are typically similar to 18-hole but slightly adjusted
            $slope9Front = $combo->slope - 1.0;
            $slope9Back = $combo->slope + 1.0;

            // Update all records for this combination
            DB::table('course_info')
                ->where('golf_course_id', $combo->golf_course_id)
                ->where('teebox', $combo->teebox)
                ->update([
                    'slope_9_front' => $slope9Front,
                    'slope_9_back' => $slope9Back,
                    'rating_9_front' => $rating9Front,
                    'rating_9_back' => $rating9Back,
                ]);

            $courseName = DB::table('golf_courses')->where('id', $combo->golf_course_id)->value('name');
            $this->info("✓ {$courseName} ({$combo->teebox}): Front 9: {$rating9Front}/{$slope9Front}, Back 9: {$rating9Back}/{$slope9Back}");
        }

        $this->info('9-hole rating population completed!');
    }
}
