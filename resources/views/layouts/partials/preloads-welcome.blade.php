{{-- Preloads specific to the welcome (home) landing experience --}}
<link rel="preload" href="{{ Vite::asset('resources/css/welcome.css') }}" as="style"
  onload="this.onload=null;this.rel='stylesheet'">
<noscript>
  <link rel="stylesheet" href="{{ Vite::asset('resources/css/welcome.css') }}">
</noscript>

<link rel="prefetch" href="{{ Vite::asset('resources/css/app-v3.css') }}" as="style" crossorigin>
