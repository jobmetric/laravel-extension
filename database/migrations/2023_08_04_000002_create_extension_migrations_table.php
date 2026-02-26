<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config('extension.tables.extension_migration'), function (Blueprint $table) {
            $table->id();
            $table->string('migration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('extension.tables.extension_migration'));
    }
};
