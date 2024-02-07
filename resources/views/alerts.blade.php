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

    <div id="filters">

        <div class="form-group mb-3">
            
            <label for="" class="fw-bold">Categorias</label>

            <div class="d-flex flex-wrap">

                @foreach ($categories as $category)
    
                    <div class="form-check me-3 mt-1">
                        <input class="form-check-input" type="checkbox" value="{{ $category }}" id="{{ 'category-' . $loop->index }}" name="category[]">
                        <label class="form-check-label" for="{{ 'category-' . $loop->index }}">{{ $category }}</label>
                    </div>
                    
                @endforeach

            </div>

        </div>

        <div class="form-group mb-3">
            
            <label for="" class="fw-bold">Ligas</label>

            <div class="d-flex flex-wrap">

                @foreach ($leagues as $league)
    
                    <div class="form-check me-3 mt-1">
                        <input class="form-check-input" type="checkbox" value="{{ $league }}" id="{{ 'league-' . $loop->index }}" name="league[]">
                        <label class="form-check-label" for="{{ 'league-' . $loop->index }}">{{ $league }}</label>
                    </div>
                    
                @endforeach

            </div>

        </div>

        <div class="form-group">
            
            <label for="" class="fw-bold">Casas de aposta</label>

            <div class="d-flex flex-wrap">

                @foreach ($bookmakers as $bookmaker)
    
                    <div class="form-check me-3 mt-1">
                        <input class="form-check-input" type="checkbox" value="{{ $bookmaker }}" id="{{ 'bookmaker-' . $loop->index }}" name="bookmaker[]">
                        <label class="form-check-label" for="{{ 'bookmaker-' . $loop->index }}">{{ $bookmaker }}</label>
                    </div>
                    
                @endforeach

            </div>

        </div>

    </div>

    <div class="table-responsive border-top pt-3 mt-3 border-2">

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
                        <td data-name="category"><span>{{ $alert->category }}</span></td>
                        <td data-name="league"><span>{{ $alert->league }}</span></td>
                        <td>{{ $alert->ht }}</td>
                        <td>{{ $alert->at }}</td>
                        
                        <td data-order="{{ $alert->match_time }}">
                            <span>{{ $alert->match_time }}</span>
                            <small class="d-block text-muted">{{ $alert->refreshed_at }}</small>
                        </td>
    
                        @if ($marketSubname)
                            
                            <td>
                                <span>{{ $marketSubname }}</span>
                                <small class="d-block mt-1 opacity-50">{{ $alert->betting_market_name }}</small>
                            </td>
                            
                        @else
                            <td>{{ $alert->betting_market_name }}</td>
                        @endif
    
                        <td data-order="{{ $alert->o1 }}" data-name="bookmaker">
                            <x-bookmaker-odd-link :alert="$alert" odd="1" />
                            <small class="d-block text-muted" data-odd="{{ $alert->o1 }}" data-pair-odd="{{ $alert->o2 }}"></small>
                        </td>
    
                        <td data-order="{{ $alert->o2 }}" data-name="bookmaker">
                            <x-bookmaker-odd-link :alert="$alert" odd="2" />
                            <small class="d-block text-muted" data-odd="{{ $alert->o2 }}" data-pair-odd="{{ $alert->o1 }}"></small>
                        </td>
    
                        <td>{{ round($alert->profit_percentage, 3) }}</td>
    
                    </tr>
                    
                @endforeach
    
            </tbody>
    
        </table>

    </div>

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

            $('#bet-total').on('input', function() {
                
                updateBetAmounts();

                localStorage.setItem('bet-total', $(this).val());

            });

            var storageBetTotal = localStorage.getItem('bet-total');

            if (storageBetTotal) {
                $('#bet-total').val(storageBetTotal);
            }

            updateBetAmounts();

            $('.form-check-input').on('change', function() {

                var categories = [];
                var leagues = [];
                var bookmakers = [];

                $('[name="category[]"]:checked').each(function() {
                    categories.push($(this).val());
                });

                $('[name="league[]"]:checked').each(function() {
                    leagues.push($(this).val());
                });

                $('[name="bookmaker[]"]:checked').each(function() {
                    bookmakers.push($(this).val());
                });

                $('table tbody tr').each(function() {

                    var category = $(this).find('[data-name="category"] span').text();
                    var league = $(this).find('[data-name="league"] span').text();
                    var bookmaker = [];

                    $(this).find('[data-name="bookmaker"] .bookmaker').each(function() {
                        bookmaker.push($(this).text());
                    });

                    var show = 
                        (categories.length == 0 || categories.includes(category)) &&
                        (leagues.length == 0 || leagues.includes(league)) &&
                        (bookmakers.length == 0 || bookmakers.some(b => bookmaker.includes(b)));

                    $(this).toggle(show);

                });

                console.log({categories, leagues, bookmakers});

            });

        });

    </script>

@endsection