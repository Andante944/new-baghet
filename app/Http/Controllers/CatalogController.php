<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function home(): View
    {
        return view('home');
    }

    public function plastic(Request $request): View
    {
        return $this->catalog($request, 'plastic');
    }

    public function hardware(Request $request): View
    {
        return $this->catalog($request, 'hardware');
    }

    public function search(Request $request): JsonResponse|View
    {
        $term = trim((string) $request->query('q', ''));

        $query = Product::query()
            ->active()
            ->with('category');

        if (mb_strlen($term) >= 2) {
            $query
                ->where(function ($sub) use ($term) {
                    $like = '%'.$term.'%';
                    $sub->where('sku', 'like', $like)
                        ->orWhere('name_ru', 'like', $like)
                        ->orWhere('name_ro', 'like', $like)
                        ->orWhere('description_ru', 'like', $like)
                        ->orWhere('description_ro', 'like', $like);
                })
                ->orderByRaw('CASE WHEN sku = ? THEN 0 WHEN sku LIKE ? THEN 1 ELSE 2 END', [
                    $term,
                    $term.'%',
                ])
                ->orderBy('sku');
        } else {
            $query->whereRaw('1 = 0');
        }

        // Подсказки в шапке (fetch с Accept: application/json) — короткий список.
        if ($request->expectsJson()) {
            $products = $query->limit(8)->get()->map(fn (Product $product) => [
                'name' => $product->localizedName(),
                'sku' => $product->sku,
                'category' => $product->category->localizedName(),
                'price' => number_format($product->displayPrice(), 2),
                'currency' => 'USD',
                'image' => $product->imageUrl(),
                'url' => route('catalog.show', $product->slug),
                'in_stock' => $product->inStock(),
            ]);

            return response()
                ->json(['products' => $products])
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        // Enter в поиске — страница со всеми результатами.
        $products = $query->paginate(24)->withQueryString();

        return view('catalog.search', compact('products', 'term'));
    }

    public function show(string $slug): View|RedirectResponse
    {
        $product = Product::query()
            ->active()
            ->with('category')
            ->where('slug', $slug)
            ->first();

        if ($product === null) {
            $inactive = Product::query()->with('category')->where('slug', $slug)->first();
            if ($inactive !== null) {
                return redirect()->route(
                    $inactive->category?->slug === 'hardware' ? 'catalog.hardware' : 'catalog.plastic',
                    [],
                    301,
                );
            }

            abort(404);
        }

        $canonical = $this->canonicalProduct($product);
        if ($canonical->id !== $product->id) {
            return redirect()->route('catalog.show', $canonical->slug, 301);
        }

        $related = $this->relatedProducts($product);

        return view('catalog.show', compact('product', 'related'));
    }

    /**
     * One page per SKU: when imports left duplicate cards, the CRM-bound card
     * wins, then the one with a photo, then the oldest id.
     */
    private function canonicalProduct(Product $product): Product
    {
        $sku = trim((string) $product->sku);
        if ($sku === '') {
            return $product;
        }

        return Product::query()
            ->active()
            ->where('sku', $sku)
            ->orderByRaw('CASE WHEN crm_product_id IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw("CASE WHEN image_path IS NULL OR image_path = '' THEN 1 ELSE 0 END")
            ->orderBy('id')
            ->first() ?? $product;
    }

    /**
     * Similar products by SKU family: 20-02-* first, then the whole 20-* width
     * group, then the category as a last resort. Hardware and items without a
     * numeric article simply fall back to their category.
     *
     * @return Collection<int, Product>
     */
    private function relatedProducts(Product $product, int $limit = 4): Collection
    {
        $base = Product::query()
            ->active()
            ->with('category')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id);

        $picked = new Collection;

        foreach ($this->skuPrefixPatterns($product->sku) as $pattern) {
            if ($picked->count() >= $limit) {
                break;
            }

            $rows = (clone $base)
                ->where('sku', 'like', $pattern)
                ->orderByDesc('stock')
                ->orderBy('sku')
                ->limit($limit * 2)
                ->get();

            $picked = $picked
                ->merge($rows)
                ->unique(fn (Product $row) => mb_strtolower((string) $row->sku))
                ->take($limit)
                ->values();
        }

        if ($picked->isEmpty()) {
            $picked = (clone $base)
                ->orderByDesc('stock')
                ->orderBy('sku')
                ->limit($limit)
                ->get();
        }

        return $picked->values();
    }

    /**
     * @return list<string>
     */
    private function skuPrefixPatterns(?string $sku): array
    {
        $sku = trim((string) $sku);
        if (preg_match('/^(\d+)(?:-(\d+))?/', $sku, $matches) !== 1) {
            return [];
        }

        $patterns = [];
        if (isset($matches[2]) && $matches[2] !== '') {
            $patterns[] = $matches[1].'-'.$matches[2].'%';
        }
        $patterns[] = $matches[1].'-%';

        return $patterns;
    }

    private function catalog(Request $request, string $slug): View
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();

        $query = Product::query()
            ->active()
            ->where('category_id', $category->id);

        $widths = array_filter((array) $request->input('width', []));
        $colors = array_filter((array) $request->input('color', []));
        $types = array_filter((array) $request->input('type', []));

        // Мини-фильтр: порядок товаров (по умолчанию — по артикулу).
        $sort = (string) $request->query('sort', 'default');
        $sort = in_array($sort, ['price_asc', 'price_desc', 'name', 'new'], true) ? $sort : 'default';

        match ($sort) {
            'price_asc' => $query->orderBy('price')->orderBy('sku'),
            'price_desc' => $query->orderByDesc('price')->orderBy('sku'),
            'name' => $query->orderBy('name_ru'),
            'new' => $query->orderByDesc('id'),
            default => $query->orderBy('sku'),
        };

        if ($widths) {
            $query->whereIn('width', $widths);
        }
        if ($colors) {
            $query->whereIn('color', $colors);
        }
        if ($types) {
            $query->whereIn('type', $types);
        }

        $products = $query->paginate(24)->withQueryString();

        $availableWidths = Product::query()
            ->where('category_id', $category->id)
            ->whereNotNull('width')
            ->distinct()
            ->orderBy('width')
            ->pluck('width');

        $availableColors = Product::query()
            ->where('category_id', $category->id)
            ->whereNotNull('color')
            ->distinct()
            ->orderBy('color')
            ->pluck('color');

        $availableTypes = Product::query()
            ->where('category_id', $category->id)
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('catalog.index', compact(
            'category',
            'products',
            'availableWidths',
            'availableColors',
            'availableTypes',
            'widths',
            'colors',
            'types',
            'sort',
        ));
    }
}
