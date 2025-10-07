<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('user_id');
            $table->unsignedBigInteger('category_id');
            $table->boolean('status')->default(0); // 0 = pending, 1 = checked out
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('sec_user')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')->on('product_categories')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
