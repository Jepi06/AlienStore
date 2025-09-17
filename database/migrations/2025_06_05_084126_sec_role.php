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
        Schema::create('sec_role', function (Blueprint $table) {
            $table->mediumIncrements('id')->unsigned();
            $table->string('name', 20);
            $table->string('description', 100)->nullable();
            $table->tinyInteger('active')->unsigned()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sec_role');
    }
};
