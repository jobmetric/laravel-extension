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
             * Extension identifier (e.g. package name, slug)
             *
             * - unique key to identify the extension in the system
             */

            $table->string('name')->index();
            /**
             * Display name of the extension
             *
             * - human-readable label for UI
             */

            $table->string('namespace');
            /**
             * PHP namespace of the extension
             *
             * - e.g. Vendor\PackageName
             */

            $table->json('info')->nullable();
            /**
             * Extension metadata from installer file
             *
             * value: json
             * use: {
             *     "title": "Extension Name",
             *     "description": "Description of the extension",
             *     "version": "1.0.0",
             *     "author": "Author Name",
             *     "email": "Author Email",
             *     "website": "Author Website",
             *     "creationDate": "2023-08-04",
             *     "copyright": "Copyright",
             *     "license": "License"
             * }
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
        Schema::dropIfExists(config('extension.tables.extension'));
    }
};
