<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_sources', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('raw_content');
        });

        Schema::table('uploaded_files', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('extracted_text');
        });
    }

    public function down(): void
    {
        Schema::table('business_sources', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });

        Schema::table('uploaded_files', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
