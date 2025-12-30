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
        if (Schema::hasTable('favorites') && ! Schema::hasColumn('favorites', 'favorite_id')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->unsignedBigInteger('favorite_id')->nullable()->after('user_id');
            });
        }

        if (Schema::hasTable('favorites') && ! Schema::hasColumn('favorites', 'favorite_type')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->string('favorite_type')->nullable()->after('favorite_id');
            });
        }

        // Migrate data from post_id to favorite_id and favorite_type

        if (Schema::hasTable('favorites') && Schema::hasColumn('favorites', 'post_id')) {
            DB::table('favorites')
                ->whereNotNull('post_id')
                ->update([
                    'favorite_id' => DB::raw('post_id'),
                    'favorite_type' => 'App\Models\Post',
                ]);
            Schema::table('favorites', function (Blueprint $table) {
                $table->dropColumn('post_id');
            });
        }


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropColumn('favorite_id');
            $table->dropColumn('favorite_type');
        });


    }
};
