<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Styles -->
        <link href="{{ mix('css/app.css') }}" rel="stylesheet">

        <link id="style_dashboard" href="{{ asset('css/custom.css') }}" rel="stylesheet">

    </head>
    <body class="">

        {{-- page vue (single page application) --}}
        <div id="app"></div>
        <!-- script -->
        <script src="{{ mix('js/app.js') }}"></script>

    </body>
</html>
