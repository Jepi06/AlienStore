<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->string('merk');
        $table->decimal('harga', 10, 2);
        $table->integer('stok');
        $table->string('image')->nullable();
        $table->timestamps();
        $table->unsignedBigInteger('subcategory_id');
$table->foreign('subcategory_id')->references('id')->on('product_subcategories')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
