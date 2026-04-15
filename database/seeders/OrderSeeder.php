<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderHistory;
use App\Models\Product;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $business_id = 1000;
        $products = Product::where('business_id', $business_id)->get();

        if ($products->isEmpty()) {
            $this->command->info('No products found for business 1000. Please seed products first.');
            return;
        }

        $order_statuses = config('const.order_status');
        $payment_statuses = config('const.order_payment_status');
        $payment_methods = config('const.order_payment_method');
        $order_sources = config('const.order_source');
        $order_types = config('const.order_type');

        for ($i = 0; $i < 20; $i++) {
            $subtotal = 0;
            $items_to_add = [];
            
            // Randomly select 1 to 3 products
            foreach ($products->random(rand(1, 3)) as $product) {
                $qty = rand(1, 5);
                $price = $product->sell_price ?: $product->price ?: 100;
                $subtotal += ($price * $qty);
                
                $items_to_add[] = [
                    'business_id' => $business_id,
                    'item_id' => $product->id,
                    'item_name' => $product->name,
                    'price' => $price,
                    'quantity' => $qty,
                ];
            }

            $tax_rate = 0.18; // 18%
            $total_tax = $subtotal * $tax_rate;
            $total = $subtotal + $total_tax;

            $order_status = $order_statuses[array_rand($order_statuses)];
            $payment_status = $payment_statuses[array_rand($payment_statuses)];

            $order = Order::create([
                'business_id' => $business_id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'order_source' => $order_sources[array_rand($order_sources)],
                'order_type' => $order_types[array_rand($order_types)],
                'customer_name' => 'Customer ' . ($i + 1),
                'customer_contact' => '988776655' . rand(0, 9),
                'address' => 'Sample Address ' . rand(1, 100),
                'city' => 'Bhavnagar',
                'state' => 'Gujarat',
                'pincode' => '364002',
                'subtotal' => $subtotal,
                'total_tax' => $total_tax,
                'total' => $total,
                'payment_method' => $payment_methods[array_rand($payment_methods)],
                'payment_status' => $payment_status,
                'order_status' => $order_status,
            ]);

            foreach ($items_to_add as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
            }

            OrderHistory::create([
                'business_id' => $business_id,
                'order_id' => $order->id,
                'status' => 'pending',
                'remark' => 'Order created via seeder',
            ]);

            if ($order_status != 'pending') {
                OrderHistory::create([
                    'business_id' => $business_id,
                    'order_id' => $order->id,
                    'status' => $order_status,
                    'remark' => 'Status updated to ' . $order_status,
                ]);
            }
        }
    }
}
