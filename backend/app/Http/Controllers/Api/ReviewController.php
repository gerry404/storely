<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $shop = Shop::where('slug', $slug)->firstOrFail();

        $request->validate([
            'author_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'product_id' => 'nullable|exists:products,id',
            'images' => 'nullable|array|max:4',
            'images.*' => 'image|max:5120',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store("reviews/{$shop->id}", 'public');
            }
        }

        $review = $shop->reviews()->create([
            'author_name' => $request->author_name,
            'author_phone' => $request->author_phone,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'product_id' => $request->product_id,
            'images' => !empty($imagePaths) ? $imagePaths : null,
        ]);

        // Update shop rating
        $shop->update([
            'rating' => $shop->reviews()->avg('rating'),
            'reviews_count' => $shop->reviews()->count(),
        ]);

        return response()->json($review, 201);
    }
}
