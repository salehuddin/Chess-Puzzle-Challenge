<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bundles', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('slug');
        });

        DB::table('bundles')->orderBy('id')->each(function ($bundle) {
            DB::table('bundles')
                ->where('id', $bundle->id)
                ->update(['sku' => sprintf('BUND-%05d', $bundle->id)]);
        });

        Schema::table('bundles', function (Blueprint $table) {
            $table->string('sku')->unique()->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bundles', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn('sku');
        });
    }
};
