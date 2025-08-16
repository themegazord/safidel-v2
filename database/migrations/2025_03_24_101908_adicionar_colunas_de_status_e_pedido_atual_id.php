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
    Schema::table('mesas', function (Blueprint $table) {
      $table->string('status', 20)->default('livre')->nullable()->after('empresa_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('mesas', function (Blueprint $table) {
      $table->dropColumn( 'status');
    });
  }
};
