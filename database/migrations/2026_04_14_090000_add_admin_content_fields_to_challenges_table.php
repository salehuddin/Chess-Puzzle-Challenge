<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->after('slug');
            $table->string('meta_title')->nullable()->after('description');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('poster_image')->nullable()->after('sticker_artwork');
            $table->json('medal_images')->nullable()->after('poster_image');
            $table->json('content_blocks')->nullable()->after('medal_images');
            $table->json('image_gallery')->nullable()->after('content_blocks');
            $table->json('videos')->nullable()->after('image_gallery');
            $table->longText('terms_and_conditions')->nullable()->after('videos');
        });

        DB::table('challenges')
            ->where('is_active', true)
            ->update(['status' => 'published']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'meta_title',
                'meta_description',
                'poster_image',
                'medal_images',
                'content_blocks',
                'image_gallery',
                'videos',
                'terms_and_conditions',
            ]);
        });
    }
};
