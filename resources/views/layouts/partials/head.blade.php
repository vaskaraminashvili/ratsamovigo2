<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#120F17">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
<title>{{ $title ? $title.' – ' : '' }}{{ config('app.name') }}</title>
@php($description = 'ერთი საერთო საყიდლების სია მთელი ოჯახისთვის. ვინც სახლშია, ამატებს, ვინც მაღაზიაშია, ყიდულობს. აღარ დარეკო, რომ იკითხო, რა იყიდო.')
<meta name="description" content="{{ $description }}">
{{-- Link previews on Facebook, Messenger, Viber and X --}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:locale" content="ka_GE">
<meta property="og:title" content="{{ $title ? $title.' – ' : '' }}{{ config('app.name') }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ url('/og.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">

<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/favicon-48.png" type="image/png" sizes="48x48">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="preload" href="/fonts/noto-sans-georgian-georgian.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/fonts/bricolage-grotesque-latin.woff2" as="font" type="font/woff2" crossorigin>

{{-- Theme boot: runs before paint so there is no flash. Server theme is the fallback when the browser has no saved choice. --}}
<script>
(function () {
  var KEY = 'rw-theme', mq = matchMedia('(prefers-color-scheme: light)'), choice = @js(auth()->user()?->theme ?? 'system');
  try { choice = localStorage.getItem(KEY) || choice; } catch (e) {}
  function resolve() {
    if (choice !== 'system') return choice;
    return mq.matches ? 'matsoni' : 'night';
  }
  function apply() {
    document.documentElement.setAttribute('data-palette', resolve());
    // SVG backdrop refraction only renders in Chromium; other engines keep the plain blur.
    var brands = (navigator.userAgentData && navigator.userAgentData.brands) || [];
    document.documentElement.toggleAttribute('data-refract', brands.some(function (b) { return b.brand === 'Chromium'; }));
    var m = document.querySelector('meta[name="theme-color"]');
    if (m) m.setAttribute('content', getComputedStyle(document.documentElement).getPropertyValue('--ink-950').trim() || '#120F17');
  }
  apply();
  mq.addEventListener('change', function () { if (choice === 'system') apply(); });
  window.rwTheme = {
    get: function () { return choice; },
    resolved: resolve,
    apply: apply,
    set: function (v) { choice = v; try { localStorage.setItem(KEY, v); } catch (e) {} apply(); }
  };
})();
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
