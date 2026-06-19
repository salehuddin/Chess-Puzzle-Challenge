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
            $table->decimal('medal_weight', 8, 2)->nullable()->after('sticker_artwork');
            $table->decimal('medal_length', 8, 2)->nullable()->after('medal_weight');
            $table->decimal('medal_width', 8, 2)->nullable()->after('medal_length');
            $table->decimal('medal_height', 8, 2)->nullable()->after('medal_width');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn(['medal_weight', 'medal_length', 'medal_width', 'medal_height']);
        });
    }
};
