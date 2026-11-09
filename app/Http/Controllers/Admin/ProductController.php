<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim($request->string('q')->toString());
        $image = $request->string('image', 'all')->toString();
        if (! in_array($image, ['all', 'missing', 'present'], true)) {
            $image = 'all';
        }

        $products = Product::query()
            ->with('category')
            ->when($q !== '', function ($query) use ($q) {
                $term = '%'.$q.'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('sku', 'like', $term)
                        ->orWhere('name_ru', 'like', $term)
                        ->orWhere('name_ro', 'like', $term);
                });
            })
            ->when($image === 'missing', fn ($query) => $query->where(function ($sub) {
                $sub->whereNull('image_path')->orWhere('image_path', '');
            }))
            ->when($image === 'present', fn ($query) => $query
                ->whereNotNull('image_path')
                ->where('image_path', '!=', ''))
            ->orderBy('sku')
            ->paginate(50)
            ->withQueryString();

        $total = Product::query()->count();
        $missing = Product::query()
            ->where(function ($sub) {
                $sub->whereNull('image_path')->orWhere('image_path', '');
            })
            ->count();

        return view('admin.products.index', compact('products', 'q', 'image', 'total', 'missing'));
    }

    public function edit(Product $product): View
    {
        $product->load('category');

        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:100'],
            'name_ru' => ['required', 'string', 'max:255'],
            'name_ro' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'image_url' => ['nullable', 'url', 'max:2048', 'starts_with:http://,https://'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $product->sku = trim($data['sku']);
        $product->name_ru = trim($data['name_ru']);
        $product->name_ro = filled($data['name_ro'] ?? null) ? trim($data['name_ro']) : null;

        $oldImage = $product->image_path;
        if ($request->hasFile('image')) {
            $product->image_path = $request->file('image')->store('products', 'public');
        } elseif (filled($data['image_url'] ?? null)) {
            $product->image_path = trim($data['image_url']);
        } elseif ($request->boolean('remove_image')) {
            $product->image_path = null;
        }

        $product->save();

        if ($oldImage !== $product->image_path) {
            $this->deleteLocalImage($oldImage);
        }

        Cache::forget('sitemap.xml');

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', __('site.admin_product_saved'));
    }

    public function destroyImage(Product $product): RedirectResponse
    {
        $oldImage = $product->image_path;
        $product->image_path = null;
        $product->save();

        $this->deleteLocalImage($oldImage);
        Cache::forget('sitemap.xml');

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', __('site.admin_product_image_removed'));
    }

    private function deleteLocalImage(?string $path): void
    {
        if (blank($path) || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
