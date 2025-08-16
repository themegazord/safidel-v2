<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('forma_pagamento', function (Blueprint $table) {
      $table->id();
      $table->foreignId('empresa_id')->constrained('empresas', 'id')->cascadeOnDelete();
      $table->json('formas');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('forma_pagamento');
  }
};
