
@extends('app')

@section('content')

    @php
        $selectedBookmakerIds = request()->get('bookmaker_id', []);
    @endphp

    <form action="">

        <div class="form-group">

            <label for="" class="fw-bold mb-2">Casa de apostas</label>

            <div class="d-flex flex-wrap gap-2">

                @foreach ($bookmakers as $b)
    
                    <div class="form-check me-2">
                        
                        <input name="bookmaker_id[]" class="form-check-input" type="checkbox" value="{{ $b->id }}" id="{{ 'bookmaker-' . $b->id }}" {{ in_array($b->id, $selectedBookmakerIds) ? 'checked' : '' }}>
                        
                        <label class="form-check-label" for="{{ 'bookmaker-' . $b->id }}">{{ $b->name }}</label>

                    </div>
                    
                @endforeach

            </div>

        </div>

        <div class="row mt-3">

            <div class="form-group col-4">

                <label for="" class="fw-bold">Odd mínima</label>

                <input type="number" step="any" class="form-control" name="min_odd" value={{ request()->get('min_odd') }} />

            </div>

            <div class="form-group col-4">

                <label for="" class="fw-bold">Odd máxima</label>

                <input type="number" step="any" class="form-control" name="max_odd" value={{ request()->get('max_odd') }} />

            </div>

            <div class="form-group col-4">

                <label for="" class="fw-bold">Proporção</label>

                <input type="number" class="form-control" name="ratio" value={{ request()->get('ratio') }} />

            </div>

        </div>

        <button type="submit" class="btn btn-dark fw-bold mt-3">Enviar</button>

    </form>

    <ul class="mt-4 d-flex flex-column gap-3 ps-0">

        @foreach ($odds as $odd)

            <li class="d-flex flex-column">
                
                <small class="opacity-75">{{ $odd['sport'] }}, {{ $odd['category'] }}, {{ $odd['league'] }}</small>
                
                <span>{{ $odd['home'] }} ({{ $odd['home_odd'] }}) — {{ $odd['away'] }} ({{ $odd['away_odd'] }})</span>
                
                <small class="opacity-50">{{ $odd['bookmaker_name'] }}</small>

            </li>
            
        @endforeach

    </ul>
    
@endsection

