<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Lynnezra_qq_bot_configs', function (Blueprint $table) {
            $table->id();
            $table->string('bot_qq', 20);
            $table->string('napcat_api_url');
            $table->string('api_token')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('Lynnezra_qq_bot_configs');
    }
};