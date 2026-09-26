<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
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

    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['products' => []]);
        }

        $products = Product::query()
            ->active()
            ->with('category')
            ->where(function ($query) use ($term) {
                $like = '%'.$term.'%';
                $query->where('sku', 'like', $like)
                    ->orWhere('name_ru', 'like', $like)
                    ->orWhere('name_ro', 'like', $like)
                    ->orWhere('description_ru', 'like', $like)
                    ->orWhere('description_ro', 'like', $like);
            })
            ->orderByRaw('CASE WHEN sku = ? THEN 0 WHEN sku LIKE ? THEN 1 ELSE 2 END', [
                $term,
                $term.'%',
            ])
            ->orderBy('sku')
            ->limit(8)
            ->get()
            ->map(fn (Product $product) => [
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

    public function show(string $slug): View
    {
        $product = Product::query()
            ->active()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('catalog.show', compact('product'));
    }

    private function catalog(Request $request, string $slug): View
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();

        $query = Product::query()
            ->active()
            ->where('category_id', $category->id)
            ->orderBy('sku');

        $widths = array_filter((array) $request->input('width', []));
        $colors = array_filter((array) $request->input('color', []));
        $types = array_filter((array) $request->input('type', []));

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
        ));
    }
}
