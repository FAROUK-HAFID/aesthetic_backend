<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $path = $request->file('image')->store('products', 'public');

        $image = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $path
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully',
            'image' => $image,
            'url' => asset('storage/' . $path)
        ]);
    }

    public function destroy($id)
    {
        $image = ProductImage::findOrFail($id);

        Storage::disk('public')->delete($image->image_path);

        $image->delete();

        return response()->json([
            'message' => 'Image deleted successfully'
        ]);
    }

    public function uploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $uploaded = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('products', 'public');

            $uploaded[] = $product->images()->create([
                'image_path' => $path
            ]);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Images uploaded successfully',
            'images' => $uploaded
        ]);
    }
}