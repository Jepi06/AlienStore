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
        Schema::create('sec_user', function (Blueprint $table) {
            $table->mediumIncrements('id')->unsigned();
            $table->string('name')->nullable();
            $table->string('email', 254)->nullable();
            $table->string('password', 255);
            $table->mediumInteger('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('sec_role')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sec_user');
    }
};
