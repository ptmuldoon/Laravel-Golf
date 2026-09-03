<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    @include('partials.theme-vars')
    <link rel="icon" type="image/svg+xml" href="/images/logo3.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Season Recap - {{ $league->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1100px; margin: 0 auto; }
        .back-link {
            display: inline-block; color: white; text-decoration: none;
            padding: 10px 20px; background: rgba(255,255,255,0.2);
            border-radius: 5px; margin-bottom: 20px; transition: background 0.3s ease;
        }
        .back-link:hover { background: rgba(255,255,255,0.3); }
        .content-section {
            background: white; padding: 30px; border-radius: 12px;
            margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-width: 0;
        }
        .section-title {
            font-size: 1.8em; color: var(--primary-color); margin-bottom: 20px;
            padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;
        }
        .scrollable-table { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th {
            background: var(--primary-light); padding: 10px 8px; text-align: left;
            font-weight: 600; color: var(--primary-color);
            border-bottom: 2px solid #e0e0e0; font-size: 0.9em; white-space: nowrap;
        }
        td { padding: 5px 8px; border-bottom: 1px solid #f0f0f0; font-size: 0.95em; }
        tr:hover { background: var(--primary-light); }
        .team-link { color: var(--primary-color); text-decoration: none; font-weight: 600; }
        .team-link:hover { text-decoration: underline; }
        .rank-1 { background: #fff9e6 !important; }
        .empty-state { text-align: center; padding: 40px; color: #888; }
        @media (max-width: 768px) {
            body { padding: 10px; }
            .content-section { padding: 16px; }
            .section-title { font-size: 1.3em; }
            table { font-size: 0.85em; }
            td, th { padding: 6px 4px; }
        }
        @media print {
            body { background: white; padding: 0; }
            .back-link, .print-btn { display: none; }
            .content-section { box-shadow: none; border: 1px solid #ddd; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('home', ['league' => $league->id]) }}" class="back-link">&larr; Back to League</a>
            <a href="#" class="back-link print-btn" onclick="event.preventDefault(); window.print();">🖨️ Print</a>
        </div>

        @include('leagues.season-recap-partial')
    </div>
</body>
</html>
