<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    @include('partials.theme-vars')
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teams - {{ $league->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .back-link {
            display: inline-block;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background 0.3s ease;
        }
        .back-link:hover {
            background: rgba(255,255,255,0.3);
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h1 {
            color: var(--primary-color);
            font-size: 2em;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 20px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-success:disabled {
            background: #94d3a2;
            cursor: not-allowed;
        }
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        .btn-primary:hover {
            background: var(--secondary-color);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-small {
            padding: 6px 12px;
            font-size: 0.85em;
        }
        .teams-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .team-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .team-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .team-name {
            font-size: 1.5em;
            font-weight: bold;
            color: var(--primary-color);
        }
        .team-name-display {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .edit-name-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.65em;
            color: #999;
            padding: 2px 6px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        .edit-name-btn:hover {
            color: var(--primary-color);
            background: #f0f2ff;
        }
        .edit-name-form {
            display: none;
            align-items: center;
            gap: 8px;
        }
        .edit-name-form.active {
            display: flex;
        }
        .edit-name-input {
            font-size: 1em;
            padding: 6px 10px;
            border: 2px solid var(--primary-color);
            border-radius: 6px;
            font-weight: 600;
            color: var(--primary-color);
            width: 180px;
            font-family: inherit;
        }
        .edit-name-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.2);
        }
        .team-record {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 15px;
        }
        .players-list {
            margin-bottom: 15px;
        }
        .player-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: var(--primary-light);
            border-radius: 5px;
            margin-bottom: 8px;
        }
        .player-name {
            font-weight: 500;
        }
        .captain-badge {
            background: #ffd700;
            color: #333;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.75em;
            font-weight: 600;
            margin-left: 8px;
        }
        .player-handicap {
            color: #666;
            font-size: 0.9em;
        }
        .player-checkbox-item {
            display: flex;
            align-items: center;
            padding: 8px;
            background: var(--primary-light);
            border-radius: 5px;
            margin-bottom: 6px;
            border: 2px solid #e8e9ff;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .player-checkbox-item:hover {
            border-color: var(--primary-color);
            background: #f0f2ff;
        }
        .player-checkbox-item.selected {
            border-color: var(--primary-color);
            background: #e8ebff;
        }
        .player-checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            cursor: pointer;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
            background: var(--primary-light);
            border-radius: 8px;
        }
        .success-message {
            background: #28a745;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .create-team-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .section-title {
            font-size: 1.5em;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 15px;
        }
        label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        input[type="text"],
        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            font-family: inherit;
            transition: border-color 0.3s ease;
        }
        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        .add-player-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }
        .selection-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: var(--primary-light);
            border-radius: 5px;
        }
        .selection-info {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.9em;
        }
        .players-checkboxes {
            max-height: 200px;
            overflow-y: auto;
            margin-bottom: 10px;
            padding: 5px;
        }
        .toggle-players-btn {
            background: #f0f2ff;
            color: var(--primary-color);
            padding: 8px 12px;
            border: 2px solid #e8e9ff;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 600;
            width: 100%;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        .toggle-players-btn:hover {
            background: #e8ebff;
            border-color: var(--primary-color);
        }
        .players-container {
            display: none;
        }
        .players-container.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('admin.leagues.show', $league->id) }}" class="back-link">← Back to League</a>

        @if(session('success'))
            <div class="success-message">
                ✓ {{ session('success') }}
            </div>
        @endif

        <div class="header">
            <h1>👥 Manage Teams</h1>
            <p class="subtitle">{{ $league->name }} - {{ $league->season }}</p>
        </div>

        @if(isset($segments) && $segments->isNotEmpty())
            <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div style="font-weight: 600; color: #333; margin-bottom: 10px;">Select Segment</div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    @foreach($segments as $segment)
                        <a href="{{ route('admin.leagues.teams.manage', $league->id) }}?segment={{ $segment->id }}"
                           class="btn {{ isset($selectedSegment) && $selectedSegment->id == $segment->id ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.9em;">
                            {{ $segment->name }} (Wk {{ $segment->start_week }}-{{ $segment->end_week }})
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($allPlayers->isEmpty())
            <div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <strong>⚠️ No Players Assigned to League</strong><br>
                You need to assign players to this league before you can add them to teams.
                <a href="{{ route('admin.leagues.players.manage', $league->id) }}" style="color: #721c24; text-decoration: underline; font-weight: 600; margin-left: 5px;">Manage League Players</a>
            </div>
        @endif

        <!-- Create New Team -->
        <div class="create-team-section">
            <h2 class="section-title">➕ Create New Team</h2>
            <form action="{{ route('admin.teams.store') }}" method="POST">
                @csrf
                <input type="hidden" name="league_id" value="{{ $league->id }}">
                @if(isset($selectedSegment) && $selectedSegment)
                    <input type="hidden" name="league_segment_id" value="{{ $selectedSegment->id }}">
                @endif
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Team Name</label>
                        <input type="text" id="name" name="name" required placeholder="e.g., Eagles">
                    </div>
                    <div class="form-group">
                        <label for="captain_id">Captain (Optional)</label>
                        <select id="captain_id" name="captain_id">
                            <option value="">None</option>
                            @foreach($allPlayers as $player)
                                <option value="{{ $player->id }}">{{ $player->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Create Team</button>
            </form>
        </div>

        <!-- Existing Teams -->
        @if($league->teams->isEmpty())
            <div class="empty-state">
                <p style="font-size: 1.2em; margin-bottom: 10px;">No teams yet</p>
                <p>Create your first team using the form above</p>
            </div>
        @else
            <div class="teams-grid">
                @foreach($league->teams as $team)
                    <div class="team-card">
                        <div class="team-header">
                            <div class="team-name">
                                <div class="team-name-display" id="name-display-{{ $team->id }}">
                                    {{ $team->name }}
                                    <button type="button" class="edit-name-btn" onclick="showEditName({{ $team->id }})" title="Edit team name">✏️</button>
                                </div>
                                <form action="{{ route('admin.teams.update', $team->id) }}" method="POST" class="edit-name-form" id="name-form-{{ $team->id }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $team->name }}" class="edit-name-input" required maxlength="255">
                                    <button type="submit" class="btn btn-success btn-small">Save</button>
                                    <button type="button" class="btn btn-secondary btn-small" onclick="cancelEditName({{ $team->id }})">Cancel</button>
                                </form>
                            </div>
                            <form action="{{ route('admin.teams.destroy', $team->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete team {{ $team->name }}? This cannot be undone!');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-small">Delete</button>
                            </form>
                        </div>

                        <div class="team-record">
                            📊 {{ $team->wins }}-{{ $team->losses }}-{{ $team->ties }} ({{ $team->totalPoints() }} pts)
                        </div>

                        @if($team->captain)
                            <div style="margin-bottom: 15px; font-size: 0.9em; color: #666;">
                                ⭐ Captain: {{ $team->captain->name }}
                            </div>
                        @endif

                        <div class="players-list">
                            <strong style="display: block; margin-bottom: 10px;">Players ({{ $team->players->count() }})</strong>

                            @if($team->players->isEmpty())
                                <p style="color: #888; font-size: 0.9em; font-style: italic;">No players assigned</p>
                            @else
                                @foreach($team->players as $player)
                                    <div class="player-item">
                                        <div>
                                            <span class="player-name">{{ $player->name }}</span>
                                            @if($team->captain_id == $player->id)
                                                <span class="captain-badge">CAPTAIN</span>
                                            @endif
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span class="player-handicap">
                                                HI: {{ $player->currentHandicap()?->handicap_index ?? 'N/A' }}
                                            </span>
                                            <form action="{{ route('admin.teams.players.remove', [$team->id, $player->id]) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Remove {{ $player->name }} from team?');">
                                                    ✕
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Multi-select player addition -->
                        <div class="add-player-section">
                            @php
                                // Get all player IDs already on any team in this league
                                $playersOnTeams = $league->teams->flatMap(function($t) {
                                    return $t->players->pluck('id');
                                })->unique();

                                // Only show players not on any team
                                $availablePlayers = $allPlayers->filter(function($player) use ($playersOnTeams) {
                                    return !$playersOnTeams->contains($player->id);
                                });
                            @endphp

                            @if($availablePlayers->isEmpty())
                                <p style="color: #888; font-size: 0.9em; font-style: italic;">All players have been added to this team</p>
                            @else
                                <button type="button" class="toggle-players-btn" onclick="togglePlayers({{ $team->id }})">
                                    ➕ Add Players to Team (<span id="count-{{ $team->id }}">0</span> selected)
                                </button>

                                <div id="players-{{ $team->id }}" class="players-container">
                                    <form action="{{ route('admin.teams.players.add', $team->id) }}" method="POST" id="form-{{ $team->id }}">
                                        @csrf

                                        <div class="selection-controls">
                                            <div class="selection-info">
                                                <span id="selected-{{ $team->id }}">0</span> player(s) selected
                                            </div>
                                            <div style="display: flex; gap: 5px;">
                                                <button type="button" class="btn btn-secondary btn-small" onclick="selectAllTeam({{ $team->id }})">All</button>
                                                <button type="button" class="btn btn-secondary btn-small" onclick="deselectAllTeam({{ $team->id }})">None</button>
                                            </div>
                                        </div>

                                        <div class="players-checkboxes">
                                            @foreach($availablePlayers as $player)
                                                <label class="player-checkbox-item" data-team="{{ $team->id }}" data-checkbox>
                                                    <input type="checkbox" name="player_ids[]" value="{{ $player->id }}" onchange="updateTeamSelection({{ $team->id }})">
                                                    <div style="flex: 1;">
                                                        <div class="player-name">{{ $player->name }}</div>
                                                        <div class="player-handicap">HI: {{ $player->currentHandicap()?->handicap_index ?? 'N/A' }}</div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>

                                        <button type="submit" class="btn btn-success btn-small" id="submit-{{ $team->id }}" disabled style="width: 100%; margin-top: 10px;">
                                            Add Selected Players
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <!-- Update Captain -->
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                            <form action="{{ route('admin.teams.update', $team->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label for="captain_id_{{ $team->id }}">Update Captain</label>
                                    <div style="display: flex; gap: 10px;">
                                        <select id="captain_id_{{ $team->id }}" name="captain_id" style="flex: 1;">
                                            <option value="">None</option>
                                            @foreach($team->players as $player)
                                                <option value="{{ $player->id }}" {{ $team->captain_id == $player->id ? 'selected' : '' }}>
                                                    {{ $player->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-small">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function showEditName(teamId) {
            document.getElementById(`name-display-${teamId}`).style.display = 'none';
            const form = document.getElementById(`name-form-${teamId}`);
            form.classList.add('active');
            form.querySelector('input[name="name"]').focus();
        }

        function cancelEditName(teamId) {
            document.getElementById(`name-display-${teamId}`).style.display = 'flex';
            document.getElementById(`name-form-${teamId}`).classList.remove('active');
        }

        function togglePlayers(teamId) {
            const container = document.getElementById(`players-${teamId}`);
            const btn = event.target;

            if (container.classList.contains('show')) {
                container.classList.remove('show');
                btn.innerHTML = btn.innerHTML.replace('▼', '➕');
            } else {
                container.classList.add('show');
                btn.innerHTML = btn.innerHTML.replace('➕', '▼');
            }
        }

        function updateTeamSelection(teamId) {
            const checkboxes = document.querySelectorAll(`#form-${teamId} input[name="player_ids[]"]`);
            const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;

            // Update counters
            document.getElementById(`selected-${teamId}`).textContent = checkedCount;
            document.getElementById(`count-${teamId}`).textContent = checkedCount;

            // Enable/disable submit button
            document.getElementById(`submit-${teamId}`).disabled = checkedCount === 0;

            // Update card styling
            checkboxes.forEach(checkbox => {
                const card = checkbox.closest('[data-checkbox]');
                if (checkbox.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            });
        }

        function selectAllTeam(teamId) {
            const checkboxes = document.querySelectorAll(`#form-${teamId} input[name="player_ids[]"]`);
            checkboxes.forEach(cb => cb.checked = true);
            updateTeamSelection(teamId);
        }

        function deselectAllTeam(teamId) {
            const checkboxes = document.querySelectorAll(`#form-${teamId} input[name="player_ids[]"]`);
            checkboxes.forEach(cb => cb.checked = false);
            updateTeamSelection(teamId);
        }

        // Initialize all team selections on page load
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($league->teams as $team)
                updateTeamSelection({{ $team->id }});
            @endforeach
        });
    </script>
</body>
</html>
