<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\League;
use App\Models\ScoringSetting;
use Illuminate\Http\Request;

class ScoringSettingsController extends Controller
{
    public function index($league_id)
    {
        $league = League::findOrFail($league_id);
        $settingsByType = ScoringSetting::allGroupedByType($league->id);
        $scoringTypes = ScoringSetting::scoringTypes();

        return view('admin.scoring', compact('league', 'settingsByType', 'scoringTypes'));
    }

    public function update(Request $request, $league_id)
    {
        $league = League::findOrFail($league_id);

        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required|numeric|min:0|max:999.99',
        ]);

        foreach ($validated['settings'] as $settingId => $points) {
            ScoringSetting::where('id', $settingId)
                ->where('league_id', $league->id)
                ->update(['points' => $points]);
        }

        return redirect()->route('admin.leagues.scoring', $league->id)
            ->with('success', 'Scoring settings updated successfully!');
    }
}
