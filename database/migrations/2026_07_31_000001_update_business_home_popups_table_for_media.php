<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('business_home_popups', function (Blueprint $table) {
            $table->string('popup_type')->change();
        });

        // Update any existing 'image' type popups to 'media' type
        \Illuminate\Support\Facades\DB::table('business_home_popups')
            ->where('popup_type', 'image')
            ->update(['popup_type' => 'media']);

        Schema::table('business_home_popups', function (Blueprint $table) {
            $table->enum('media_type', ['image', 'youtube'])->nullable()->after('popup_type');
            $table->string('youtube_url', 2048)->nullable()->after('image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_home_popups', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'youtube_url']);
        });

        \Illuminate\Support\Facades\DB::table('business_home_popups')
            ->where('popup_type', 'media')
            ->update(['popup_type' => 'image']);
    }
};
