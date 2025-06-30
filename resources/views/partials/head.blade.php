<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="{{asset('imgs/fav.png')}}" sizes="any">
<link rel="apple-touch-icon" href="{{asset("imgs/fav.png")}}">


@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
