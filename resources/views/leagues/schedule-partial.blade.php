<div class="content-section">
    @if($matchesByWeek->isEmpty())
        <div style="text-align: center; padding: 40px; color: #888;">No matches scheduled yet.</div>
    @else
        <div style="background: #e8f4f8; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; color: #0c5460; font-size: 0.9em;">
            <strong>Schedule Summary:</strong>
            Total Weeks: <strong>{{ $matchesByWeek->count() }}</strong> |
            Total Matches: <strong>{{ $totalMatches }}</strong> |
            Completed: <strong>{{ $completedMatches }}</strong>
        </div>

        @foreach($matchesByWeek as $weekNumber => $matches)
            @php
                $firstMatch = $matches->first();
                $isCompleted = $matches->every(fn($m) => $m->status === 'completed');
                $weekHomeTeamName = $firstMatch->homeTeam->name ?? null;
                $weekAwayTeamName = $firstMatch->awayTeam->name ?? null;
            @endphp
            <div style="border: 1px solid #e0e0e0; border-radius: 10px; margin-bottom: 15px; overflow: hidden;">
                <div onclick="toggleScheduleWeek({{ $weekNumber }}, {{ $league->id }})" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; background: {{ $isCompleted ? '#f0fdf4' : 'var(--primary-light)' }}; cursor: pointer; user-select: none;" onmouseover="this.style.background='{{ $isCompleted ? '#dcfce7' : '#eef0ff' }}'" onmouseout="this.style.background='{{ $isCompleted ? '#f0fdf4' : 'var(--primary-light)' }}'">
                    <h3 style="color: var(--primary-color); font-size: 1.15em; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span id="sched-arrow-{{ $league->id }}-{{ $weekNumber }}" style="display: inline-block; transition: transform 0.2s ease; font-size: 0.7em;">&#9654;</span>
                        Week {{ $weekNumber }}
                    </h3>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 0.85em; color: #666;">
                        @if($firstMatch->match_date)
                            <span>{{ $firstMatch->match_date->format('M d, Y') }}</span>
                        @endif
                        @if($firstMatch->golfCourse)
                            <span style="color: #999;">&bull;</span>
                            <span>{{ $firstMatch->golfCourse->name }}</span>
                        @endif
                        <span style="padding: 2px 10px; border-radius: 10px; font-size: 0.85em; font-weight: 600;
                            background: {{ $isCompleted ? '#d4edda' : '#cce5ff' }};
                            color: {{ $isCompleted ? '#155724' : '#004085' }};">
                            {{ $isCompleted ? 'Completed' : ($matches->count() . ' ' . ($matches->count() == 1 ? 'match' : 'matches')) }}
                        </span>
                    </div>
                </div>

                <div id="sched-week-{{ $league->id }}-{{ $weekNumber }}" style="display: none; padding: 10px 18px 14px;">
                    <div style="font-size: 0.85em; color: #888; margin-bottom: 10px;">
                        {{ $firstMatch->holes === 'back_9' ? 'Back 9' : 'Front 9' }}
                        @if($firstMatch->scoring_type)
                            &bull; {{ \App\Models\ScoringSetting::scoringTypes()[$firstMatch->scoring_type] ?? ucfirst(str_replace('_', ' ', $firstMatch->scoring_type)) }}
                        @endif
                    </div>

                    {{-- Team name header --}}
                    @if($weekAwayTeamName || $weekHomeTeamName)
                        <div style="display: flex; align-items: center; gap: 8px; padding: 4px 0 8px; margin-left: 60px;">
                            <div style="flex: 1; text-align: center;">
                                <span style="font-size: 0.85em; color: #dc3545; font-weight: 700;">{{ $weekAwayTeamName ?? '' }}</span>
                            </div>
                            <span style="color: transparent; flex-shrink: 0;">vs</span>
                            <div style="flex: 1; text-align: center;">
                                <span style="font-size: 0.85em; color: #28a745; font-weight: 700;">{{ $weekHomeTeamName ?? '' }}</span>
                            </div>
                        </div>
                    @endif

                    @foreach($matches as $index => $match)
                        @php
                            $homePlayers = $match->matchPlayers->where('position_in_pairing', '<=', 2)->sortBy('position_in_pairing');
                            $awayPlayers = $match->matchPlayers->where('position_in_pairing', '>', 2)->sortBy('position_in_pairing');
                            $shortName = function($mp) {
                                $player = $mp->substitute_player_id ? $mp->substitutePlayer : $mp->player;
                                if ($player && $player->first_name && $player->last_name) {
                                    return $player->name;
                                }
                                return $player ? $player->name : ($mp->substitute_name ?? '');
                            };
                        @endphp
                        <div style="display: flex; align-items: center; gap: 8px; padding: 6px 0; {{ !$loop->last ? 'border-bottom: 1px solid #f0f0f0;' : '' }} font-size: 0.95em;">
                            <span style="color: var(--primary-color); font-weight: 700; min-width: 55px; font-size: 0.9em;">
                                @if($match->tee_time)
                                    {{ \Carbon\Carbon::parse($match->tee_time)->format('g:i A') }}
                                @else
                                    #{{ $index + 1 }}
                                @endif
                            </span>
                            <div style="flex: 1; display: flex; align-items: center; gap: 8px;">
                                {{-- Away team (left side) --}}
                                <div style="flex: 1; text-align: right;">
                                    @foreach($awayPlayers as $mp)
                                        @if(!$loop->first) <span style="color: #dc3545;">&amp;</span> @endif
                                        <span style="color: #dc3545; font-weight: 600;">
                                            {{ $shortName($mp) }}
                                            @if($mp->substitute_player_id)
                                                <span style="font-size: 0.7em; color: #999;">(sub)</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                                <span style="color: #888; font-weight: 600; flex-shrink: 0;">vs</span>
                                {{-- Home team (right side) --}}
                                <div style="flex: 1; text-align: left;">
                                    @foreach($homePlayers as $mp)
                                        @if(!$loop->first) <span style="color: #28a745;">&amp;</span> @endif
                                        <span style="color: #28a745; font-weight: 600;">
                                            {{ $shortName($mp) }}
                                            @if($mp->substitute_player_id)
                                                <span style="font-size: 0.7em; color: #999;">(sub)</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>
