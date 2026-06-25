<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    /**
     * Display a listing of active products.
     */
    public function index()
    {
        $products = Product::where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('products.index', ['products' => $products]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'sku' => 'required|unique:products,sku',
            'price' => 'required|numeric',
            'image' => 'image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->route('products.create')->withErrors($validator)->withInput();
        }

        $product = new Product();
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->save();

        if ($request->hasFile('image')) {
            $image = $request->image;
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/products'), $imageName);
            $product->image = $imageName;
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing.
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', ['product' => $product]);
    }

    /**
     * Update product data (only data fields, not status logic).
     */
    public function update($id, Request $request)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'sku' => 'required|unique:products,sku,' . $product->id,
            'price' => 'required|numeric',
            'image' => 'image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->route('products.edit', $product->id)->withErrors($validator)->withInput();
        }

        $product->update($request->except('image'));

        if ($request->hasFile('image')) {
            if ($product->image && File::exists(public_path('uploads/products/' . $product->image))) {
                File::delete(public_path('uploads/products/' . $product->image));
            }
            $image = $request->image;
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/products'), $imageName);
            $product->image = $imageName;
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Move to trash (Soft Delete).
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->status = 0; // Move to trash status
        $product->save();
        $product->delete(); // Triggers soft delete

        return redirect()->route('products.index')->with('success', 'Product moved to trash.');
    }

    /**
     * Display trash bin.
     */
    public function trash()
    {
        // Auto-archive products that were moved to trash > 30 days ago
        Product::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->update(['status' => 2, 'deleted_at' => null]);

        $products = Product::where('status', 0)->onlyTrashed()->get();
        return view('products.trash', ['products' => $products]);
    }

    /**
     * Restore item from trash.
     */
    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->update(['status' => 1]); // Set back to active
        $product->restore();

        return redirect()->route('products.index')->with('success', 'Product restored!');
    }

    /**
     * Permanently remove from database.
     */
    /**
     * Permanently mark as status 2 (Archived/Permanently Deleted)
     */
    public function forceDelete($id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        // 1. Delete the physical image file
        if ($product->image && File::exists(public_path('uploads/products/' . $product->image))) {
            File::delete(public_path('uploads/products/' . $product->image));
            $product->image = null; // Clear the image path
        }

        // 2. Instead of removing the row, update status to 2
        $product->update([
            'status' => 2,
            'deleted_at' => null // Clear the soft-delete timestamp so it's no longer "trashed"
        ]);

        return redirect()->route('products.trash')->with('success', 'Product permanently archived .');
    }
}
