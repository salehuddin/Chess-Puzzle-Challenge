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
        Schema::table('challenges', function (Blueprint $table) {
            $table->unsignedInteger('medal_stock_on_hand')->default(0)->after('medal_height');
            $table->unsignedInteger('medal_reorder_threshold')->default(5)->after('medal_stock_on_hand');
            $table->unsignedInteger('medal_reorder_quantity')->nullable()->after('medal_reorder_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn(['medal_stock_on_hand', 'medal_reorder_threshold', 'medal_reorder_quantity']);
        });
    }
};
