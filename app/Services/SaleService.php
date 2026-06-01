<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Exception;

class SaleService
{
    /**
     * Create a new sale with its items.
     *
     * @param int $userId
     * @param array $data
     * @return Sale
     * @throws Exception
     */
    public function createSale(int $userId, array $data): Sale
    {
        DB::beginTransaction();
        try {
            // Generate Sale Code: SL-YYYYMMDD-UUID
            $datePrefix = date('Ymd');
            $randomSuffix = strtoupper(substr(uniqid(), -5));
            $saleCode = "SL-{$datePrefix}-{$randomSuffix}";

            $sale = Sale::create([
                'user_id' => $userId,
                'sale_code' => $saleCode,
                'total_cost' => 0,
                'total_revenue' => 0,
                'gross_profit' => 0,
                'notes' => $data['notes'] ?? null,
                'sale_date' => $data['sale_date'],
            ]);

            $totalCost = 0;
            $totalRevenue = 0;
            $grossProfit = 0;

            foreach ($data['items'] as $itemData) {
                $productId = $itemData['product_id'] ?? null;
                $productName = $itemData['product_name'];
                $sellPrice = $itemData['sell_price'];
                $quantity = $itemData['quantity'];

                if ($productId) {
                    $product = Product::where('user_id', $userId)->find($productId);
                    if ($product) {
                        $costPrice = $product->cost_price;
                        $productName = $product->name;
                    } else {
                        $costPrice = 0;
                        $productId = null;
                    }
                } else {
                    $product = Product::where('user_id', $userId)
                        ->where('name', $productName)
                        ->first();
                    if (!$product) {
                        $product = Product::where('user_id', $userId)
                            ->whereRaw('LOWER(name) = ?', [strtolower($productName)])
                            ->first();
                    }
                    if ($product) {
                        $productId = $product->id;
                        $costPrice = $product->cost_price;
                        $productName = $product->name;
                    } else {
                        $costPrice = 0;
                    }
                }

                $subtotalCost = $costPrice * $quantity;
                $subtotalRevenue = $sellPrice * $quantity;
                $subtotalProfit = $subtotalRevenue - $subtotalCost;

                $sale->items()->create([
                    'product_id' => $productId,
                    'product_name' => $productName,
                    'cost_price' => $costPrice,
                    'sell_price' => $sellPrice,
                    'quantity' => $quantity,
                    'subtotal_cost' => $subtotalCost,
                    'subtotal_revenue' => $subtotalRevenue,
                    'subtotal_profit' => $subtotalProfit,
                ]);

                $totalCost += $subtotalCost;
                $totalRevenue += $subtotalRevenue;
                $grossProfit += $subtotalProfit;
            }

            // Update main sale totals
            $sale->update([
                'total_cost' => $totalCost,
                'total_revenue' => $totalRevenue,
                'gross_profit' => $grossProfit,
            ]);

            DB::commit();

            return $sale->load('items');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing sale and recalculate its items and totals.
     */
    public function updateSale(Sale $sale, array $data): Sale
    {
        DB::beginTransaction();
        try {
            // Delete old items
            $sale->items()->delete();

            $totalCost = 0;
            $totalRevenue = 0;
            $grossProfit = 0;

            foreach ($data['items'] as $itemData) {
                $productId = $itemData['product_id'] ?? null;
                $productName = $itemData['product_name'];
                $sellPrice = $itemData['sell_price'];
                $quantity = $itemData['quantity'];

                if ($productId) {
                    $product = Product::where('user_id', $sale->user_id)->find($productId);
                    if ($product) {
                        $costPrice = $product->cost_price;
                        $productName = $product->name;
                    } else {
                        $costPrice = 0;
                        $productId = null;
                    }
                } else {
                    $product = Product::where('user_id', $sale->user_id)
                        ->where('name', $productName)
                        ->first();
                    if (!$product) {
                        $product = Product::where('user_id', $sale->user_id)
                            ->whereRaw('LOWER(name) = ?', [strtolower($productName)])
                            ->first();
                    }
                    if ($product) {
                        $productId = $product->id;
                        $costPrice = $product->cost_price;
                        $productName = $product->name;
                    } else {
                        $costPrice = 0;
                    }
                }

                $subtotalCost = $costPrice * $quantity;
                $subtotalRevenue = $sellPrice * $quantity;
                $subtotalProfit = $subtotalRevenue - $subtotalCost;

                $sale->items()->create([
                    'product_id' => $productId,
                    'product_name' => $productName,
                    'cost_price' => $costPrice,
                    'sell_price' => $sellPrice,
                    'quantity' => $quantity,
                    'subtotal_cost' => $subtotalCost,
                    'subtotal_revenue' => $subtotalRevenue,
                    'subtotal_profit' => $subtotalProfit,
                ]);

                $totalCost += $subtotalCost;
                $totalRevenue += $subtotalRevenue;
                $grossProfit += $subtotalProfit;
            }

            // Update main sale totals
            $sale->update([
                'total_cost' => $totalCost,
                'total_revenue' => $totalRevenue,
                'gross_profit' => $grossProfit,
                'notes' => $data['notes'] ?? null,
                'sale_date' => $data['sale_date'],
            ]);

            DB::commit();

            return $sale->load('items');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
