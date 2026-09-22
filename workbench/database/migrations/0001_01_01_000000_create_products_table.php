<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku');
            $table->string('category');
            $table->string('status');
            $table->decimal('price', 10, 2);
            $table->integer('stock');
            $table->date('released_at');
            $table->boolean('featured')->default(false);
        });

        $categories = ['electronics', 'books', 'clothing', 'toys'];
        $statuses = ['in_stock', 'low', 'out'];
        $rows = [];
        for ($i = 1; $i <= 60; $i++) {
            $rows[] = [
                'name' => 'Product '.$i,
                'sku' => 'SKU-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'category' => $categories[$i % 4],
                'status' => $statuses[$i % 3],
                'price' => round(5 + ($i * 3.5), 2),
                'stock' => ($i * 7) % 120,
                // A handful of recent rows so the custom "within last N days" operator
                // has something to select; the rest are safely in the past.
                'released_at' => $i <= 5
                    ? now()->subDays($i)->toDateString()
                    : now()->subYear()->setDate(now()->year - 1, ($i % 12) + 1, ($i % 27) + 1)->toDateString(),
                'featured' => $i % 5 === 0,
            ];
        }
        DB::table('products')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
