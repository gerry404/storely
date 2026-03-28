<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DownloadToken;
use App\Models\Product;
use App\Models\Shop;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DigitalProductController extends Controller
{
    /**
     * Upload/replace a digital file for a product.
     */
    public function upload(Request $request, Product $product)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        abort_if($product->shop_id !== $shop->id, 403);

        if (!PlanService::hasFeature($shop, 'digital_products')) {
            return response()->json([
                'message' => 'Les produits digitaux ne sont pas disponibles pour votre plan.',
                'upgrade_required' => true,
            ], 403);
        }

        $request->validate([
            'digital_file' => 'required|file|max:102400', // 100MB max
        ]);

        // Delete old file if exists
        if ($product->digital_file_path && Storage::disk('local')->exists($product->digital_file_path)) {
            Storage::disk('local')->delete($product->digital_file_path);
        }

        $path = $request->file('digital_file')->store('digital_products', 'local');

        $product->update([
            'digital_file_path' => $path,
            'product_type' => 'digital',
        ]);

        return response()->json([
            'message' => 'Fichier digital uploadé avec succès.',
            'product' => $product->fresh(),
        ]);
    }

    /**
     * Public: download a digital product using a token.
     */
    public function download(string $token)
    {
        $downloadToken = DownloadToken::where('token', $token)->firstOrFail();

        if (!$downloadToken->isValid()) {
            return response()->json([
                'message' => $downloadToken->downloads_remaining <= 0
                    ? 'Nombre de téléchargements maximum atteint.'
                    : 'Le lien de téléchargement a expiré.',
            ], 403);
        }

        $product = $downloadToken->product;

        if (!$product->digital_file_path || !Storage::disk('local')->exists($product->digital_file_path)) {
            return response()->json(['message' => 'Fichier introuvable.'], 404);
        }

        // Consume one download
        $downloadToken->consumeDownload();

        $filename = $product->name . '.' . pathinfo($product->digital_file_path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($product->digital_file_path, $filename);
    }
}
