<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#120F17">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
<title>{{ $title ? $title.' – ' : '' }}{{ config('app.name') }}</title>

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
