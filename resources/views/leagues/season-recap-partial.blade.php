<style>
    /* Season Recap */
    .sr-hero { text-align: center; }
    .sr-hero-title { font-size: 2.2em; color: var(--primary-color); font-weight: 700; line-height: 1.1; }
    .sr-hero-sub { color: #666; margin-top: 8px; font-size: 1.05em; }
    .sr-stat-strip {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 12px; margin-top: 24px;
    }
    .sr-stat {
        background: var(--primary-light); border-radius: 10px; padding: 14px 10px;
    }
    .sr-stat-value { font-size: 1.8em; font-weight: 700; color: var(--primary-color); line-height: 1; }
    .sr-stat-label {
        font-size: 0.75em; text-transform: uppercase; letter-spacing: 0.06em;
        color: #666; margin-top: 6px; font-weight: 600;
    }
    .sr-champ-banner {
        display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
        background: linear-gradient(135deg, #fff9e6 0%, #fff 70%);
        border: 2px solid #f0d98c; border-radius: 10px;
        padding: 16px 20px; margin-bottom: 16px;
    }
    .sr-champ-trophy { font-size: 2.4em; line-height: 1; }
    .sr-champ-label {
        font-size: 0.75em; text-transform: uppercase; letter-spacing: 0.08em;
        color: #8a6d3b; font-weight: 700;
    }
    .sr-champ-name { font-size: 1.5em; font-weight: 700; line-height: 1.2; }
    .sr-champ-meta { color: #666; font-size: 0.9em; margin-top: 2px; }
    .sr-podium {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }
    .sr-podium-card {
        border: 2px solid #eee; border-radius: 12px; padding: 18px; text-align: center;
        background: white;
    }
    .sr-podium-card.sr-gold { border-color: #f0d98c; background: linear-gradient(180deg, #fff9e6 0%, #fff 60%); }
    .sr-podium-card.sr-silver { border-color: #d8dce2; background: linear-gradient(180deg, #f6f8fa 0%, #fff 60%); }
    .sr-podium-card.sr-bronze { border-color: #e6c9a8; background: linear-gradient(180deg, #fdf3e9 0%, #fff 60%); }
    .sr-podium-medal { font-size: 2.2em; line-height: 1; }
    .sr-podium-name { font-size: 1.2em; font-weight: 700; margin-top: 6px; }
    .sr-podium-team { color: #666; font-size: 0.85em; margin-top: 2px; }
    .sr-podium-points { font-size: 1.9em; font-weight: 700; color: var(--primary-color); margin-top: 10px; line-height: 1; }
    .sr-podium-record { color: #666; font-size: 0.85em; margin-top: 4px; }
    .sr-awards {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 14px;
    }
    .sr-award {
        border: 1px solid #ececf2; border-left: 4px solid var(--primary-color);
        border-radius: 10px; padding: 14px 16px; background: #fcfcfd;
    }
    .sr-award-label {
        font-size: 0.72em; text-transform: uppercase; letter-spacing: 0.07em;
        color: #777; font-weight: 700;
    }
    .sr-award-winner {
        font-size: 1.15em; font-weight: 700; color: var(--primary-color);
        margin-top: 6px; line-height: 1.25;
    }
    .sr-award-value { font-size: 1.5em; font-weight: 700; margin-top: 4px; line-height: 1.1; }
    .sr-award-note { color: #777; font-size: 0.82em; margin-top: 5px; line-height: 1.4; }
    .sr-good { color: #28a745; }
    .sr-bad { color: #dc3545; }
    .sr-muted-row td { color: #999; font-style: italic; }
    .sr-footnote { color: #888; font-size: 0.82em; margin-top: 14px; line-height: 1.5; }
    @media (max-width: 768px) {
        .sr-hero-title { font-size: 1.6em; }
        .sr-stat-value { font-size: 1.4em; }
        .sr-champ-name { font-size: 1.2em; }
    }
</style>

@if(empty($hasData))
    <div class="content-section">
        <h2 class="section-title">Season Recap</h2>
        <div class="empty-state">No completed matches yet — the recap appears once results are in.</div>
    </div>
@else
    @php
        $o = $overview;
        $a = $awards;
        $signed = fn($n) => ($n > 0 ? '+' : ($n == 0 ? 'E' : '')) . ($n == 0 ? '' : number_format($n, abs($n - (int) $n) > 0.001 ? 2 : 0));
    @endphp

    {{-- Hero --}}
    <div class="content-section sr-hero">
        <div class="sr-hero-title">{{ $league->name }} Season Recap</div>
        <div class="sr-hero-sub">
            {{ $league->season }}
            @if($o['start_date'] && $o['end_date'])
                &middot; {{ $o['start_date']->format('M j') }} – {{ $o['end_date']->format('M j, Y') }}
            @endif
            &middot; Weeks {{ $o['first_week'] }}–{{ $o['last_week'] }}
        </div>

        <div class="sr-stat-strip">
            <div class="sr-stat">
                <div class="sr-stat-value">{{ $o['weeks'] }}</div>
                <div class="sr-stat-label">Weeks</div>
            </div>
            <div class="sr-stat">
                <div class="sr-stat-value">{{ $o['players'] }}</div>
                <div class="sr-stat-label">Players</div>
            </div>
            <div class="sr-stat">
                <div class="sr-stat-value">{{ $o['matches'] }}</div>
                <div class="sr-stat-label">Matches</div>
            </div>
            <div class="sr-stat">
                <div class="sr-stat-value">{{ number_format($o['rounds']) }}</div>
                <div class="sr-stat-label">Rounds</div>
            </div>
            <div class="sr-stat">
                <div class="sr-stat-value">{{ number_format($o['holes_played']) }}</div>
                <div class="sr-stat-label">Holes Played</div>
            </div>
            <div class="sr-stat">
                <div class="sr-stat-value">{{ number_format($o['birdies']) }}</div>
                <div class="sr-stat-label">Birdies or Better</div>
            </div>
            <div class="sr-stat">
                <div class="sr-stat-value">{{ number_format($o['pars']) }}</div>
                <div class="sr-stat-label">Pars</div>
            </div>
        </div>
    </div>

    {{-- Team champions --}}
    @foreach($teamGroups as $group)
        @php $winner = $group['teams']->first(); @endphp
        @if($winner)
            <div class="content-section">
                <h2 class="section-title">
                    🏆 {{ $group['segment'] ? $group['segment']->name . ' Champion' : 'League Champion' }}
                </h2>

                <div class="sr-champ-banner">
                    <span class="sr-champ-trophy">🏆</span>
                    <div>
                        <div class="sr-champ-label">
                            @if($group['segment'])
                                Weeks {{ $group['segment']->start_week }}–{{ $group['segment']->end_week }}
                            @else
                                Final Standings
                            @endif
                        </div>
                        <div class="sr-champ-name" @if($winner['color']) style="color: {{ $winner['color'] }};" @endif>
                            {{ $winner['team']->name }}
                        </div>
                        <div class="sr-champ-meta">
                            {{ $winner['team']->wins }}-{{ $winner['team']->losses }}-{{ $winner['team']->ties }}
                            &middot; {{ number_format($winner['points'], 2) }} pts
                            &middot; {{ $winner['win_pct'] }}% win rate
                            @if($winner['team']->captain)
                                &middot; Captain {{ $winner['team']->captain->name }}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="scrollable-table">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 45px;">#</th>
                                <th>Team</th>
                                <th style="width: 50px; text-align: center;">W</th>
                                <th style="width: 50px; text-align: center;">L</th>
                                <th style="width: 50px; text-align: center;">T</th>
                                <th style="width: 70px; text-align: center;">Pts</th>
                                <th style="width: 70px; text-align: center;">Win%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group['teams'] as $i => $row)
                                <tr class="{{ $i === 0 ? 'rank-1' : '' }}">
                                    <td style="text-align: center; font-weight: 600;">
                                        {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i + 1)) }}
                                    </td>
                                    <td style="font-weight: 600; @if($row['color']) color: {{ $row['color'] }}; @endif">
                                        {{ $row['team']->name }}
                                    </td>
                                    <td style="text-align: center; color: #28a745; font-weight: 600;">{{ $row['team']->wins }}</td>
                                    <td style="text-align: center; color: #dc3545; font-weight: 600;">{{ $row['team']->losses }}</td>
                                    <td style="text-align: center; color: #856404; font-weight: 600;">{{ $row['team']->ties }}</td>
                                    <td style="text-align: center; font-weight: 600; color: var(--primary-color);">{{ number_format($row['points'], 2) }}</td>
                                    <td style="text-align: center;">{{ $row['win_pct'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endforeach

    {{-- Player podium --}}
    @if($podium->isNotEmpty())
        <div class="content-section">
            <h2 class="section-title">🥇 Player of the Season</h2>
            <div class="sr-podium">
                @foreach($podium as $i => $row)
                    <div class="sr-podium-card {{ ['sr-gold', 'sr-silver', 'sr-bronze'][$i] ?? '' }}">
                        <div class="sr-podium-medal">{{ ['🥇', '🥈', '🥉'][$i] ?? '' }}</div>
                        <div class="sr-podium-name">
                            <a href="{{ route('players.show', $row['player']->id) }}" class="team-link"
                               @if($row['team_color']) style="color: {{ $row['team_color'] }};" @endif>
                                {{ $row['player']->name }}
                            </a>
                        </div>
                        @if($row['team_name'])
                            <div class="sr-podium-team">{{ $row['team_name'] }}</div>
                        @endif
                        <div class="sr-podium-points">{{ number_format($row['points'], 2) }}</div>
                        <div class="sr-stat-label">Points</div>
                        <div class="sr-podium-record">
                            {{ $row['wins'] }}-{{ $row['losses'] }}-{{ $row['ties'] }}
                            @if($row['win_pct'] !== null) &middot; {{ $row['win_pct'] }}% @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @php $tiedAtTop = $players->where('points', $players->first()['points'])->count(); @endphp
            @if($tiedAtTop > 1)
                <div class="sr-footnote">
                    {{ $tiedAtTop }} players finished level on {{ number_format($players->first()['points'], 2) }}
                    points — the tie is broken here on matches won.
                </div>
            @endif
        </div>
    @endif

    {{-- Awards --}}
    <div class="content-section">
        <h2 class="section-title">🎖️ Season Awards</h2>
        <div class="sr-awards">
            @if($a['low_gross'])
                <div class="sr-award">
                    <div class="sr-award-label">Low Gross Round</div>
                    <div class="sr-award-winner">{{ $a['low_gross']['player']->name }}</div>
                    <div class="sr-award-value">
                        {{ $a['low_gross']['strokes'] }}
                        <span style="font-size: 0.6em;" class="{{ $a['low_gross']['vs_par'] <= 0 ? 'sr-good' : '' }}">
                            ({{ $signed($a['low_gross']['vs_par']) }})
                        </span>
                    </div>
                    <div class="sr-award-note">
                        Week {{ $a['low_gross']['week'] }} &middot; {{ $a['low_gross']['label'] }}
                    </div>
                </div>
            @endif

            @if($a['low_net'])
                <div class="sr-award">
                    <div class="sr-award-label">Low Net Round</div>
                    <div class="sr-award-winner">{{ $a['low_net']['player']->name }}</div>
                    <div class="sr-award-value">
                        {{ $a['low_net']['net'] }}
                        <span style="font-size: 0.6em;" class="{{ $a['low_net']['net_vs_par'] <= 0 ? 'sr-good' : '' }}">
                            ({{ $signed($a['low_net']['net_vs_par']) }})
                        </span>
                    </div>
                    <div class="sr-award-note">
                        Week {{ $a['low_net']['week'] }} &middot; {{ $a['low_net']['label'] }}
                        &middot; {{ $a['low_net']['strokes'] }} gross
                    </div>
                </div>
            @endif

            @if($a['best_average'])
                <div class="sr-award">
                    <div class="sr-award-label">Best Scoring Average</div>
                    <div class="sr-award-winner">{{ $a['best_average']['player']->name }}</div>
                    <div class="sr-award-value">{{ $a['best_average']['avg_strokes'] }}</div>
                    <div class="sr-award-note">
                        {{ $signed($a['best_average']['avg_vs_par']) }} per round
                        over {{ $a['best_average']['rounds'] }} rounds
                    </div>
                </div>
            @endif

            @if($a['most_improved'])
                <div class="sr-award">
                    <div class="sr-award-label">Most Improved</div>
                    <div class="sr-award-winner">{{ $a['most_improved']['player']->name }}</div>
                    <div class="sr-award-value sr-good">−{{ number_format($a['most_improved']['hi_delta'], 1) }}</div>
                    <div class="sr-award-note">
                        Handicap index {{ number_format($a['most_improved']['first_hi'], 1) }}
                        → {{ number_format($a['most_improved']['last_hi'], 1) }}
                        (weeks {{ $a['most_improved']['first_hi_week'] }}–{{ $a['most_improved']['last_hi_week'] }})
                    </div>
                </div>
            @endif

            @if($a['birdie_king'])
                <div class="sr-award">
                    <div class="sr-award-label">Most Birdies or Better</div>
                    <div class="sr-award-winner">{{ $a['birdie_king']['player']->name }}</div>
                    <div class="sr-award-value">{{ $a['birdie_king']['birdies'] }}</div>
                    <div class="sr-award-note">
                        {{ $a['birdie_king']['birdie'] }} birdies
                        @if($a['birdie_king']['eagle'] + $a['birdie_king']['albatross'] > 0)
                            &middot; {{ $a['birdie_king']['eagle'] + $a['birdie_king']['albatross'] }} eagle or better
                        @endif
                        &middot; {{ $a['birdie_king']['par'] }} pars
                    </div>
                </div>
            @endif

            @if($a['hot_streak'])
                <div class="sr-award">
                    <div class="sr-award-label">Hottest Streak</div>
                    <div class="sr-award-winner">{{ $a['hot_streak']['player']->name }}</div>
                    <div class="sr-award-value">{{ $a['hot_streak']['streak'] }} <span style="font-size: 0.55em;">in a row</span></div>
                    <div class="sr-award-note">Consecutive matches won &middot; {{ $a['hot_streak']['wins'] }} wins on the season</div>
                </div>
            @endif

            @if($a['best_duo'])
                <div class="sr-award">
                    <div class="sr-award-label">Best Duo</div>
                    <div class="sr-award-winner">
                        {{ $a['best_duo']['players'][0]->name }} &amp; {{ $a['best_duo']['players'][1]->name }}
                    </div>
                    <div class="sr-award-value">
                        {{ $a['best_duo']['wins'] }}-{{ $a['best_duo']['losses'] }}-{{ $a['best_duo']['ties'] }}
                    </div>
                    <div class="sr-award-note">
                        {{ number_format($a['best_duo']['points'], 2) }} points
                        over {{ $a['best_duo']['matches'] }} matches paired together
                    </div>
                </div>
            @endif

            @if($a['par3_king'])
                <div class="sr-award">
                    <div class="sr-award-label">Par 3 Contest</div>
                    <div class="sr-award-winner">{{ $a['par3_king']['player']->name }}</div>
                    <div class="sr-award-value">{{ $a['par3_king']['par3'] }} <span style="font-size: 0.55em;">wins</span></div>
                    <div class="sr-award-note">Most closest-to-the-pin wins</div>
                </div>
            @endif

            @if($a['attendance'])
                <div class="sr-award">
                    <div class="sr-award-label">
                        {{ $a['attendance']['perfect'] ? 'Perfect Attendance' : 'Best Attendance' }}
                    </div>
                    <div class="sr-award-winner">
                        @if($a['attendance']['count'] === 1)
                            {{ $a['attendance']['players']->first()->name }}
                        @else
                            {{ $a['attendance']['count'] }} players
                        @endif
                    </div>
                    <div class="sr-award-value">
                        {{ $a['attendance']['weeks'] }}<span style="font-size: 0.55em;">/{{ $a['attendance']['season_weeks'] }} weeks</span>
                    </div>
                    <div class="sr-award-note">
                        @if($a['attendance']['count'] > 1)
                            {{ $a['attendance']['players']->take(5)->pluck('name')->join(', ') }}@if($a['attendance']['count'] > 5) and {{ $a['attendance']['count'] - 5 }} more @endif
                        @elseif($a['attendance']['perfect'])
                            Teed off every single week
                        @else
                            Most weeks played — no one made all {{ $a['attendance']['season_weeks'] }}
                        @endif
                    </div>
                </div>
            @endif

            @if($a['toughest_hole'])
                <div class="sr-award">
                    <div class="sr-award-label">Toughest Hole</div>
                    <div class="sr-award-winner">Hole {{ $a['toughest_hole']['hole'] }} &middot; Par {{ $a['toughest_hole']['par'] }}</div>
                    <div class="sr-award-value sr-bad">{{ number_format($a['toughest_hole']['avg'], 2) }}</div>
                    <div class="sr-award-note">
                        {{ $signed($a['toughest_hole']['avg_vs_par']) }} to par
                        &middot; {{ $a['toughest_hole']['birdie_or_better'] }} birdies all season
                    </div>
                </div>
            @endif

            @if($a['easiest_hole'])
                <div class="sr-award">
                    <div class="sr-award-label">Friendliest Hole</div>
                    <div class="sr-award-winner">Hole {{ $a['easiest_hole']['hole'] }} &middot; Par {{ $a['easiest_hole']['par'] }}</div>
                    <div class="sr-award-value sr-good">{{ number_format($a['easiest_hole']['avg'], 2) }}</div>
                    <div class="sr-award-note">
                        {{ $signed($a['easiest_hole']['avg_vs_par']) }} to par
                        &middot; {{ $a['easiest_hole']['birdie_or_better'] }} birdies all season
                    </div>
                </div>
            @endif
        </div>

        <div class="sr-footnote">
            Scoring awards cover completed, non-scramble rounds and are credited to whoever played the round,
            substitutes included. Averages need at least {{ $a['min_rounds'] }} rounds; Best Duo needs
            {{ $a['min_duo_matches'] }} matches together. Rounds are ranked against par so 9- and 18-hole weeks compare fairly.
        </div>
    </div>

    {{-- Week by week --}}
    @if(!empty($weeklyBest))
        <div class="content-section">
            <h2 class="section-title">📅 Round of the Week</h2>
            <div class="scrollable-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 70px;">Week</th>
                            <th style="width: 110px;">Date</th>
                            <th style="width: 120px;">Format</th>
                            <th>Best Round</th>
                            <th style="width: 80px; text-align: center;">Score</th>
                            <th style="width: 80px; text-align: center;">To Par</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weeklyBest as $week)
                            <tr class="{{ $week['round'] ? '' : 'sr-muted-row' }}">
                                <td style="font-weight: 600;">{{ $week['week'] }}</td>
                                <td>{{ $week['date'] ? $week['date']->format('M j') : '—' }}</td>
                                <td>{{ $week['format'] ?? '—' }}</td>
                                @if($week['round'])
                                    <td style="font-weight: 600;">
                                        <a href="{{ route('players.show', $week['round']['player']->id) }}" class="team-link">
                                            {{ $week['round']['player']->name }}
                                        </a>
                                        <span style="color: #999; font-weight: 400; font-size: 0.9em;">
                                            &middot; {{ $week['round']['label'] }}
                                        </span>
                                    </td>
                                    <td style="text-align: center; font-weight: 600;">{{ $week['round']['strokes'] }}</td>
                                    <td style="text-align: center;" class="{{ $week['round']['vs_par'] <= 0 ? 'sr-good' : '' }}">
                                        {{ $signed($week['round']['vs_par']) }}
                                    </td>
                                @else
                                    <td colspan="3">No individual rounds this week</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Final player standings --}}
    @if($players->isNotEmpty())
        <div class="content-section">
            <h2 class="section-title">📊 Final Player Standings</h2>
            <div class="scrollable-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 45px;">#</th>
                            <th>Player</th>
                            <th>Team</th>
                            <th style="width: 70px; text-align: center;">Pts</th>
                            <th style="width: 90px; text-align: center;">W-L-T</th>
                            <th style="width: 70px; text-align: center;">Win%</th>
                            <th style="width: 75px; text-align: center;" title="Weeks the player teed off — a week a substitute filled the slot does not count">Weeks</th>
                            <th style="width: 70px; text-align: center;">Rounds</th>
                            <th style="width: 70px; text-align: center;">Avg</th>
                            <th style="width: 70px; text-align: center;">Low</th>
                            <th style="width: 70px; text-align: center;">Birdies</th>
                            <th style="width: 60px; text-align: center;">Par 3</th>
                            <th style="width: 120px; text-align: center;">Handicap</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($players as $i => $row)
                            <tr class="{{ $i === 0 ? 'rank-1' : '' }}">
                                <td style="text-align: center; font-weight: 600;">
                                    {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i + 1)) }}
                                </td>
                                <td style="white-space: nowrap;">
                                    <a href="{{ route('players.show', $row['player']->id) }}" class="team-link"
                                       @if($row['team_color']) style="color: {{ $row['team_color'] }};" @endif>
                                        {{ $row['player']->name }}
                                    </a>
                                </td>
                                <td style="color: #666; white-space: nowrap;">{{ $row['team_name'] ?? '—' }}</td>
                                <td style="text-align: center; font-weight: 700; color: var(--primary-color);">
                                    {{ number_format($row['points'], 2) }}
                                </td>
                                <td style="text-align: center; white-space: nowrap;">{{ $row['wins'] }}-{{ $row['losses'] }}-{{ $row['ties'] }}</td>
                                <td style="text-align: center;">{{ $row['win_pct'] !== null ? $row['win_pct'] . '%' : '—' }}</td>
                                <td style="text-align: center; white-space: nowrap;">
                                    {{ $row['weeks_played'] }}
                                    @if($row['weeks_subbed_out'] > 0)
                                        <span style="font-size: 0.8em; color: #999;"
                                              title="{{ $row['weeks_subbed_out'] }} week(s) covered by a substitute">
                                            (−{{ $row['weeks_subbed_out'] }})
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ $row['rounds'] }}</td>
                                <td style="text-align: center;">{{ $row['avg_strokes'] ?? '—' }}</td>
                                <td style="text-align: center; font-weight: 600; white-space: nowrap;">
                                    @if($row['low_gross'])
                                        {{ $row['low_gross']['strokes'] }}
                                        <span style="font-weight: 400; font-size: 0.85em; color: #777;">
                                            ({{ $signed($row['low_gross']['vs_par']) }})
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ $row['birdies'] ?: '—' }}</td>
                                <td style="text-align: center;">{{ $row['par3'] ?: '—' }}</td>
                                <td style="text-align: center; white-space: nowrap;">
                                    @if($row['first_hi'] !== null && $row['last_hi'] !== null)
                                        {{ number_format($row['first_hi'], 1) }} → {{ number_format($row['last_hi'], 1) }}
                                        @if($row['hi_delta'] !== null && $row['hi_delta'] != 0)
                                            <span class="{{ $row['hi_delta'] > 0 ? 'sr-good' : 'sr-bad' }}" style="font-size: 0.85em;">
                                                ({{ $row['hi_delta'] > 0 ? '−' : '+' }}{{ number_format(abs($row['hi_delta']), 1) }})
                                            </span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Hole by hole --}}
    @if(!empty($holes))
        <div class="content-section">
            <h2 class="section-title">⛳ How the Course Played</h2>
            <div class="scrollable-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">Hole</th>
                            <th style="width: 55px; text-align: center;">Par</th>
                            <th style="width: 80px; text-align: center;">Played</th>
                            <th style="width: 80px; text-align: center;">Avg</th>
                            <th style="width: 90px; text-align: center;">To Par</th>
                            <th style="width: 90px; text-align: center;">Birdie+</th>
                            <th style="width: 80px; text-align: center;">Pars</th>
                            <th style="width: 90px; text-align: center;">Double+</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($holes as $hole)
                            @php
                                $isToughest = $awards['toughest_hole'] && $hole['hole'] === $awards['toughest_hole']['hole'];
                                $isEasiest = $awards['easiest_hole'] && $hole['hole'] === $awards['easiest_hole']['hole'];
                            @endphp
                            <tr>
                                <td style="font-weight: 600;">
                                    {{ $hole['hole'] }}
                                    @if($isToughest) <span title="Toughest hole">🔥</span> @endif
                                    @if($isEasiest) <span title="Friendliest hole">🎯</span> @endif
                                </td>
                                <td style="text-align: center;">{{ $hole['par'] }}</td>
                                <td style="text-align: center; color: #666;">{{ $hole['played'] }}</td>
                                <td style="text-align: center; font-weight: 600;">{{ number_format($hole['avg'], 2) }}</td>
                                <td style="text-align: center;" class="{{ $hole['avg_vs_par'] > 1 ? 'sr-bad' : '' }}">
                                    {{ $signed($hole['avg_vs_par']) }}
                                </td>
                                <td style="text-align: center; color: #28a745; font-weight: 600;">{{ $hole['birdie_or_better'] ?: '—' }}</td>
                                <td style="text-align: center;">{{ $hole['par_count'] ?: '—' }}</td>
                                <td style="text-align: center; color: #c0392b;">{{ $hole['double'] + $hole['triple_plus'] ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif
