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
            if (Schema::hasColumn('challenges', 'puzzle_count')) {
                $table->dropColumn('puzzle_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            if (! Schema::hasColumn('challenges', 'puzzle_count')) {
                $table->unsignedInteger('puzzle_count')->default(100)->after('price_myr');
            }
        });
    }
};