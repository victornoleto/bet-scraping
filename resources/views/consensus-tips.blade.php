@extends('app')

@section('content')

    <table class="table table-striped align-middle">

        <thead>
            <tr>
                <th>Sport</th>
                <th>Category</th>
                <th>League</th>
                <th>Home Team</th>
                <th>Away Team</th>
                <th>Match Date</th>
                <th>Market Name</th>
                <th>Tips</th>
                <th>Consensus Ratio</th>
                <th>Max Odd</th>
                <th>Links</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($tips as $tip)

                @php

                    $marketSubnameParts = array_filter([
                        $tip['outcome_name'],
                        $tip['handicap_name']
                    ]);
                    
                    $marketSubname = null;
                    
                    if (count($marketSubnameParts) > 0) {
                        $marketSubname = implode(' - ', $marketSubnameParts);
                    }
                    
                @endphp

                <tr>
                    <td>{{ $tip['match']['sport_name'] }}</td>
                    <td>{{ $tip['match']['category_name'] }}</td>
                    <td>{{ $tip['match']['league_name'] }}</td>
                    <td>{{ $tip['match']['ht'] }}</td>
                    <td>{{ $tip['match']['at'] }}</td>
                    <td>{{ $tip['match']['md'] }}</td>
                    <td>
                        <span>{{ $tip['market'] }}</span>
                        @if ($marketSubname)
                            <small class="d-block mt-1 opacity-50">{{ $marketSubname }}</small>
                        @endif
                    </td>
                    <td>{{ $tip['same_tips'] }}/{{ $tip['tips'] }}</td>
                    <td>{{ $tip['consensus_ratio'] }}</td>
                    <td>
                        <span>{{ $tip['max_odd']['value'] ?? 0 }}</span>
                        <small class="d-block mt-1 opacity-50">{{ $tip['max_odd']['bookie'] ?? '-' }}</small>
                    </td>
                    <td>
                        <a href="{{ $tip['match']['uri'] }}" target="_blank">Link</a>
                    </td>
                </tr>
                
            @endforeach

        </tbody>

    </table>

    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <script>

        $(document).ready(function() {
            $('table').DataTable({
                paging: false,
                searching: false,
                info: false,
                order: [[ 8, "desc" ]]
            });
        });

    </script>

@endsection