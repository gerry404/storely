<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryZoneController extends Controller
{
    public function index(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        return response()->json(
            $shop->deliveryZones()->orderBy('sort_order')->orderBy('id')->get()
        );
    }

    public function store(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'price' => 'required|integer|min:0|max:10000000',
            'estimated_hours' => 'nullable|integer|min:1|max:720',
            'is_default' => 'sometimes|boolean',
            'active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        return DB::transaction(function () use ($shop, $data) {
            if (!empty($data['is_default'])) {
                $shop->deliveryZones()->update(['is_default' => false]);
            }

            $zone = $shop->deliveryZones()->create($data);
            return response()->json($zone, 201);
        });
    }

    public function update(Request $request, DeliveryZone $zone)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        abort_if($zone->shop_id !== $shop->id, 403);

        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:255',
            'price' => 'sometimes|integer|min:0|max:10000000',
            'estimated_hours' => 'nullable|integer|min:1|max:720',
            'is_default' => 'sometimes|boolean',
            'active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        return DB::transaction(function () use ($shop, $zone, $data) {
            if (!empty($data['is_default'])) {
                $shop->deliveryZones()->where('id', '!=', $zone->id)->update(['is_default' => false]);
            }

            $zone->update($data);
            return response()->json($zone->fresh());
        });
    }

    public function destroy(Request $request, DeliveryZone $zone)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();
        abort_if($zone->shop_id !== $shop->id, 403);

        $zone->delete();
        return response()->json(['message' => 'Zone supprimée']);
    }
}
