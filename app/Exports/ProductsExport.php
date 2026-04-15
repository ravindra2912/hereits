<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Product::with(['category', 'images' => function ($q) {
            $q->orderBy('sort_order', 'asc');
        }])
            ->where('business_id', getBusinessId())
            ->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'Name',
            'Category',
            'SKU',
            'Description',
            'Price Type',
            'Price',
            'Sell Price',
            'Min Price',
            'Max Price',
            'Image 1',
            'Image 2',
            'Image 3',
            'Image 4',
            'Image 5'
        ];
    }

    public function map($row): array
    {
        $data = [
            $row->name,
            $row->category ? $row->category->name : '',
            $row->sku,
            $row->description,
            $row->price_type,
            $row->price,
            $row->sell_price,
            $row->min_price,
            $row->max_price
        ];

        // Add up to 5 image URLs
        for ($i = 0; $i < 5; $i++) {
            $imageUrl = '';
            if (isset($row->images[$i])) {
                $url = $row->images[$i]->image_url;
                if (str_contains($url, 'https://') || str_contains($url, 'http://')) {
                    $imageUrl = $url;
                } else {
                    $imageUrl = asset('storage/' . $url);
                    //check if image is not exist
                    // if (!file_exists($imageUrl)) {
                    //     $imageUrl = '';
                    // }
                }
            }
            $data[] = $imageUrl;
        }

        return $data;
    }
}
