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
        Schema::create(config('extension.tables.extension'), function (Blueprint $table) {
            $table->id();

            $table->string('extension')->index();
            /**
             * This is the name of each Extension.
             */

            $table->string('name')->index();
            /**
             * This is the name of each Extension.
             */

            $table->string('namespace');
            /**
             * This is the namespace of each Extension.
             */

            $table->json('info')->nullable();
            /**
             * Information of the extension, this field will be filled from the installer file during installation.
             *
             * @example
             * {
             *    "title": "Extension Name",
             *    "description": "Description of the extension",
             *    "version": "1.0.0",
             *    "author": "Author Name",
             *    "email": "Author Email",
             *    "website": "Author Website",
             *    "creationDate": "2023-08-04",
             *    "copyright": "Copyright",
             *    "license": "License",
             * }
             */

            $table->timestamps();
        });

        cache()->forget('extension');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('extension.tables.extension'));

        cache()->forget('extension');
    }
};
