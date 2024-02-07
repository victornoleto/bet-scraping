@php
    $url = empty($url) ? 'javascript:void(0)' : $url;
@endphp

<a href="{{ $url }}" target="_blank">

    <small class="d-block opacity-50">{{ $alert[$odd == 1 ? 'o1_bookmaker_name' : 'o2_bookmaker_name'] }}</small>

    <span>{{ $alert[$odd == 1 ? 'o1' : 'o2'] }}</span>

</a>