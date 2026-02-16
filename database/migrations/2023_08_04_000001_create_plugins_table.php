<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('extension.tables.plugin'), function (Blueprint $table) {
            $table->id();

            $table->foreignId('extension_id')->index()->constrained(config('extension.tables.extension'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * Parent extension this plugin belongs to
             */

            $table->string('name')->index();
            /**
             * Plugin name / identifier
             *
             * - human-readable or machine identifier for the plugin
             */

            $table->json('fields')->nullable();
            /**
             * Plugin configuration from installer file
             *
             * value: json
             * use: {
             *     "key": "value"
             * }
             */

            $table->boolean('status')->default(true)->index();
            /**
             * Active status of the plugin
             *
             * - true = active, false = inactive
             * - inactive plugins are not available in the system
             */

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('extension.tables.plugin'));
    }
};
