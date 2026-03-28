<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\PlanService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Ensure Pro plan access.
     */
    private function getProShop(Request $request): Shop
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();

        if (!PlanService::hasFeature($shop, 'analytics_full')) {
            abort(403, 'Les rapports sont disponibles avec le plan Pro.');
        }

        return $shop;
    }

    /**
     * Revenue report with breakdown by period.
     */
    public function revenue(Request $request)
    {
        $shop = $this->getProShop($request);
        $period = $request->get('period', '30'); // days

        $startDate = now()->subDays((int) $period);

        // Daily revenue
        $dailyRevenue = $shop->orders()
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders_count')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // Top products
        $topProducts = $shop->orders()
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('product_id, SUM(total) as revenue, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->with('product:id,name,price,images')
            ->get();

        // Digital commissions
        $digitalStats = $shop->digitalSales()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('SUM(sale_amount) as total_sales, SUM(commission_amount) as total_commissions, SUM(seller_amount) as total_net')
            ->first();

        // Totals
        $totalRevenue = $shop->orders()
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $startDate)
            ->sum('total');

        $totalOrders = $shop->orders()
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $startDate)
            ->count();

        return response()->json([
            'period_days' => (int) $period,
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'average_order' => $totalOrders > 0 ? (int) round($totalRevenue / $totalOrders) : 0,
            'daily_revenue' => $dailyRevenue,
            'top_products' => $topProducts,
            'digital' => $digitalStats,
        ]);
    }

    /**
     * Customer report.
     */
    public function customers(Request $request)
    {
        $shop = $this->getProShop($request);

        // Top customers by order count + total spent
        $customers = $shop->orders()
            ->where('status', '!=', 'cancelled')
            ->selectRaw('customer_name, customer_phone, customer_email, COUNT(*) as orders_count, SUM(total) as total_spent, MAX(created_at) as last_order_at')
            ->groupBy('customer_phone', 'customer_name', 'customer_email')
            ->orderByDesc('total_spent')
            ->paginate(20);

        // Summary
        $uniqueCustomers = $shop->orders()
            ->where('status', '!=', 'cancelled')
            ->distinct('customer_phone')
            ->count('customer_phone');

        $repeatCustomers = $shop->orders()
            ->where('status', '!=', 'cancelled')
            ->selectRaw('customer_phone, COUNT(*) as cnt')
            ->groupBy('customer_phone')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        return response()->json([
            'customers' => $customers,
            'unique_customers' => $uniqueCustomers,
            'repeat_customers' => $repeatCustomers,
            'repeat_rate' => $uniqueCustomers > 0 ? round($repeatCustomers / $uniqueCustomers * 100, 1) : 0,
        ]);
    }

    /**
     * Export orders as CSV.
     */
    public function export(Request $request)
    {
        $shop = $this->getProShop($request);

        $orders = $shop->orders()
            ->with('product:id,name')
            ->orderByDesc('created_at')
            ->get();

        $csv = "ID,Date,Client,Téléphone,Email,Produit,Quantité,Total,Statut,Précommande\n";

        foreach ($orders as $order) {
            $csv .= implode(',', [
                $order->id,
                $order->created_at->format('d/m/Y H:i'),
                '"' . str_replace('"', '""', $order->customer_name) . '"',
                $order->customer_phone,
                $order->customer_email ?? '',
                '"' . str_replace('"', '""', $order->product?->name ?? 'Supprimé') . '"',
                $order->quantity,
                $order->total,
                $order->status,
                $order->is_preorder ? 'Oui' : 'Non',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="commandes-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
