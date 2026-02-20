@php
    $categories = ['albatross', 'eagle', 'birdie', 'par', 'bogey', 'double', 'triple_plus'];
    $catLabels = ['Albatross', 'Eagle', 'Birdie', 'Par', 'Bogey', 'Double', 'Triple+'];
    $catColors = ['#9b59b6', '#e67e22', '#28a745', '#333', '#dc3545', '#c0392b', '#8b0000'];
    $catWeights = ['700', '700', '600', 'normal', 'normal', '600', '700'];

    $frontGross = array_filter($grossByHole, fn($v, $k) => $k >= 1 && $k <= 9, ARRAY_FILTER_USE_BOTH);
    $backGross = array_filter($grossByHole, fn($v, $k) => $k >= 10 && $k <= 18, ARRAY_FILTER_USE_BOTH);
    $frontNet = array_filter($netByHole, fn($v, $k) => $k >= 1 && $k <= 9, ARRAY_FILTER_USE_BOTH);
    $backNet = array_filter($netByHole, fn($v, $k) => $k >= 10 && $k <= 18, ARRAY_FILTER_USE_BOTH);

    $buildTotals = function($holes) use ($categories) {
        $totals = array_fill_keys($categories, 0);
        foreach ($holes as $h) {
            foreach ($categories as $cat) { $totals[$cat] += $h[$cat]; }
        }
        return $totals;
    };
@endphp

@foreach(['gross' => [$frontGross, $backGross], 'net' => [$frontNet, $backNet]] as $mode => $nines)
    <div id="hs-byhole-{{ $mode }}-{{ $idSuffix }}" style="{{ $mode === 'net' ? 'display: none;' : '' }}">
        @foreach([['Front 9', $nines[0], 1], ['Back 9', $nines[1], 10]] as $nine)
            @php
                [$label, $holeData, $startHole] = $nine;
                $holes = [];
                for ($h = $startHole; $h < $startHole + 9; $h++) {
                    if (isset($holeData[$h])) $holes[$h] = $holeData[$h];
                }
                $totals = $buildTotals($holes);
            @endphp
            @if(!empty($holes))
                <h3 style="color: var(--primary-color); font-size: 1.1em; margin: 20px 0 10px 0;">{{ $label }} <span style="font-size: 0.8em; color: #888; font-weight: normal;">({{ ucfirst($mode) }})</span></h3>
                <div class="scrollable-table">
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: left; min-width: 75px;">Hole</th>
                                @foreach($holes as $holeNum => $hData)
                                    <th>{{ $holeNum }}</th>
                                @endforeach
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 0; $i < count($categories); $i++)
                                <tr>
                                    <td style="text-align: left; font-weight: 600; color: {{ $catColors[$i] }};">{{ $catLabels[$i] }}</td>
                                    @foreach($holes as $holeNum => $hData)
                                        <td style="color: {{ $catColors[$i] }}; font-weight: {{ $catWeights[$i] }};">{{ $hData[$categories[$i]] ?: '-' }}</td>
                                    @endforeach
                                    <td style="color: {{ $catColors[$i] }}; font-weight: 700;">{{ $totals[$categories[$i]] ?: '-' }}</td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach
    </div>
@endforeach
