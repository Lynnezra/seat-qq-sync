<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Lynnezra_qq_bindings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('character_id');
            $table->string('qq_number', 20);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'qq_number']);
            $table->unique('qq_number');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('character_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('Lynnezra_qq_bindings');
    }
};