<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductStockPriceImport implements ToCollection, WithHeadingRow
{
    public array $reportRows = [];
    public int $notFoundCount = 0;
    public int $updatedCount = 0;
    public int $unupdatedCount = 0;
    protected array $updatedProductIds = [];
    protected array $matchedProductIds = [];

    public function collection(Collection $rows)
    {
        $businessId = getBusinessId();

        foreach ($rows as $row) {
            $sku = trim((string)($row['sku'] ?? $row['Sku'] ?? $row['SKU'] ?? ''));
            $nameRaw = trim((string)($row['name'] ?? $row['Name'] ?? $row['NAME'] ?? $row['product_name'] ?? ''));
            $priceRaw = $row['price'] ?? $row['Price'] ?? $row['PRICE'] ?? $row['sell_price'] ?? $row['Sell_Price'] ?? null;
            $qtyRaw = $row['quantity'] ?? $row['Quantity'] ?? $row['QUANTITY'] ?? $row['qty'] ?? $row['Qty'] ?? $row['QTY'] ?? null;
            $weightRaw = $row['weight'] ?? $row['Weight'] ?? $row['WEIGHT'] ?? null;

            if (empty($sku)) {
                $this->reportRows[] = [
                    'sku' => '',
                    'name' => $nameRaw,
                    'weight' => $weightRaw,
                    'price' => $priceRaw,
                    'quantity' => $qtyRaw,
                    'status' => 'Skipped (Blank SKU)'
                ];
                continue;
            }

            $product = Product::where('business_id', $businessId)
                ->where(function ($query) use ($sku) {
                    $query->where('sku', $sku)->orWhere('slug', $sku);
                })->first();

            if (!$product) {
                $this->notFoundCount++;
                $this->reportRows[] = [
                    'sku' => $sku,
                    'name' => $nameRaw,
                    'weight' => $weightRaw,
                    'price' => $priceRaw,
                    'quantity' => $qtyRaw,
                    'status' => 'Not Found'
                ];
                continue;
            }

            $this->matchedProductIds[$product->id] = true;

            $isPriceProvided = !is_null($priceRaw) && trim((string)$priceRaw) !== '';
            $isQtyProvided = !is_null($qtyRaw) && trim((string)$qtyRaw) !== '';
            $isWeightProvided = !is_null($weightRaw) && trim((string)$weightRaw) !== '';

            $updatedFields = [];
            if ($isPriceProvided) {
                $product->sell_price = (float)$priceRaw;
                $updatedFields[] = 'Price';

                Product::where('share_type', 'shared')
                    ->where('parent_product_id', $product->id)
                    ->where('parent_product_business_id', $businessId)
                    ->update([
                        'sell_price' => (float)$priceRaw
                    ]);
            }
            if ($isQtyProvided) {
                $product->quantity = (int)$qtyRaw;
                $updatedFields[] = 'Quantity';
            }
            if ($isWeightProvided) {
                $product->weight = (float)$weightRaw;
                $updatedFields[] = 'Weight';
            }

            if (!empty($updatedFields)) {
                $product->save();
                $this->updatedCount++;
                $this->updatedProductIds[$product->id] = true;
            } else {
                $this->reportRows[] = [
                    'sku' => $sku,
                    'name' => $product->name,
                    'weight' => $weightRaw,
                    'price' => $priceRaw,
                    'quantity' => $qtyRaw,
                    'status' => 'Skipped (Price, Quantity and Weight blank)'
                ];
            }
        }

        // Fetch all products of this business that were not updated in this import
        $unupdatedQuery = Product::where('business_id', $businessId)
            ->select('id', 'name', 'sku', 'slug', 'weight', 'sell_price', 'price', 'quantity')
            ->where('status', 'active')->where('share_type', '!=', 'shared');

        if (!empty($this->updatedProductIds)) {
            $unupdatedQuery->whereNotIn('id', array_keys($this->updatedProductIds));
        }

        $unupdatedProducts = $unupdatedQuery->get();

        foreach ($unupdatedProducts as $p) {
            // If already recorded (e.g. matched in file but skipped due to blank values), count and avoid duplicate
            if (isset($this->matchedProductIds[$p->id])) {
                $this->unupdatedCount++;
                continue;
            }

            $this->unupdatedCount++;
            $this->reportRows[] = [
                'sku' => $p->sku ?: $p->slug,
                'name' => $p->name,
                'weight' => $p->weight,
                'price' => $p->sell_price ?: $p->price,
                'quantity' => $p->quantity,
                'status' => 'Not Updated (Not in import file)'
            ];
        }
    }
}
