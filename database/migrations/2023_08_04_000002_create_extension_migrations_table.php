<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('extension.tables.extension_migration'), function (Blueprint $table) {
            $table->id();

            $table->string('extension');
            /**
             * Extension identifier (e.g. package name, slug)
             *
             * - unique key to identify the extension in the system
             */

            $table->string('name');
            /**
             * Display name of the extension
             *
             * - human-readable label for UI
             */

            $table->string('migration');
            /**
             * The migration class name that was run for this extension
             *
             * - e.g. CreateUsersTable
             */

            $table->dateTime('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('extension.tables.extension_migration'));
    }
};
