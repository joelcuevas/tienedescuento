<?php

namespace App\Crawlers\Palacio;

use App\Models\Url;
use Illuminate\Http\Response;
use Symfony\Component\DomCrawler\Crawler;

class PalacioCategoryCrawler extends PalacioBaseCrawler
{
    protected static ?string $pattern = '#^https://www\.elpalaciodehierro\.com(/[\w\-]+)*/(\?start=\d+&sz=\d+)?(\?prefn1=\w+&prefv1=[\w\-]+)?(\&start=\d+&sz=\d+)?$#';

    protected int $cooldown = 3;

    protected function parse(mixed $dom): int
    {
        $data = $dom->filter('[itemscope][itemtype="http://schema.org/Product"]');

        // check if there is processable data on the page
        if ($data->count() == 0) {
            return Response::HTTP_NO_CONTENT;
        }

        $categories = $this->getCategories($dom->filter('.b-breadcrumbs'));

        $data->each(function (Crawler $item) use ($categories) {
            $this->saveProduct($item, $categories, 'category');
        });

        // check if there's a categories carousel
        $carousel = $dom->filter('.g-carousel-track a');

        if ($carousel->count() > 0) {
            $hrefs = [];

            $carousel->each(function (Crawler $item) use (&$hrefs) {
                $hrefs[] = $item->attr('href');
            });

            foreach (array_unique($hrefs) as $href) {
                Url::resolve($href, 20);
            }
        }

        // check if there's a next category page
        $nextPage = $dom->filter('.b-next-btn > a');

        if ($nextPage->count() > 0) {
            $href = $nextPage->eq(0)->attr('data-permalink');
            $nextUrl = Url::resolve($href, 20);

            // force-discover the next page
            if ($nextUrl->status != Response::HTTP_OK) {
                $nextUrl->scheduleNow();
            }
        }

        // we are done!
        return Response::HTTP_OK;
    }
}
