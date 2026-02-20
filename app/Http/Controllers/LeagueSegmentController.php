<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\LeagueSegment;
use Illuminate\Http\Request;

class LeagueSegmentController extends Controller
{
    public function index($leagueId)
    {
        $league = League::with('segments')->findOrFail($leagueId);

        // Get the max week number from the schedule
        $maxWeek = $league->matches()->max('week_number') ?? 0;

        return view('leagues.segments', compact('league', 'maxWeek'));
    }

    public function store(Request $request, $leagueId)
    {
        $league = League::findOrFail($leagueId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_week' => 'required|integer|min:1',
            'end_week' => 'required|integer|gte:start_week',
        ]);

        // Check for unique name within league
        if ($league->segments()->where('name', $validated['name'])->exists()) {
            return back()->withErrors(['name' => 'A segment with this name already exists.'])->withInput();
        }

        // Check for overlapping week ranges
        $overlap = $league->segments()
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_week', [$validated['start_week'], $validated['end_week']])
                  ->orWhereBetween('end_week', [$validated['start_week'], $validated['end_week']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('start_week', '<=', $validated['start_week'])
                         ->where('end_week', '>=', $validated['end_week']);
                  });
            })
            ->first();

        if ($overlap) {
            return back()->withErrors(['start_week' => "Weeks overlap with segment '{$overlap->name}' (weeks {$overlap->start_week}-{$overlap->end_week})."])->withInput();
        }

        $displayOrder = $league->segments()->max('display_order') + 1;

        LeagueSegment::create([
            'league_id' => $league->id,
            'name' => $validated['name'],
            'start_week' => $validated['start_week'],
            'end_week' => $validated['end_week'],
            'display_order' => $displayOrder,
        ]);

        return redirect()->route('admin.leagues.segments.index', $leagueId)
            ->with('success', "Segment '{$validated['name']}' created successfully!");
    }

    public function update(Request $request, $id)
    {
        $segment = LeagueSegment::findOrFail($id);
        $league = $segment->league;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_week' => 'required|integer|min:1',
            'end_week' => 'required|integer|gte:start_week',
        ]);

        // Check for unique name within league (excluding this segment)
        if ($league->segments()->where('name', $validated['name'])->where('id', '!=', $segment->id)->exists()) {
            return back()->withErrors(['name' => 'A segment with this name already exists.'])->withInput();
        }

        // Check for overlapping week ranges (excluding this segment)
        $overlap = $league->segments()
            ->where('id', '!=', $segment->id)
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_week', [$validated['start_week'], $validated['end_week']])
                  ->orWhereBetween('end_week', [$validated['start_week'], $validated['end_week']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('start_week', '<=', $validated['start_week'])
                         ->where('end_week', '>=', $validated['end_week']);
                  });
            })
            ->first();

        if ($overlap) {
            return back()->withErrors(['start_week' => "Weeks overlap with segment '{$overlap->name}' (weeks {$overlap->start_week}-{$overlap->end_week})."])->withInput();
        }

        $segment->update($validated);

        return redirect()->route('admin.leagues.segments.index', $league->id)
            ->with('success', "Segment '{$validated['name']}' updated successfully!");
    }

    public function destroy($id)
    {
        $segment = LeagueSegment::findOrFail($id);
        $leagueId = $segment->league_id;
        $name = $segment->name;
        $teamCount = $segment->teams()->count();

        $segment->delete();

        $msg = "Segment '{$name}' deleted successfully!";
        if ($teamCount > 0) {
            $msg .= " ({$teamCount} team(s) removed.)";
        }

        return redirect()->route('admin.leagues.segments.index', $leagueId)
            ->with('success', $msg);
    }
}
