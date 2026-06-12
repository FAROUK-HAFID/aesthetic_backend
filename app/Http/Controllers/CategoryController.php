<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::with('children')->get();
            return response()->json([
                'status'     => 1,
                'message'    => 'Categories retrieved successfully',
                'categories' => $categories
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
                'name'        => 'required|max:150',
                'description' => 'nullable',
                'parent_id'   => 'nullable|exists:categories,id'
            ]);

            $category = Category::create($validated);

            if (!$category) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Failed to create category'
                ], 400);
            }

            return response()->json([
                'status'   => 1,
                'message'  => 'Category created successfully',
                'category' => $category
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 0,
                'error'  => $th->getMessage()
            ], 500);
        }
    }

    public function show(Category $category)
    {
        try {
            return response()->json([
                'status'   => 1,
                'message'  => 'Category retrieved successfully',
                'category' => $category->load('children')
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 0,
                'error'  => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Category $category)
    {
        try {
            $validated = $request->validate([
                'name'        => 'required|max:150',
                'description' => 'nullable',
                'parent_id'   => 'nullable|exists:categories,id'
            ]);

            $updated = $category->update($validated);

            if (!$updated) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Failed to update category'
                ], 400);
            }

            return response()->json([
                'status'   => 1,
                'message'  => 'Category updated successfully',
                'category' => $category
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 0,
                'error'  => $th->getMessage()
            ], 500);
        }
    }

    public function destroy(Category $category)
    {
        try {
            $deleted = $category->delete();

            if (!$deleted) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Failed to delete category'
                ], 400);
            }

            return response()->json([
                'status'  => 1,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 0,
                'error'  => $th->getMessage()
            ], 500);
        }
    }
}