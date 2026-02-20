<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    @include('partials.theme-vars')
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Players - {{ $league->name }}</title>
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
        .success-message {
            background: #28a745;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .error-message {
            background: #dc3545;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .add-players-section {
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
        .players-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .players-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 15px;
        }
        .player-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: var(--primary-light);
            border-radius: 8px;
            border: 2px solid #e8e9ff;
            transition: all 0.3s ease;
        }
        .player-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(var(--primary-rgb), 0.1);
        }
        .player-checkbox-card {
            display: flex;
            align-items: center;
            padding: 15px;
            background: var(--primary-light);
            border-radius: 8px;
            border: 2px solid #e8e9ff;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .player-checkbox-card:hover {
            border-color: var(--primary-color);
            background: #f0f2ff;
        }
        .player-checkbox-card.selected {
            border-color: var(--primary-color);
            background: #e8ebff;
        }
        .player-checkbox-card input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 15px;
            cursor: pointer;
        }
        .player-info {
            flex: 1;
        }
        .player-name {
            font-weight: 600;
            font-size: 1.1em;
            color: #333;
            margin-bottom: 5px;
        }
        .player-handicap {
            color: #666;
            font-size: 0.9em;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
            background: var(--primary-light);
            border-radius: 8px;
        }
        .info-box {
            background: #e8f4f8;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #0c5460;
        }
        .selection-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: var(--primary-light);
            border-radius: 8px;
        }
        .selection-info {
            font-weight: 600;
            color: var(--primary-color);
        }
        .selection-buttons {
            display: flex;
            gap: 10px;
        }
        .submit-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e8e9ff;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        @if($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="header">
            <h1>👥 Manage League Players</h1>
            <p class="subtitle">{{ $league->name }} - {{ $league->season }}</p>
        </div>

        <div class="info-box">
            <strong>ℹ️ About League Players:</strong><br>
            Add players to this league to make them available for matches and auto-scheduling. Only players assigned to the league will appear in match creation and scheduling forms.
        </div>

        <!-- Add Players Section -->
        @if($availablePlayers->isEmpty())
            <div class="add-players-section">
                <h2 class="section-title">➕ Add Players to League</h2>
                <div class="empty-state">
                    <p style="font-size: 1.2em; margin-bottom: 10px;">All players have been added to this league!</p>
                    <p>There are no more players available to add.</p>
                </div>
            </div>
        @else
            <div class="add-players-section">
                <h2 class="section-title">➕ Add Players to League</h2>

                <form action="{{ route('admin.leagues.players.add', $league->id) }}" method="POST" id="addPlayersForm">
                    @csrf

                    <div class="selection-controls">
                        <div class="selection-info">
                            <span id="selectedCount">0</span> player(s) selected
                        </div>
                        <div class="selection-buttons">
                            <button type="button" class="btn btn-secondary btn-small" onclick="selectAll()">Select All</button>
                            <button type="button" class="btn btn-secondary btn-small" onclick="deselectAll()">Deselect All</button>
                        </div>
                    </div>

                    <div class="players-grid">
                        @foreach($availablePlayers->sortBy('first_name') as $player)
                            <label class="player-checkbox-card" data-player-card>
                                <input type="checkbox" name="player_ids[]" value="{{ $player->id }}" onchange="updateSelection()">
                                <div class="player-info">
                                    <div class="player-name">{{ $player->name }}</div>
                                    <div class="player-handicap">
                                        Handicap Index: {{ $player->currentHandicap()?->handicap_index ?? 'N/A' }}
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="submit-section">
                        <div style="color: #666; font-size: 0.9em;">
                            Select one or more players above to add them to the league
                        </div>
                        <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                            Add Selected Players
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Current Players -->
        <div class="players-section">
            <h2 class="section-title">Current League Players ({{ $league->players->count() }})</h2>

            @if($league->players->isEmpty())
                <div class="empty-state">
                    <p style="font-size: 1.2em; margin-bottom: 10px;">No players assigned yet</p>
                    <p>Add players to this league using the form above</p>
                </div>
            @else
                <div class="players-grid">
                    @foreach($league->players->sortBy('first_name') as $player)
                        <div class="player-card">
                            <div class="player-info">
                                <div class="player-name">{{ $player->name }}</div>
                                <div class="player-handicap">
                                    Handicap Index: {{ $player->currentHandicap()?->handicap_index ?? 'N/A' }}
                                </div>
                            </div>
                            <form action="{{ route('admin.leagues.players.remove', [$league->id, $player->id]) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Remove {{ $player->name }} from this league?');">
                                    Remove
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>
        function updateSelection() {
            const checkboxes = document.querySelectorAll('input[name="player_ids[]"]');
            const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;

            // Update counter
            document.getElementById('selectedCount').textContent = checkedCount;

            // Enable/disable submit button
            document.getElementById('submitBtn').disabled = checkedCount === 0;

            // Update card styling
            checkboxes.forEach(checkbox => {
                const card = checkbox.closest('[data-player-card]');
                if (checkbox.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            });
        }

        function selectAll() {
            const checkboxes = document.querySelectorAll('input[name="player_ids[]"]');
            checkboxes.forEach(cb => cb.checked = true);
            updateSelection();
        }

        function deselectAll() {
            const checkboxes = document.querySelectorAll('input[name="player_ids[]"]');
            checkboxes.forEach(cb => cb.checked = false);
            updateSelection();
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSelection();
        });
    </script>
</body>
</html>
