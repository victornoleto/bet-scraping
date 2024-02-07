@extends('app')

@section('content')

    <div class="tips d-flex flex-column gap-4">

        @foreach ($tips as $tip)

            @php

                $selections = $tip['selection'];

                if (isset($selections['match_id'])) {
                    $selections = [$selections];
                }

            @endphp

            <div class="tip">

                <div class="badge fs-5 bg-dark">{{ $tip['selections_count'] }}</div>

                <div class="badge fs-5 bg-primary">{{ $tip['total_odds'] }}</div>

                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Home Team</th>
                            <th>Away Team</th>
                            <th>Market Name</th>
                            <th>Status</th>
                            <th>Odd</th>
                            <th>Links</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($selections as $selection)

                            @php
                                $marketSubnameParts = [
                                    $selection['outcome_name'],
                                    $selection['handicap_name']
                                ];

                                $marketSubname = implode(' - ', array_filter($marketSubnameParts));

                            @endphp

                            <tr>
                                <td>{{ $selection['ht_name'] }}</td>
                                <td>{{ $selection['at_name'] }}</td>
                                <td>
                                    <span>{{ $selection['market_name'] }}</span>
                                    <small class="d-block mt-1 opacity-50">{{ $marketSubname }}</small>
                                </td>
                                <td>{{ $selection['status'] }}</td>
                                <td>{{ $selection['odd'] }}</td>
                                <td>
                                    <a href="{{ $selection['match_uri'] }}" target="_blank">Link</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
    
        @endforeach

    </div>


@endsection