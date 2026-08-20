<a href="{{ route('home') }}">Route</a>
<a href="{{Web::home->value}}">Enum</a>
<a href="{!! $url !!}">Unescaped</a>
<form action="{{ route('users.store') }}" method="post"></form>
