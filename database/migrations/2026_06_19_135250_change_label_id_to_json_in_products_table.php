<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK if it still exists (may have been removed already)
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign('products_label_id_foreign');
            });
        } catch (\Exception $e) {
            // FK already dropped, continue
        }

        // Drop the index
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('products_label_id_foreign');
            });
        } catch (\Exception $e) {
            // Index already dropped, continue
        }

        // Rename old column, add new JSON column, copy data, drop old column
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('label_id', 'label_id_old');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('label_id')->nullable()->after('category_id');
        });

        // Migrate existing single IDs to JSON arrays
        DB::table('products')->whereNotNull('label_id_old')->chunkById(200, function ($products) {
            foreach ($products as $product) {
                DB::table('products')->where('id', $product->id)->update([
                    'label_id' => json_encode([$product->label_id_old]),
                ]);
            }
        });

        // Drop old column
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('label_id_old');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('label_id', 'label_id_json');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('label_id')->nullable()->after('category_id');
            $table->foreign('label_id')->references('id')->on('labels')->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('label_id_json');
        });
    }
};

