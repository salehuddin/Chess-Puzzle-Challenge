<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['source_subscription_id']);
            });
        } catch (Throwable) {
            // FK may already have been dropped by a previous failed migration attempt
        }

        Schema::dropIfExists('user_puzzle_progress');
        Schema::dropIfExists('subscriptions');

        Schema::create('puzzle_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('puzzle_id')->constrained()->cascadeOnDelete();
            $table->timestamp('solved_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'challenge_id', 'puzzle_id']);
            $table->index(['user_id', 'challenge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puzzle_progress');

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->enum('status', ['pending', 'paid', 'in_progress', 'completed', 'shipped'])->default('pending');
            $table->json('address_snapshot')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('courier')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_puzzle_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('puzzle_id')->constrained()->cascadeOnDelete();
            $table->timestamp('solved_at')->nullable();
            $table->timestamps();

            $table->unique(['subscription_id', 'puzzle_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('source_subscription_id')
                ->nullable()
                ->after('user_id')
                ->unique()
                ->constrained('subscriptions')
                ->nullOnDelete();
        });
    }
};
