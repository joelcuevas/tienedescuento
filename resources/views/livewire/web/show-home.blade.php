<div class="space-y-10">
    @if (config('ads.enabled'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9285674270424452" crossorigin="anonymous"></script>
        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-9285674270424452"
            data-ad-slot="3526061510"
            data-ad-format="auto"
            data-full-width-responsive="true">
        </ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    @endif
    @foreach ($featured as $category => $products)
        <div>
            <h1 class="text-base font-medium text-gray-900 mb-4">{{ $category }}</h1>
            <x-product-grid :$products />
        </div>
    @endforeach
</div>
