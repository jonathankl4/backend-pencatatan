<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Exception;

class SaleController extends Controller
{
    protected SaleService $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $request->user()->sales()->with('items');

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('sale_date', [$request->start_date, $request->end_date]);
        }

        $sales = $query->latest()->get();
        return SaleResource::collection($sales);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleRequest $request)
    {
        try {
            $sale = $this->saleService->createSale($request->user()->id, $request->validated());
            return new SaleResource($sale);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to process sale.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $sale = $request->user()->sales()->with('items')->findOrFail($id);
        return new SaleResource($sale);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $sale = $request->user()->sales()->findOrFail($id);
        $sale->delete();
        return response()->noContent();
    }

    /**
     * Get unique product recommendations and their latest sell prices.
     */
    public function suggestions(Request $request)
    {
        $userId = $request->user()->id;

        $products = \DB::table('products')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->select('name as product_name', 'sell_price')
            ->get();

        $saleItems = \DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->select('sale_items.product_name', 'sale_items.sell_price')
            ->orderBy('sales.sale_date', 'desc')
            ->orderBy('sales.created_at', 'desc')
            ->get();

        $suggestions = $products->concat($saleItems)
            ->unique('product_name')
            ->values();

        return response()->json([
            'data' => $suggestions
        ]);
    }
}
