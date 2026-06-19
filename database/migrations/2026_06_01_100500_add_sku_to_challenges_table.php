<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('slug');
        });

        DB::table('challenges')->orderBy('id')->each(function ($challenge) {
            DB::table('challenges')
                ->where('id', $challenge->id)
                ->update(['sku' => sprintf('CHAL-%05d', $challenge->id)]);
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->string('sku')->unique()->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn('sku');
        });
    }
};
