<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Looks up grocery products and their store prices in the tarifebi.ge catalog.
 *
 * @phpstan-type Suggestion array{name: string, unit: ?string, price: ?string, store_name: string, store_slug: ?string, product_id: int, url: ?string, caption: ?string}
 */
class ProductSearch
{
    public const LIMIT = 5;

    public const MIN_LENGTH = 3;

    public function __construct(private GeorgianLatin $latin) {}

    /**
     * @return list<array{name: string, unit: ?string, price: ?string, store_name: string, store_slug: ?string, product_id: int, url: ?string, caption: ?string}>
     */
    public function suggestions(string $query): array
    {
        $query = trim($query);

        if (mb_strlen($query) < self::MIN_LENGTH) {
            return [];
        }

        $georgian = trim($this->latin->toGeorgian($query));

        if (mb_strlen($georgian) < self::MIN_LENGTH) {
            return [];
        }

        $key = 'product-search.'.sha1($georgian);
        $cached = Cache::get($key);

        if (is_array($cached)) {
            /** @var list<array{name: string, unit: ?string, price: ?string, store_name: string, store_slug: ?string, product_id: int, url: ?string, caption: ?string}> $cached */
            return $cached;
        }

        $rows = $this->fetch($georgian);

        if ($rows === null) {
            return [];
        }

        Cache::put($key, $rows, now()->addMinutes(10));

        return $rows;
    }

    /**
     * @return list<array{name: string, unit: ?string, price: ?string, store_name: string, store_slug: ?string, product_id: int, url: ?string, caption: ?string}>|null
     */
    private function fetch(string $query): ?array
    {
        try {
            $response = Http::timeout((int) config('services.tarifebi.timeout', 3))
                ->connectTimeout(2)
                ->acceptJson()
                ->get((string) config('services.tarifebi.url'), [
                    'store_type' => 'grocery',
                    'q' => $query,
                    'limit' => self::LIMIT,
                    'group' => 1,
                    'sort' => 'sale',
                ]);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();

        if (! is_array($payload) || ! ($payload['success'] ?? false)) {
            return null;
        }

        $products = $payload['products'] ?? [];

        return $this->rows(is_array($products) ? $products : []);
    }

    /**
     * @param  array<mixed>  $products
     * @return list<array{name: string, unit: ?string, price: ?string, store_name: string, store_slug: ?string, product_id: int, url: ?string, caption: ?string}>
     */
    private function rows(array $products): array
    {
        $rows = [];

        foreach ($products as $product) {
            if (! is_array($product)) {
                continue;
            }

            $name = trim((string) ($product['name'] ?? ''));
            $store = trim((string) ($product['store_name'] ?? ''));

            if ($name === '' || $store === '') {
                continue;
            }

            $unit = trim((string) ($product['unit'] ?? ''));
            $slug = trim((string) ($product['store_slug'] ?? ''));
            $price = $this->price($product);
            $unit = $unit === '' ? null : mb_substr($unit, 0, 40);
            $store = mb_substr($store, 0, 80);

            $rows[] = [
                'name' => mb_substr($name, 0, 120),
                'unit' => $unit,
                'price' => $price,
                'store_name' => $store,
                'store_slug' => $slug === '' ? null : mb_substr($slug, 0, 40),
                'product_id' => max(0, (int) ($product['id'] ?? 0)),
                'url' => $this->url($product),
                'caption' => CatalogLine::format($price, $store, $unit),
            ];

            if (count($rows) === self::LIMIT) {
                break;
            }
        }

        return $rows;
    }

    /**
     * The sale price when there is one, otherwise the regular price.
     *
     * @param  array<mixed>  $product
     */
    private function price(array $product): ?string
    {
        $sale = $product['sale_price'] ?? null;
        $original = $product['original_price'] ?? null;
        $value = ($sale !== null && $sale !== '') ? $sale : $original;

        if (! is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    /**
     * @param  array<mixed>  $product
     */
    private function url(array $product): ?string
    {
        $url = $product['product_url'] ?? null;

        if (! is_string($url) || ! str_starts_with($url, 'https://') || mb_strlen($url) > 255) {
            return null;
        }

        return $url;
    }
}
