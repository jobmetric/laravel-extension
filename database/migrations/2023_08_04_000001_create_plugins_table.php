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
             * Extension ID is the ID of the extension.
             */

            $table->string('title')->index();
            /**
             * This is the title of each Extension.
             */

            $table->json('fields')->nullable();
            /**
             * Fields of the extension, this field will be filled from the installer file during installation.
             *
             * @example
             * {
             *    "key": "value"
             * }
             */

            $table->boolean('status')->default(true)->index();
            /**
             * If the layout is not active, it will not be displayed in the layout list.
             */

            $table->timestamps();
        });

        cache()->forget('plugin');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('extension.tables.plugin'));

        cache()->forget('plugin');
    }
};
