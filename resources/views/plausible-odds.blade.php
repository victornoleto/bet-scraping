<ul>

    @foreach ($odds as $odd)

        <li>
            <span>{{ $odd['home'] }} ({{ $odd['home_odd'] }}) — {{ $odd['away'] }} ({{ $odd['away_odd'] }})</span>
            <small style="opacity: 0.5">{{ $odd['bookmaker_name'] }}</small>
        </li>
        
    @endforeach

</ul>