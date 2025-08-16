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
    Schema::table('cardapios', function (Blueprint $table) {
      $table->string('tipo_funcionamento')->after('dias_funcionamento')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('cardapios', function (Blueprint $table) {
      $table->dropColumn('tipo_funcionamento');
    });
  }
};
