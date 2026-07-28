<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->enum('puzzle_navigation_mode', ['strict', 'free', 'strict_skip', 'strict_autosolve'])
                ->default('strict')
                ->after('is_active');
            $table->smallInteger('skip_token_count')->nullable()->after('puzzle_navigation_mode');
            $table->smallInteger('autosolve_threshold')->nullable()->after('skip_token_count');
        });

        Schema::table('puzzle_progress', function (Blueprint $table) {
            $table->boolean('solved_with_help')->default(false)->after('solved_at');
            $table->timestamp('skipped_at')->nullable()->after('solved_with_help');
            $table->unsignedInteger('attempts')->default(0)->after('skipped_at');
            $table->unsignedInteger('hints_used')->default(0)->after('attempts');
        });
    }

    public function down(): void
    {
        Schema::table('puzzle_progress', function (Blueprint $table) {
            $table->dropColumn(['solved_with_help', 'skipped_at', 'attempts', 'hints_used']);
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn(['puzzle_navigation_mode', 'skip_token_count', 'autosolve_threshold']);
        });
    }
};
