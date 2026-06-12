<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::with('category', 'images')->get();
            return response()->json([
                'status'   => 1,
                'message'  => 'Products retrieved successfully',
                'products' => $products
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 0,
                'error'  => $th->getMessage()
            ], 500);
        }
    }

    public function show(Product $product)
    {
        try {
            return response()->json([
                'status'  => 1,
                'message' => 'Product retrieved successfully',
                'product' => $product->load('category', 'images')
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 0,
                'error'  => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'category_id'    => 'nullable|exists:categories,id',
                'name'           => 'required|max:200',
                'description'    => 'nullable',
                'price'          => 'required|numeric',
                'stock_quantity' => 'integer',
                'status'         => 'in:active,inactive'
            ]);

            $product = Product::create($validated);

            if (!$product) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Failed to create product'
                ], 400);
            }

            return response()->json([
                'status'  => 1,
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 0,
                'error'  => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'category_id'    => 'nullable|exists:categories,id',
                'name'           => 'required|max:200',
                'description'    => 'nullable',
                'price'          => 'required|numeric',
                'stock_quantity' => 'integer',
                'status'         => 'in:active,inactive'
            ]);

            $updated = $product->update($validated);

            if (!$updated) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Failed to update product'
                ], 400);
            }

            return response()->json([
                'status'  => 1,
                'message' => 'Product updated successfully',
                'product' => $product
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 0,
                'error'  => $th->getMessage()
            ], 500);
        }
    }

    public function destroy(Product $product)
    {
        try {
            $deleted = $product->delete();

            if (!$deleted) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Failed to delete product'
                ], 400);
            }

            return response()->json([
                'status'  => 1,
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 0,
                'error'  => $th->getMessage()
            ], 500);
        }
    }
}