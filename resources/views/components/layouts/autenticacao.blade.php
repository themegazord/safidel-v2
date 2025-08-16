<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-base-200/50 dark:bg-base-200">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Flatpickr  --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>

  <script>
    flatpickr.localize(flatpickr.l10ns.pt);
  </script>

</head>
<body class="font-sans antialiased">
{{ $slot }}
{{--Toast--}}
<x-toast/>
</body>
</html>
