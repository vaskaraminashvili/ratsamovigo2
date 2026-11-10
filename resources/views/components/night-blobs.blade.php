{{-- Slow milky blobs behind the page. Hidden by CSS unless the night palette is active. --}}
<div class="night-blobs" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
<script>
(function () {
  // One palette per full page load (a refresh picks a new one); wire:navigate reuses it so colours do not jump between pages.
  var sets = [
    ['205,220,225', '243,222,192', '70,125,140', '190,205,215', '250,230,205'],
    ['190,170,235', '240,190,215', '95,85,170', '200,185,225', '250,215,230'],
    ['190,235,215', '215,240,200', '50,140,130', '185,225,210', '230,245,215'],
    ['245,200,180', '250,225,190', '150,80,90', '240,210,200', '255,230,200'],
    ['175,205,245', '215,225,250', '55,90,160', '190,210,240', '225,235,255'],
    ['200,180,240', '170,230,225', '70,110,150', '215,200,235', '190,235,240']
  ];
  window.rwBlobSet = window.rwBlobSet || sets[Math.floor(Math.random() * sets.length)];
  var el = document.currentScript.previousElementSibling;
  window.rwBlobSet.forEach(function (c, i) { el.style.setProperty('--blob-' + (i + 1), c); });
})();
</script>
