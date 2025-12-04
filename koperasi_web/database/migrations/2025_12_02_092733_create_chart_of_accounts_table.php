<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); 
            $table->string('name'); 
            $table->string('class_code'); 
            $table->string('group_code')->nullable();
            $table->string('type_code')->nullable(); 
            $table->string('category'); 
            $table->enum('normal_balance', ['debit', 'credit']); 
            $table->text('description')->nullable(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};