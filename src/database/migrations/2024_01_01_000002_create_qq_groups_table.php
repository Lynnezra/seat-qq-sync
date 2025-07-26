<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Lynnezra_qq_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_id', 20)->unique();
            $table->string('group_name', 100)->nullable();
            $table->json('required_corporations')->nullable();
            $table->json('required_alliances')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('Lynnezra_qq_groups');
    }
};