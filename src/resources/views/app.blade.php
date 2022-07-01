<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, user-scalable=no">
  <link rel="icon" href="/48.png" />
  <link rel="manifest" href="/manifest.json" />
  <meta name="theme-color" content="#1F2937" />
  <link rel="icon" sizes="32x32" href="/32.png" />
  <link rel="icon" sizes="48x48" href="/48.png" />
  <link rel="icon" sizes="76x76" href="/76.png" />
  <link rel="icon" sizes="144x144" href="/144.png" />
  <link rel="icon" sizes="196x196" href="/196.png" />
  <link rel="icon" sizes="512x512" href="/512.png" />
  <link rel="apple-touch-icon" href="/76.png" />
  <link rel="apple-touch-icon" sizes="76x76" href="/76.png" />
  <link rel="apple-touch-icon" sizes="120x120" href="/120.png" />
  <link rel="apple-touch-icon" sizes="152x152" href="/152.png" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title></title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
  @routes()
  <link rel="stylesheet" href="{{ mix('css/app.css') }}">
  {!! '<script>var Locale = "' . app()->getLocale() . '";</script>' !!}
  <script src="{{ mix('js/app.js') }}" defer></script>
</head>

<body class="font-sans antialiased bg-gray-100">
  @inertia
</body>

</html>
