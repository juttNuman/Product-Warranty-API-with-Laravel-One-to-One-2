<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Warranty;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('warranty')->get();

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products available',
                'data' => []
            ], 404);
        }

        return ProductResource::collection($products);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'warranty_period' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product = Product::create([
                'name' => $request->input('name'),
                'price' => $request->input('price'),
            ]);

            if (!$product) {
                return response()->json([
                    'message' => 'Product not saved',
                ], 500);
            }

            if ($request->has('warranty_period')) {
                $warranty = new Warranty([
                    'warranty_period' => $request->input('warranty_period'),
                    'product_id' => $product->id,
                ]);

                $product->warranty()->save($warranty);
            }

            return response()->json([
                'message' => 'Product added successfully',
                'data' => new ProductResource($product)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::with('warranty')->find($id);

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }

            return new ProductResource($product);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving the product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'price' => 'sometimes|required|numeric',
                'warranty_period' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $product->update($request->only(['name', 'price']));

            if ($request->has('warranty_period')) {
                $warranty = $product->warranty;

                if ($warranty) {
                    $warranty->update([
                        'warranty_period' => $request->input('warranty_period'),
                    ]);
                } else {
                    $warranty = new Warranty([
                        'warranty_period' => $request->input('warranty_period'),
                    ]);
                    $product->warranty()->save($warranty);
                }
            }

            return new ProductResource($product);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }

            $product->warranty()->delete();
            $product->delete();

            return response()->json([
                'message' => 'Product and associated warranty deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
