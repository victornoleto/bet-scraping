@extends('app')

@section('content')

    <div class="row">

        <div class="col-4">

            <div class="form-group mb-3">
                <label class="fw-bold">Quanto você deseja apostar?</label>
                <input type="number" class="form-control" id="bet-total" value="100" />
            </div>

        </div>

    </div>

    <table class="table table-striped align-middle">

        <thead>
            <tr>
                {{-- <th>Sport</th> --}}
                <th>Category</th>
                <th>League</th>
                <th>Home Team</th>
                <th>Away Team</th>
                <th>Match Date</th>
                <th>Market</th>
                <th>Odd 1</th>
				<th>Odd 2</th>
                <th>Lucro (%)</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($alerts as $alert)

                @php

                    $marketSubnameParts = array_filter([
                        $alert['period'],
                        $alert['alternative']
                    ]);
                    
                    $marketSubname = null;
                    
                    if (count($marketSubnameParts) > 0) {
                        $marketSubname = implode(', ', $marketSubnameParts);
                    }
                    
                @endphp

                <tr>

                    {{-- <td>{{ $tip['match']['sport_name'] }}</td> --}}
                    <td>{{ $alert->category }}</td>
                    <td>{{ $alert->league }}</td>
                    <td>{{ $alert->ht }}</td>
                    <td>{{ $alert->at }}</td>
                    <td>{{ $alert->match_time }}</td>

                    @if ($marketSubname)
                        
                        <td>
                            <span>{{ $marketSubname }}</span>
                            <small class="d-block mt-1 opacity-50">{{ $alert->betting_market_name }}</small>
                        </td>
                        
                    @else
                        <td>{{ $alert->betting_market_name }}</td>
                    @endif

                    <td>
                        <x-bookmaker-odd-link :alert="$alert" odd="1" />
                        <small class="d-block text-muted" data-odd="{{ $alert->o1 }}" data-pair-odd="{{ $alert->o2 }}"></small>
                    </td>

                    <td>
                        <x-bookmaker-odd-link :alert="$alert" odd="2" />
                        <small class="d-block text-muted" data-odd="{{ $alert->o2 }}" data-pair-odd="{{ $alert->o1 }}"></small>
                    </td>

                    <td>{{ round($alert->profit_percentage, 3) }}</td>

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
                //order: [[ 8, "desc" ]]
            });

            function updateBetAmounts() {

                var betTotal = parseFloat($('#bet-total').val());

                $('[data-odd]').each(function() {

                    var odd = parseFloat($(this).data('odd'));
                    var pairOdd = parseFloat($(this).data('pair-odd'));

                    var betAmount = (betTotal * pairOdd) / (odd + pairOdd);

                    $(this).text(betAmount.toFixed(2));

                });
            }

            updateBetAmounts();

            $('#bet-total').on('input', function() {
                updateBetAmounts();
            });

        });

    </script>

@endsection